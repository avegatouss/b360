# OPEN_RISKS — B360

> Risques techniques connus, suivi vivant. Mise à jour : **2026-04-22 19:08:13    **

---

## CRITIQUE

_(aucun risque critique ouvert — R-001, R-002, R-003, R-004 fermés le 2026-04-22 et 2026-04-23. Voir section **FERMÉ** ci-dessous.)_

## MAJEUR

### R-101 — Eshop360 monolithique

- **Source** : cartographie 2026-04-04
- **Constat** : 89 modèles / 81 contrôleurs / 54 services / 146 migrations / ~22 276 LOC (chiffres 2026-04-23)
- **Impact** : maintenance difficile, couplage fort, empêche un module tiers (Menuiserie360) de consommer proprement une partie d'Eshop360
- **Plan** : extraction progressive en 13 sous-domaines (`Catalog`, `CRM`, `Channel`, `Pricing`, `Inventory`, `Promotions`, `Purchasing`, `Sales`, `Finance`, `HR`, `Communication`, `Projects`, `Reporting`) selon ADR-008 — stratégie C (hybride `Modules/Eshop360/Domain/<X>/` + deptrac sub-layers, promotion en vrais modules Laravel différée jusqu'à besoin externe concret)
- **Statut** : **en cours** — Phase 0 (R-001..R-004) et Phase 1 (R-102, R-103, R-104, R-201, R-202, R-301) livrées, Phase 2 démarrée
- **Sous-lots** :
  - ✅ **S0 — Préparation** (2026-04-23) : 13 layers deptrac intra-Eshop360 + ruleset permissive + ADR-008
  - ✅ **S1 — Catalog** (2026-04-23) : 7 modèles déplacés sous `Domain/Catalog/Models/` (Product, Category, Brand, ProductGroup, ProductTax, ProductVariation, Tax) + stubs d'alias rétrocompatibles dans `Models/` + ruleset `EshopCatalog` restreinte (socles + Eshop360 temp) + ADR-009. 0 régression (659 passed).
  - ✅ **S2 — CRM** (2026-04-23) : 4 modèles déplacés sous `Domain/CRM/Models/` (Customer, CustomerGroup, CustomerDue, CustomerTransaction) + stubs d'alias + ruleset `EshopCRM` restreinte + ADR-010. 0 régression (659 passed).
  - ✅ **S3 — Channel** (2026-04-23) : 4 modèles déplacés sous `Domain/Channel/Models/` (DistributionChannel, ChannelProductPrice, ChannelMarginLog, ChannelUser) + stubs d'alias + ruleset `EshopChannel` restreinte + ADR-011. Décision architecturale : `BelongsToChannel` + `ChannelScope` restent dans `Database/Traits/Scopes/` (infrastructure partagée intra-Eshop360, évite dépendance circulaire Catalog↔Channel). 0 régression (659 passed).
  - ✅ **S4 — Pricing normalisation** (2026-04-23) : **aucun fichier déplacé** — `Modules/Eshop360/Pricing/` reste à son emplacement historique (sous-domaine déjà auto-contenu sous son propre namespace avec 23 classes). Seule action : ruleset deptrac `EshopPricing` resserrée de permissive → `socles + EshopCatalog + Eshop360 (transitoire)`. Pricing confirmé feuille logique (0 dépendance vers CRM/Channel/Inventory/Sales/Finance). ADR-012. 0 régression.
  - ⏳ S5 — Inventory (déjà partiellement extrait, **L1**, 5 jours)
  - ⏳ S5 — Inventory (déjà partiellement extrait, **L1**, 5 jours) ← prochain
  - ⏳ S6 — Promotions (2 jours)
  - ⏳ S7 — Purchasing (3 jours)
  - ⏳ S8 — Sales (**L1**, 7 jours)
  - ⏳ S9 — Finance (**L1**, 5 jours)
  - ⏳ S10 — HR (2 jours)
  - ⏳ S11 — Communication + Projects + Reporting (3 jours)
  - ⏳ S12 — Clôture R-101 (2 jours)
- **Effort total** : ~44 jours étalés (9-10 semaines plein temps, 4-5 mois à 20%)
- **ADR** : `docs/adr/ADR-008-eshop360-subdomain-decomposition-strategy.md`

## FERMÉ

### R-103 — Double système Codifarm / DistributionChannel (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` + `docs/Ins/b360_evolution_strategy.md` §2.3.
- **Constat au jour du lot** : la migration avait déjà été exécutée (chaîne `2026_03_16_100002_migrate_codifarm_to_channels` + `2026_03_16_100003_drop_codifarm_tables_and_columns` — statut `Ran` confirmé par `migrate:status`). Les tables `eshop_codifarm_margin_config` et `eshop_codifarm_margin_logs` ont été supprimées, les colonnes `orders.is_codifarm` et `products.sale_price_codifarm` aussi. Aucun code applicatif actif (Services/Controllers/Models/Domain) ne référence la structure legacy. Seules subsistent des mentions du mot « codifarm » dans les Seeders de démo et les Tests comme **nom métier** (`[DEMO] CODIFARM` = nom d'un grossiste pharmaceutique ivoirien utilisé comme donnée de canal de démo, slug `demo-codifarm`) — acceptables car ce sont des données, pas de la structure technique.
- **Résolution** : fermeture formelle du risque + verrouillage anti-régression via ADR-007 et tests structurels.
- **Tests nouveaux** : `Modules/Eshop360/Tests/Feature/CodifarmLegacyRemovalTest` (3 tests) :
  - Les tables legacy `eshop_codifarm_margin_config` et `eshop_codifarm_margin_logs` n'existent plus.
  - Les colonnes `eshop_orders.is_codifarm` et `eshop_products.sale_price_codifarm` n'existent plus.
  - Scan récursif de `Modules/Eshop360/{Services,Http/Controllers,Models,Domain}/` → aucune référence à `codifarm_margin_config`, `codifarm_margin_log`, `is_codifarm`, `sale_price_codifarm`, `CodifarmMarginConfig`, `CodifarmMarginLog`. Toute PR qui réintroduirait ces tokens casse ce test.
- **ADR** : `docs/adr/ADR-007-codifarm-channel-consolidation.md`.
- **Canon** : `DistributionChannel` + `ChannelMarginLog` + `ChannelProductPrice` (générique, N canaux par instance, support hub/portail/pricing per-canal).
- **Commit** : branche `chore/eshop360-close-codifarm-consolidation`.

### R-201 — InventoryX squelette non chargé (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` + `docs/cartographie/01_modules/inventoryx.md` + `docs/Ins/b360_evolution_strategy.md` task #22.
- **Constat** : `Modules/InventoryX/` contenait **0 fichier PHP** et **0 fichier de config** — uniquement 10 dossiers vides (`Console/Commands`, `Database/Seeders`, `Resources/views/partials`, `Tests/Feature`). Pas de `module.json`, pas de `composer.json`, pas de ServiceProvider, absent de `modules_statuses.json` → jamais chargé par le système de modules. 0 référence depuis le code prod (grep `Modules\InventoryX` → 0 match applicatif).
- **Décision** : **SUPPRIMER**. Preuves convergentes :
  - Roadmap évolution explicite (`docs/Ins/b360_evolution_strategy.md` task #22 « Supprimer module InventoryX (vide) — 30min »).
  - La cartographie module (`docs/cartographie/01_modules/inventoryx.md`) recommandait : « Soit développer selon le besoin identifié, soit supprimer pour éviter la confusion ».
  - La phase 2 d'extraction Eshop360 créera un *nouveau* module Inventory à partir du code `eshop_stocks/stock_movements/warehouses`, pas une résurrection de ce squelette.
- **Actions** :
  - Suppression complète de `Modules/InventoryX/` (10 dossiers vides).
  - Nettoyage des références actives : `rector.php` (skip path), `phpstan.neon` (excludePath) + `tools/phpstan/phpstan.neon` (template désormais synchronisé avec root), `deptrac.yaml` (layer + ruleset + graphviz group L3) + `tools/deptrac/deptrac.yaml` (template synchronisé), `scripts/memory/bootstrap-from-existing.sh` (case InventoryX + section MOYEN R-201 + ligne « Couche future »), `.vscode/settings.json` (cSpell.words), `docs/context/PROJECT_DIGEST.md`.
  - Suppression des docs dédiés : `docs/cartographie/01_modules/inventoryx.md`, `docs/audits/01_modules/inventoryx.md`.
  - Effet de bord propre : synchronisation de `tools/phpstan/phpstan.neon` et `tools/deptrac/deptrac.yaml` avec leurs versions root (drift accumulé depuis l'install du pack corrigé).
- **Validation** : 0 référence active restante dans le code. Références résiduelles dans les docs historiques (`docs/AUDIT-ARCHITECTURE-GO-LIVE.md`, `docs/audits/`, `docs/cartographie/0*.md`, `docs/audit_comparatif_final.md`) conservées en tant qu'archive des décisions passées.
- **Commit** : branche `chore/eshop360-remove-inventoryx-skeleton`.

### R-301 — Event PasswordReset orphelin (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-12
- **Constat** : `ResetPasswordController` dispatche `Illuminate\Auth\Events\PasswordReset` après chaque réinitialisation de mot de passe. Aucun listener n'était enregistré → event orphelin, aucun trace d'audit. Les events `Login` et `Failed` étaient déjà couverts (via `LogSuccessfulLogin` et `LogFailedLogin`).
- **Résolution** :
  - Nouveau listener `Modules/Auth/Listeners/LogPasswordReset.php` : écrit dans `login_logs` avec `status = 'password_reset'` (pattern identique à `LogSuccessfulLogin`).
  - Enregistrement dans `Modules/Auth/Providers/EventServiceProvider.php`.
  - Ajout de `Modules\Auth\Providers\EventServiceProvider` dans `Modules/Auth/module.json` (n'y figurait pas — raison pour laquelle Login/Failed fonctionnaient via auto-discovery mais le nouveau listener n'aurait pas été pris sans registration explicite).
  - Migration `2026_04_23_100001_extend_login_logs_status_for_password_reset` : convertit la colonne `login_logs.status` de ENUM('success','failed','locked') en VARCHAR(30) pour accepter `password_reset` et permettre de futurs statuts (logout, session_expired, etc.) sans migration enum. Branches SQLite (rebuild), MySQL (MODIFY COLUMN), PostgreSQL (ALTER TYPE).
- **Tests** : `Modules/Auth/Tests/Feature/PasswordResetAuditTest` (3 tests) — registration du listener via Event::getListeners, dispatch event crée un LoginLog avec bon status, handle direct du listener.
- **Commit** : branche `feat/auth-log-password-reset`.

### R-202 — Numéro facture non atomique (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-10
- **Constat** : deux surfaces. Eshop360 `InvoiceService::createFromItems()` et `OrderService::createFromItems()` étaient **déjà corrects** (DB::transaction + retry sur QueryException 1062 + UNIQUE `(instance_id, invoice_number)` via migration P0 `2026_04_04_100002`). Billing `InvoiceManager::generate()` était **vulnérable** : SELECT MAX hors transaction, `Invoice::create()` sans retry → collision = 500 client non rattrapé.
- **Résolution** : application uniforme du pattern `DB::transaction + boucle for 1..MAX_NUMBER_ATTEMPTS + catch QueryException 1062 + régénération du numéro` sur les 3 services. Voir ADR-006.
  - Billing `InvoiceManager::generate()` refactorée (+constante MAX_NUMBER_ATTEMPTS=5, +DB::transaction, +boucle for, +catch QueryException, +RuntimeException de sécurité si MAX atteint).
  - Eshop360 non modifié (pattern déjà en place depuis P0).
- **Tests nouveaux** (6) :
  - `Modules/Eshop360/Tests/Feature/InvoiceNumberAtomicityTest` (3 tests) : structural (retry + catch + DB::transaction + 1062 + regenerate), DB-level UNIQUE par (instance_id, invoice_number), happy path numéros distincts.
  - `Modules/Billing/Tests/Feature/InvoiceNumberAtomicityTest` (3 tests) : structural, DB-level UNIQUE global, happy path séquentiel.
- **ADR** : `docs/adr/ADR-006-invoice-numbering-atomicity.md` — 4 alternatives rejetées (séquence DB, advisory lock, `ON CONFLICT DO NOTHING`, UUID), contraintes imposées au futur.
- **Commit** : branche `feat/billing-invoice-number-atomicity`.

### R-004 — Commissions employés dupliquées (fermée 2026-04-23)

- **Source** : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-02
- **Constat** : le code avait déjà un guard applicatif (`exists()`) et la migration P0 `2026_04_04_200001` avait ajouté la contrainte UNIQUE `(order_id, employee_id)` sur `eshop_employee_commissions`. Manquait : l'absorption gracieuse de la race (une requête concurrente gagnant entre `exists()` et `create()` faisait remonter `UniqueConstraintViolationException` en 500), la suppression de la méthode orpheline `HRService::recordCommission()` (0 appelant prod, sans guard), et les tests de la couverture complète.
- **Résolution** : défense en profondeur 2 couches + absorption silencieuse (voir ADR-005).
  - `HRService::calculateCommissionForSale()` wrap désormais `EmployeeCommission::create()` dans un `try/catch UniqueConstraintViolationException` → race absorbée, `Log::info` pour observabilité, pas de 500.
  - `HRService::recordCommission()` (orpheline, sans guard) supprimée — piège futur écarté.
- **Tests** : `Modules/Eshop360/Tests/Feature/CommissionIdempotenceTest` (3 tests) — structurel (guard + catch + import exception + Log::info présents), DB-level UNIQUE enforced, graceful absorption via le flow réel. `P0SafetyGuardsTest::test_commission_idempotente_si_ordre_completed_deux_fois` conservé.
- **Correction documentation** : `docs/governance/PROTECTED_AREAS.md` ligne 94 corrigée (référençait un `CommissionService` inexistant → `HRService::calculateCommissionForSale`).
- **ADR** : `docs/adr/ADR-005-commission-idempotency-strategy.md`.
- **Commit** : branche `feat/eshop360-commission-idempotence-hardening`.

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

_(aucun risque moyen ouvert — R-201 et R-202 fermés le 2026-04-23.)_

## FAIBLE

_(aucun risque faible ouvert — R-301 fermé le 2026-04-23, voir section **FERMÉ**.)_

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
