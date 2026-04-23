# ADR-004 — Stratégie d'intégrité du solde portefeuille (R-003)

> Architectural Decision Record. Fixe la stratégie anti-overdraft et anti-race sur toutes les mutations du solde `wallet_balance` client.

## Statut

**Accepté** — 2026-04-22

## Contexte

Le solde portefeuille (`eshop_customers.wallet_balance`) représente une valeur financière liquide pour chaque client : provision pour payer commandes, remboursements, bons d'achat. Les mutations arrivent de **multiples points d'entrée** :

- `FinanceService::debitWallet()` — paiement de commande, consommation de crédit
- `FinanceService::creditWallet()` — rechargement manuel, auto-pay des dues
- `WalletDriver::initiate()` — paiement en ligne via wallet (PublicPaymentController)
- `WalletDriver::refund()` — remboursement d'un paiement wallet
- `ChannelPortalCustomerController::walletTopup()` — rechargement via portail canal
- `SaleController::storeReturn()` refund wallet — remboursement d'un retour de vente

**Deux risques de corruption du solde** :

1. **Solde négatif** : un débit qui n'est pas guardé peut rendre `wallet_balance < 0` → crédit implicite non autorisé au client, perte financière pour l'entreprise.
2. **Double débit / double crédit** : deux requêtes concurrentes sur le même client peuvent lire le solde initial, décider chacune qu'elles ont le droit de décrémenter, et commit les deux → solde incohérent.

**Constat avant ce lot (R-003)** :

- `FinanceService::debitWallet` et `creditWallet` étaient **déjà protégés** (`lockForUpdate()` + `DB::transaction` + check `min($balance, $amount)`).
- **MAIS** deux call sites court-circuitaient totalement ce service :
  - `ChannelPortalCustomerController::walletTopup` ligne 174 : `$customer->increment('wallet_balance', …)` direct, zéro lock, zéro transaction, zéro audit.
  - `SaleController::storeReturn` ligne 451 : `Customer::where(...)->increment('wallet_balance', …)` direct, idem.
- **WalletDriver::initiate** avait un check `< $amount` **hors** `DB::transaction` (ligne 29) puis `decrement` à l'intérieur (ligne 38) → fenêtre TOCTOU.
- **WalletDriver::refund** `increment` dans une transaction mais **sans lock** pessimiste.
- **Aucune contrainte CHECK SGBD** sur `wallet_balance >= 0` → la base acceptait silencieusement les soldes négatifs si un bug applicatif en produisait.

Ce lot ferme toutes ces portes simultanément.

## Décision

Nous adoptons une **défense en profondeur à trois couches, uniformément appliquée à toutes les mutations de `wallet_balance`** :

1. **Couche 1 — Contrainte CHECK SGBD `wallet_balance >= 0`.**
   Migration `2026_04_22_110001_add_check_constraint_wallet_balance` sur MySQL / PostgreSQL (no-op SQLite). Filet ultime : si un bug applicatif contourne tous les guards, la DB refuse l'`UPDATE` avec une erreur d'intégrité.

2. **Couche 2 — Point d'entrée unique `FinanceService` pour toutes les mutations métier.**
   - `FinanceService::creditWallet()` et `debitWallet()` restent les seules méthodes autorisées à muter `wallet_balance` depuis les contrôleurs métier.
   - Les deux anciens call sites `Customer::increment('wallet_balance', ...)` ont été **supprimés** et remplacés par des appels à `FinanceService::creditWallet()`.
   - Garanties obtenues : `DB::transaction`, `lockForUpdate`, création automatique d'un `CustomerTransaction` d'audit, auto-pay des `CustomerDue` en attente.

3. **Couche 3 — Lock pessimiste généralisé sur `WalletDriver`.**
   - `initiate()` : le check `balance < amount` est désormais **à l'intérieur** de la transaction, **après** `lockForUpdate()`. L'insuffisance de solde lève `InsufficientWalletBalanceException` (exception typée, traduite en message métier par le caller).
   - `refund()` : ajout de `lockForUpdate()` avant l'increment pour sérialiser plusieurs remboursements concurrents.

### Contrainte complémentaire : signature HMAC et authentification des appelants

Hors scope de cet ADR mais rappelé pour complétude :
- `WalletDriver::initiate()` est invoqué par `PublicPaymentController` qui vérifie la signature du webhook côté passerelle.
- `ChannelPortalCustomerController::walletTopup` est derrière middleware `channel.access` + authentification.
- `SaleController::storeReturn` est derrière permissions Spatie (`eshop.sales.return`).

Ces couches d'authentification ne sont PAS modifiées par ce lot.

## Conséquences

### Positives

- **Solde négatif impossible** en production : couverture DB (CHECK) + couverture applicative (FinanceService + WalletDriver) + audit (CustomerTransaction) + tests (7 tests feature).
- **Race sur débits concurrents fermée** : toute mutation est sérialisée par `lockForUpdate()` sur la ligne Customer.
- **Audit trail complet** : chaque modification du solde crée un `CustomerTransaction` avec type/amount/reference/notes/created_by. Plus de mutations "fantômes" côté `ChannelPortalCustomerController` ou `SaleController`.
- **API unifiée** : les futurs call sites de mutation wallet n'ont qu'un seul endroit où aller (`FinanceService`), plus de duplication de logique.
- **Rétrocompatibilité des soldes existants** : la contrainte CHECK est ajoutée à une table en production. Les soldes actuels (tous `>= 0` par design applicatif) passent sans erreur.

### Négatives / coûts

- **Contention sur forte concurrence** : deux paiements sur le même client attendent en file. Trade-off accepté (compromis concurrence vs cohérence), la durée du lock est millisecondes.
- **`InsufficientWalletBalanceException`** : nouveau type d'exception que tous les callers de `WalletDriver::initiate` doivent gérer (aujourd'hui : WalletDriver lui-même la catch). Risque d'ajout futur sans handler → l'exception remonterait comme 500. Mitigation : tests structurels + revue PR.
- **SQLite tests incomplets** : la contrainte CHECK n'est pas effective en SQLite :memory: (limite driver). Les tests valident la STRUCTURE de la migration et le comportement applicatif, mais la validation SGBD complète nécessite MySQL.
- **Les routes `walletTopup` et `storeReturn` ont désormais une dépendance explicite à `FinanceService`** : le service container Laravel résout automatiquement, mais les tests qui swappaient `Customer` directement doivent être adaptés si besoin.

### Neutres

- `FinanceService::creditWallet/debitWallet` inchangés (déjà corrects avant ce lot).
- Aucun changement de schéma au-delà de la contrainte CHECK.
- Aucune migration de données requise.

## Alternatives considérées

### Alternative A : CHECK SGBD uniquement

S'appuyer sur la contrainte DB et attraper l'erreur côté application pour renvoyer une réponse métier.

**Rejetée parce que** : (1) non portable sur SQLite, casse les tests unitaires, (2) produit des `QueryException` génériques à traduire en messages métier dans chaque caller, (3) ne résout pas la race TOCTOU du WalletDriver (check+decrement = 2 queries indépendantes même avec CHECK côté DB).

### Alternative B : colonne `version` + compare-and-swap optimiste

Ajouter `eshop_customers.wallet_version` et muter via `UPDATE ... WHERE version = ?` avec retry côté application.

**Rejetée parce que** : complexité importante (retry, livelock risk sous contention), et nécessite de toucher tous les call sites pour gérer la logique retry. Le lock pessimiste est plus simple et suffisant à la charge actuelle.

### Alternative C : double-entry ledger (journal comptable immuable)

Ne jamais muter `wallet_balance` directement, mais le calculer à la demande depuis la somme des `CustomerTransaction`.

**Rejetée parce que** : refonte majeure, impacts sur performance des lectures (join + sum à chaque consultation de solde), et réécriture de toutes les pages qui affichent le solde. Envisageable comme ADR futur si le volume de transactions explose.

### Alternative D : queue idempotente sérialisant les mutations wallet

Pousser chaque opération dans une queue consommée séquentiellement par worker.

**Rejetée parce que** : casse la synchronicité du POS et du portail client (attente utilisateur), introduit une dépendance infrastructurelle nouvelle (Redis worker dédié), complexité opérationnelle. Pas justifié à l'échelle actuelle.

## Implications opérationnelles

- **Code modifié** :
  - `Modules/Eshop360/Services/Payment/Drivers/WalletDriver.php` — refonte `initiate()` et `refund()` avec lock pessimiste.
  - `Modules/Eshop360/Services/Payment/Drivers/InsufficientWalletBalanceException.php` — nouvelle exception typée.
  - `Modules/Eshop360/Http/Controllers/ChannelPortal/ChannelPortalCustomerController.php` — `walletTopup` injecte `FinanceService`.
  - `Modules/Eshop360/Http/Controllers/Sales/SaleController.php` — `storeReturn` wallet refund utilise `FinanceService`.
  - Nouvelle migration `2026_04_22_110001_add_check_constraint_wallet_balance`.

- **Tests** :
  - `Modules/Eshop360/Tests/Feature/WalletIntegrityTest.php` — 7 tests : migration présente + SQL correct, contrainte CHECK enforced MySQL, WalletDriver structure lock, débits séquentiels anti-négatif, audit trail credit via FinanceService, structure ChannelPortal refactor, structure SaleReturn refactor.
  - `Modules/Eshop360/Tests/Unit/P0SafetyGuardsTest.php` existant conservé (3 tests wallet FinanceService).

- **Documentation** :
  - `docs/governance/PROTECTED_AREAS.md` : zone L2 Wallet reste inchangée (la promotion en L1 n'est pas proposée, le lot renforce les guards sans changer la criticité perçue).
  - `docs/memory/OPEN_RISKS.md` : R-003 déplacé en FERMÉ.
  - `CHANGELOG_ARCHITECTURAL.md` : entrée `CHG-2026-04-22-005`.

- **Migration** : une migration additive. Déploiement : `php artisan migrate`. Pas de downtime (ALTER TABLE ADD CONSTRAINT CHECK est rapide sur MySQL 8+ même sur tables peuplées — la validation de l'existant est faite à la volée).

- **Formation** : tout développeur qui ajoute un nouveau point d'entrée muant `wallet_balance` doit (1) passer par `FinanceService::creditWallet/debitWallet`, (2) ne jamais utiliser `Customer::increment/decrement('wallet_balance')` directement ni `DB::table('eshop_customers')->update`, (3) lire cet ADR.

## Contraintes imposées au futur

1. **Aucune mutation de `wallet_balance` hors `FinanceService::creditWallet/debitWallet` et `WalletDriver::initiate/refund`.** Les 4 tests structurels (`test_wallet_driver_source_uses_lock_for_balance_check`, `test_channel_portal_topup_source_uses_finance_service`, `test_sale_return_source_uses_finance_service_for_wallet_refund`, `test_check_constraint_migration_exists_with_correct_sql`) bloquent toute régression.
2. **La contrainte CHECK `wallet_balance >= 0` doit rester en place sur MySQL/PostgreSQL.** Toute suppression est régression financière.
3. **`WalletDriver::initiate` et `refund` doivent rester dans un `DB::transaction` avec `lockForUpdate`.** Le test structurel `test_wallet_driver_source_uses_lock_for_balance_check` le verrouille.
4. **`InsufficientWalletBalanceException` doit être traitée par tous les callers de `WalletDriver::initiate`.** Aujourd'hui le driver se catch lui-même, futurs callers doivent suivre le pattern.
5. **Prod tourne obligatoirement sur MySQL 8+ ou PostgreSQL 14+** pour bénéficier de la contrainte CHECK.

## Références

- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — ISSUE-03 (identification R-003)
- `docs/audit_comparatif_final.md` — ligne E-3 (status avant ce lot)
- `docs/p0_correction_report.md` — contexte P0 migration
- `docs/memory/OPEN_RISKS.md` — R-003
- `docs/governance/PROTECTED_AREAS.md` — zone L2 Wallet
- `Modules/Eshop360/Services/FinanceService.php:136` (debitWallet) et `:182` (creditWallet)
- `Modules/Eshop360/Services/Payment/Drivers/WalletDriver.php` — refactor de ce lot
- `Modules/Eshop360/Database/Migrations/2026_04_22_110001_add_check_constraint_wallet_balance.php`
- ADR-002 (même pattern pour R-001 stock)
- ADR-003 (même pattern pour R-002 webhooks)
- Commit de clôture : branche `feat/eshop360-wallet-integrity`

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la stratégie évolue (ex. migration vers un ledger événementiel), créer une nouvelle ADR qui remplace celle-ci, et marquer celle-ci `Remplacé par ADR-XXX`.
