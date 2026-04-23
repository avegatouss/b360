# RECENT_DECISIONS — B360

> Décisions structurantes récentes. Mise à jour : **2026-04-22 19:08:13    **
> Pour les décisions complètes argumentées, voir `docs/adr/`.

---

## 2026-04-23 — R-103 fermé : consolidation Codifarm → DistributionChannel

- **Décision** : acter la suppression du système legacy Codifarm (déjà migré+droppé par la chaîne P0 `2026_03_16_100002/100003`) et poser les verrous anti-régression. Canon unique désormais : `DistributionChannel` + `ChannelMarginLog` + `ChannelProductPrice`.
- **Constat clé** : aucun chantier de code à faire — migration et refactor applicatif déjà livrés en P0 (mars 2026). Il manquait la **fermeture formelle** + **tests structurels anti-résurrection**.
- **Tests nouveaux** : 3 (`CodifarmLegacyRemovalTest`) — tables absentes, colonnes absentes, aucune référence legacy dans le code applicatif.
- **ADR** : `docs/adr/ADR-007-codifarm-channel-consolidation.md` (contraintes imposées au futur : réintroduire les tokens legacy dans Services/Controllers/Models/Domain casse le test structurel).
- **Résidus acceptables** : le mot « CODIFARM » reste dans les Seeders/Tests comme nom commercial de grossiste pharmaceutique ivoirien (donnée démo), pas comme technologie. Les tests structurels scannent uniquement le code applicatif, pas les Seeders/Tests.
- **Source** : lot MAJEUR, branche `chore/eshop360-close-codifarm-consolidation`, audit ISSUE + §2.3 du plan d'évolution.

## 2026-04-23 — R-101 sous-lot S2 : extraction CRM

- **Décision** : application du pattern S1 (déplacement + stubs d'alias + deptrac resserré) au sous-domaine CRM.
- **Modèles migrés** : 4 fichiers (Customer, CustomerGroup, CustomerDue, CustomerTransaction) vers `Modules/Eshop360/Domain/CRM/Models/`. 4 stubs d'alias rétrocompatibles créés dans `Models/`.
- **Imports cross-sous-domaine** : Customer.php +4 (Order, OnlineOrder, Invoice, SupportTicket). CustomerDue.php +2 (Order, Invoice). Tous via alias `Modules\Eshop360\Models\*` transitoires.
- **Deptrac** : `EshopCRM` restreint à socles + Eshop360 (pour BelongsToChannel, ScopedByUserAssignment, alias cross-domain — levés aux sous-lots S3, S8, S9, S11).
- **Validation** : 659 tests passed (aucune régression), phpstan OK, deptrac 0 violations.
- **ADR** : `docs/adr/ADR-010-eshop360-crm-subdomain-extraction.md`.
- **Source** : lot R-101 S2, branche `refactor/eshop360-s2-crm-extraction`.

## 2026-04-23 — R-101 sous-lot S1 : extraction Catalog

- **Décision** : premier sous-lot d'extraction effective. 7 modèles Catalog (Product, Category, Brand, ProductGroup, ProductTax, ProductVariation, Tax) déplacés physiquement de `Modules/Eshop360/Models/` vers `Modules/Eshop360/Domain/Catalog/Models/` avec mise à jour du namespace.
- **Rétrocompatibilité 100%** : stubs d'alias de 13 lignes créés dans `Modules/Eshop360/Models/<Nom>.php` qui `extends` le canon. Les 50+ consommateurs existants continuent de fonctionner sans aucune modification.
- **Product.php** : ajout de 5 imports pour les relations cross-sous-domaine (Stock, OrderItem, Supplier, ChannelProductPrice, DistributionChannel) — ces imports pointent vers les alias `Modules\Eshop360\Models\*` et seront remplacés par leurs FQN canoniques au fil des sous-lots S3/S5/S7/S8.
- **Deptrac** : ruleset `EshopCatalog` restreinte à socles + Eshop360 (dépendance transitoire liée à BelongsToChannel, à lever au sous-lot S3).
- **PHPStan** : baseline régénérée (3647 erreurs baselined vs 3656 avant) — les erreurs de traits sur les modèles Catalog se sont déplacées vers le nouveau namespace.
- **Validation** : 659 tests passed (aucune régression), phpstan OK, deptrac 0 violations.
- **Pattern répétable** : ce schéma (git mv + namespace update + stubs d'alias + resserrement deptrac + baseline régénérée) sera appliqué aux sous-lots S2..S11.
- **ADR** : `docs/adr/ADR-009-eshop360-catalog-subdomain-extraction.md`.
- **Source** : lot R-101 S1, branche `refactor/eshop360-s1-catalog-extraction`.

## 2026-04-23 — R-101 sous-lot S0 : préparation découpage Eshop360

- **Décision** : adoption de la **stratégie C (hybride)** pour extraire Eshop360 monolithique en sous-domaines — voir ADR-008.
  - Phase 1 : sous-dossiers `Modules/Eshop360/Domain/<X>/` disciplinés par deptrac (S0 à S11).
  - Phase 2 : promotion en vrais modules Laravel différée jusqu'à besoin externe concret (ex. Menuiserie360 consomme Catalog).
- **Sous-lot S0 livré** : 13 layers deptrac intra-Eshop360 (`EshopCatalog`, `EshopCRM`, `EshopChannel`, `EshopPricing`, `EshopInventory`, `EshopPromotions`, `EshopPurchasing`, `EshopSales`, `EshopFinance`, `EshopHR`, `EshopCommunication`, `EshopProjects`, `EshopReporting`) + ruleset permissive temporaire + layer résiduel `Eshop360` (le "reste" monolithique qui se vide au fil des sous-lots).
- **Choix non-trivial** : ruleset **permissive** au départ, resserrement **progressif** à chaque sous-lot (au lieu d'une baseline géante capturant toutes les violations actuelles). Plus lisible, plus maintenable.
- **Prochains sous-lots** : S1 Catalog (5j) → S2 CRM (3j) → S3 Channel (3j) → ... → S12 Clôture. Total ~44 jours étalés.
- **Source** : lot MAJEUR, branche `chore/eshop360-domain-layers-baseline`, ADR-008 + roadmap `docs/Ins/b360_evolution_strategy.md` §1.4.

## 2026-04-23 — R-201 fermé : suppression du squelette InventoryX

- **Décision** : SUPPRIMER le module InventoryX (preuves convergentes : 0 fichier PHP, 0 référence prod, roadmap explicite task #22, jamais chargé par le système de modules).
- **Actions** : suppression de `Modules/InventoryX/` (10 dossiers vides), nettoyage des références actives dans `rector.php`, `phpstan.neon` (+ copie `tools/phpstan/`), `deptrac.yaml` (+ copie `tools/deptrac/`), `scripts/memory/bootstrap-from-existing.sh`, `.vscode/settings.json`, `docs/context/PROJECT_DIGEST.md`. Suppression des docs dédiés (`docs/cartographie/01_modules/inventoryx.md`, `docs/audits/01_modules/inventoryx.md`).
- **Effet de bord propre** : synchronisation de `tools/phpstan/phpstan.neon` et `tools/deptrac/deptrac.yaml` avec leurs versions root (drift accumulé depuis l'install du pack corrigé à cette occasion).
- **Bénéfice** : réduction de la surface « squelettes morts », clarification de la roadmap (la future extraction Inventory partira du code Eshop360, pas d'un squelette recyclé).
- **Source** : lot MOYEN, branche `chore/eshop360-remove-inventoryx-skeleton`, audit ISSUE + roadmap task #22.

## 2026-04-23 — R-301 fermé : audit PasswordReset

- **Décision** : ajout d'un listener `Modules\Auth\Listeners\LogPasswordReset` qui écrit dans `login_logs` avec `status = 'password_reset'`. Plus d'event orphelin.
- **Chantiers** :
  1. Listener créé (pattern miroir de `LogSuccessfulLogin`).
  2. Enregistrement dans EventServiceProvider + ajout du provider dans `module.json` (n'y figurait pas).
  3. Migration convertit `login_logs.status` ENUM → VARCHAR(30) pour accepter le nouveau statut et faciliter l'extension future.
- **Tests nouveaux** : 3 (PasswordResetAuditTest) — registration, event dispatch crée log, listener direct.
- **Source** : lot L1 Auth (zone critique, ajout pur sans modif existant), branche `feat/auth-log-password-reset`, audit ISSUE-12.

## 2026-04-23 — R-202 fermé : atomicité numéros de facture

- **Décision** : pattern uniforme `DB::transaction + for-loop MAX_NUMBER_ATTEMPTS + catch QueryException 1062 + régénération` pour tous les générateurs de numéros métier (factures Eshop360, factures Billing, commandes Eshop360).
- **Chantier** : `Billing\InvoiceManager::generate()` refactorée (était vulnérable : SELECT MAX sans transaction, pas de retry). Eshop360 non modifié (déjà correct depuis P0 `2026_04_04_100002`).
- **ADR** : `docs/adr/ADR-006-invoice-numbering-atomicity.md` — 4 alternatives rejetées (séquence DB, advisory lock, ON CONFLICT, UUID).
- **Tests nouveaux** : 6 (3 Eshop360 + 3 Billing) — structural, DB-level UNIQUE, happy path distinct numbers.
- **Source** : lot L2 SENSIBLE, branche `feat/billing-invoice-number-atomicity`, audit ISSUE-10.

## 2026-04-23 — R-004 fermé : idempotence commissions employés

- **Décision** : défense en profondeur 2 couches + absorption silencieuse de la race (guard applicatif `exists()` + contrainte UNIQUE SGBD + `try/catch UniqueConstraintViolationException` avec `Log::info`).
- **Chantiers** :
  1. `HRService::calculateCommissionForSale` durci avec try/catch sur la violation UNIQUE → race absorbée en `Log::info`, plus de 500.
  2. `HRService::recordCommission()` (orpheline, 0 appelant prod, sans guard) supprimée.
  3. `PROTECTED_AREAS.md` corrigé (`CommissionService` inexistant → `HRService`).
- **ADR** : `docs/adr/ADR-005-commission-idempotency-strategy.md`.
- **Tests nouveaux** : 3 (CommissionIdempotenceTest) — structure pattern, UNIQUE enforced, absorption gracieuse.
- **Source** : lot L2 SENSIBLE, branche `feat/eshop360-commission-idempotence-hardening`, audit ISSUE-02.

## 2026-04-22 — R-003 fermé : intégrité solde portefeuille (4 chantiers)

- **Décision** : défense en profondeur 3 couches (CHECK SGBD `wallet_balance >= 0` + point d'entrée unique `FinanceService` + lock pessimiste `WalletDriver`).
- **Chantiers** :
  1. Migration `2026_04_22_110001` avec CHECK constraint (MySQL/PG, no-op SQLite).
  2. `WalletDriver::initiate/refund` : check + mutation sous `lockForUpdate()` dans `DB::transaction`. Nouvelle exception typée `InsufficientWalletBalanceException`.
  3. `ChannelPortalCustomerController::walletTopup` refactoré → passe par `FinanceService::creditWallet()`.
  4. `SaleController::storeReturn` refund=wallet refactoré → idem.
- **ADR** : `docs/adr/ADR-004-wallet-integrity-strategy.md`.
- **Tests nouveaux** : 7 tests feature (WalletIntegrityTest).
- **Source** : lot L1 CRITIQUE, branche `feat/eshop360-wallet-integrity`, audit ISSUE-03.

## 2026-04-22 — R-002 fermé : idempotence webhooks (Billing + Eshop360)

- **Décision** : défense en profondeur à 3 couches (UNIQUE SGBD + guard applicatif fast-path + HMAC signature) appliquée uniformément aux deux surfaces webhook.
- **Billing** : ajout de `billing_webhook_logs.idempotency_key VARCHAR(128) NULLABLE UNIQUE`, guard `try/catch UniqueConstraintViolationException` dans `WebhookController::handle()` — replay retourne 200 OK sans retraiter.
- **Eshop360** : index existant sur `deduplication_key` converti en UNIQUE, ferme la race window du check applicatif de `WebhookService::dispatch()`.
- **ADR** : `docs/adr/ADR-003-webhook-idempotency-strategy.md`.
- **Effet de bord** : `GatewayManager` dé-finalisé pour permettre le mocking en test.
- **Source** : lot L1 pilote, branche `feat/billing-webhook-idempotence`, audit ISSUE-04.

## 2026-04-22 — R-001 fermé : stratégie de concurrence stock validée

- **Décision** : le risque R-001 (race condition stock) était en réalité déjà corrigé en code (lockForUpdate + DB::transaction + refresh + guard) depuis l'audit go-live ; le lot consiste à documenter et tester.
- **ADR** : `docs/adr/ADR-002-stock-concurrency-strategy.md` — défense en profondeur 3 couches (lock applicatif + CHECK MySQL + UNIQUE schéma)
- **Tests ajoutés** : 5 unit (structure, séquentiel, rollback, multi-tenant, insufficient-first-sale) + 2 feature (migration + CHECK MySQL)
- **Limite assumée** : SQLite :memory: ne reproduit pas la race physique ; les tests unit valident la structure du code et le comportement séquentiel, pas la concurrence OS. Stress-test MySQL parallèle reporté.
- **Source** : lot L1 pilote du pack vibecoding, branche `test/eshop360-stock-concurrency-coverage`

## 2026-04-22 — R-102 fermé : retrait de FeatureGate deprecated

- **Décision** : suppression de `Modules\Eshop360\Services\FeatureGate` (wrapper @deprecated, 0 appelant production)
- **Canonique** : `Modules\Billing\Services\FeatureRegistry` (source HookRegistry, 48 features Eshop360 registrées), middleware `EnsureFeature`
- **Validation** : suite pest complète doit rester ≥ 625 passed (1 test obsolète supprimé)
- **Source** : lot du pack vibecoding, branche `refactor/eshop360-remove-feature-gate`, audit E-10 du comparatif

## 2026-04-22 — R-104 fermé : trait BelongsToInstance unifié

- **Décision** : suppression de `app/Models/Concerns/BelongsToInstance.php` (alias orphelin 0 usage)
- **Canonique** : `Modules\Core\Database\Traits\BelongsToInstance` (63 modèles)
- **Validation** : `InstanceScopeSafetyTest` (2/2) + suite complète 626 passed (2 échecs pré-existants non liés : ChannelIsolationTest, EshopSettingsServiceTest isolation cache)
- **Source** : lot pilote du pack vibecoding, branche `refactor/core-unify-belongs-to-instance`

## 2026-04-22 — Installation du pack vibecoding

- **Décision** : adoption d'un pipeline qualité local (Pint + PHPStan + Deptrac + Pest) imposé par hooks Git
- **Impact** : tous les nouveaux commits doivent passer Pint, PHPStan, et la convention Conventional Commits avec scope obligatoire
- **Baseline** : Deptrac et PHPStan baselines capturent l'existant — seul le NOUVEAU code doit être propre
- **ADR** : à créer dans `docs/adr/ADR-001-pipeline-qualite-local.md`

## 2026-04-04 — Architecture cible plateforme modulaire

- **Source** : `docs/Ins/b360_evolution_strategy.md`
- **Décision** : ne pas réécrire Eshop360, mais le découper progressivement par sous-domaines
- **Phases** : Catalog/Channel → Inventory/Sales → Finance/CRM/HR
- **Règle** : nouveau module ne peut pas `use` un modèle Eloquent d'un autre module

## 2026-04-06 — Multi-currency MVP fixé

- **Source** : `docs/STATUS.md`
- **Décision** : symétrisation `exchange_rate` sur `eshop_payments`
- **Validation** : 18/18 tests Currency verts

## 2026-04-06 — Double caisse cross-channel corrigée

- **Source** : `docs/STATUS.md` D-1
- **Décision** : `CashRegisterService::open()` ferme TOUTES les caisses ouvertes du user (pas seulement celles du même channel)
- **Validation** : 3 nouveaux tests TDD

---

## Convention

Chaque entrée :
- Date
- Décision en une phrase
- Impact concret
- Source (ADR, audit, conversation, PR)
