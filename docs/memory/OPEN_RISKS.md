# OPEN_RISKS — B360

> Risques techniques connus, suivi vivant. Mise à jour : **2026-04-22 19:08:13    **

---

## CRITIQUE

### R-004 — Commissions employés dupliquées

- **Source** : ISSUE-02
- **Module** : Eshop360 (HR, EmployeeCommission)
- **Impact** : sur-paiement RH
- **Statut** : à investiguer

## MAJEUR

### R-101 — Eshop360 monolithique

- **Source** : cartographie 2026-04-04
- **Constat** : 83 modèles, 78 contrôleurs, 128 migrations
- **Impact** : maintenance difficile, couplage fort
- **Plan** : extraction en sous-domaines (Catalog, Pricing, Inventory, Sales, Finance, CRM, Channel)
- **Statut** : roadmap définie, exécution à planifier

### R-103 — Double système Codifarm / DistributionChannel

- **Tables** : `eshop_codifarm_margin_config` (legacy SAPHIR) vs `eshop_distribution_channels` (actif)
- **Impact** : double écriture de logs, source de vérité ambiguë
- **Plan** : migration documentée dans `docs/Ins/b360_evolution_strategy.md` §2.3

## FERMÉ

### R-003 — Solde portefeuille / compte négatif (fermée 2026-04-22)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-03
- **Constat** : `FinanceService::creditWallet/debitWallet` étaient déjà protégés (lockForUpdate + transaction + guard). Mais deux call sites court-circuitaient ce service (`ChannelPortalCustomerController::walletTopup` et `SaleController::storeReturn` refund=wallet : `Customer::increment()` direct, zéro protection). `WalletDriver::initiate()` avait un TOCTOU (check hors transaction). Aucune contrainte SGBD `wallet_balance >= 0`.
- **Résolution** : défense en profondeur 3 couches (CHECK SGBD + point d'entrée unique `FinanceService` + lock pessimiste `WalletDriver`). Voir ADR-004.
  - Migration `2026_04_22_110001` : ajoute `CHECK (wallet_balance >= 0)` sur `eshop_customers` (MySQL/PG, no-op SQLite).
  - `WalletDriver::initiate()` : check solde déplacé **dans** la transaction, sous `lockForUpdate()`. Lève `InsufficientWalletBalanceException` typée.
  - `WalletDriver::refund()` : `lockForUpdate()` ajouté avant increment.
  - `ChannelPortalCustomerController::walletTopup` : refactoré pour passer par `FinanceService::creditWallet()` (audit CustomerTransaction + auto-pay dues).
  - `SaleController::storeReturn` refund=wallet : idem, plus de `Customer::where(...)->increment('wallet_balance')` direct.
- **Tests** : `Modules/Eshop360/Tests/Feature/WalletIntegrityTest` (7 tests : structure migration, CHECK MySQL-only, WalletDriver lock structurel, débits séquentiels anti-négatif, audit trail FinanceService, structure refactor ChannelPortal, structure refactor SaleReturn). `P0SafetyGuardsTest` (3 tests wallet FinanceService) conservé.
- **ADR** : `docs/adr/ADR-004-wallet-integrity-strategy.md`.
- **Commit** : branche `feat/eshop360-wallet-integrity`.

### R-002 — Webhook paiement traité deux fois (fermée 2026-04-22)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-04
- **Constat** : deux surfaces distinctes. Billing `WebhookController` n'avait aucune protection (pas de colonne d'idempotence, pas d'unique, aucun guard). Eshop360 `WebhookService` avait une protection partielle (colonne `deduplication_key` + check applicatif, mais pas de contrainte UNIQUE DB → race window).
- **Résolution** : défense en profondeur 3 couches (UNIQUE SGBD + guard applicatif + HMAC signature, voir ADR-003).
  - Billing : migration `2026_04_22_100001` ajoute `billing_webhook_logs.idempotency_key VARCHAR(128) NULLABLE UNIQUE`. `WebhookController::handle()` calcule une clé stable (préfixée par gateway, priorité `payload.id`/`event_id`/`transaction_id`/`cpm_trans_id`/fallback hash du corps) puis wrap la création du log dans un try/catch `UniqueConstraintViolationException` → replay retourne 200 OK sans retraiter.
  - Eshop360 : migration `2026_04_22_100002` convertit l'index existant sur `eshop_webhook_logs.deduplication_key` en contrainte UNIQUE (ferme la race window du check applicatif).
- **Tests** : `Modules/Billing/Tests/Feature/WebhookIdempotenceTest` (5 tests) + `Modules/Eshop360/Tests/Feature/WebhookServiceIdempotenceTest` (2 tests, complète le test applicatif existant `P0SafetyGuardsTest::test_webhook_duplique_est_ignore`).
- **ADR** : `docs/adr/ADR-003-webhook-idempotency-strategy.md`.
- **Effet de bord** : `Modules\Billing\Services\GatewayManager` dé-finalisé pour permettre le mocking en test (la classe reste singleton par DI, impact pratique nul).
- **Commit** : branche `feat/billing-webhook-idempotence`.

### R-001 — Race condition sur le stock (fermée 2026-04-22)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-01
- **Constat audit** : la mitigation était **déjà codée** (`StockService::adjustStock()` utilise `lockForUpdate()` dans `DB::transaction()` avec refresh et guard `quantity >= 0`) depuis la migration `2026_04_04_100001`. Ce qui manquait : la preuve et la traçabilité.
- **Ajouts** :
  - `Modules/Eshop360/Tests/Unit/StockServiceConcurrencyTest.php` — 5 tests : structure (lock+transaction), séquentiel anti-négatif, rollback, multi-tenant, insufficient-first-sale.
  - `Modules/Eshop360/Tests/Feature/StockCheckConstraintTest.php` — vérifie la présence de la contrainte CHECK SQL en MySQL (skip SQLite).
  - `docs/adr/ADR-002-stock-concurrency-strategy.md` — stratégie de défense en profondeur (lock applicatif + CHECK SQGBD + unique schéma).
- **Limite connue** : SQLite :memory: (phpunit.xml) ne reproduit pas une race physique multi-process. Les tests valident la structure du code et le comportement séquentiel. Un stress-test MySQL parallèle reste un "nice to have" (hors scope tests unitaires).
- **Commit** : branche `test/eshop360-stock-concurrency-coverage`

### R-102 — FeatureGate deprecated retiré (fermé 2026-04-22)

- **Résolution** : suppression de `Modules/Eshop360/Services/FeatureGate.php`. La classe était déjà un wrapper @deprecated délégant à `FeatureRegistry` (Billing), sans appelant production (0 import hors le test unitaire qui l'exerçait directement). Le test obsolète `test_feature_gate_service_returns_free_features_without_instance` supprimé, les 5 autres tests FeatureRegistry conservés.
- **Canonique** : `Modules\Billing\Services\FeatureRegistry` + `EnsureFeature` middleware, features registrées via `HookRegistry` dans `Eshop360HooksProvider::registerBillableFeatures()` (48 items).
- **Commit** : branche `refactor/eshop360-remove-feature-gate`

### R-104 — Trait BelongsToInstance dupliqué (fermé 2026-04-22)

- **Résolution** : suppression de `app/Models/Concerns/BelongsToInstance.php` (alias 4 lignes, 0 usage applicatif). Trait canonique conservé : `Modules\Core\Database\Traits\BelongsToInstance` (63 modèles l'importent). PHPDoc corrigé.
- **Commit** : branche `refactor/core-unify-belongs-to-instance`

## MOYEN

### R-201 — InventoryX squelette non chargé

- **Constat** : dossier visible mais pas de `module.json` ni provider chargé
- **Plan** : décider — finaliser ou supprimer

### R-202 — Numéro facture non atomique

- **Source** : ISSUE-10 audit go-live
- **Plan** : utiliser séquence DB ou advisory lock

## FAIBLE

### R-301 — Event PasswordReset orphelin

- **Source** : ISSUE-12 audit go-live
- **Plan** : ajouter listener d'audit

---

## Convention

Chaque risque a :
- ID stable (R-XXX)
- Source (audit, ticket, observation)
- Module(s) concerné(s)
- Impact business
- Statut (ouvert / en cours / mitigé / fermé)
- Plan de mitigation
- Lien vers le PR de résolution si en cours
