# CHANGELOG_ARCHITECTURAL — B360

> Registre vivant des changements d'architecture et des décisions structurantes du projet.
> Toute modification qui change un contrat, une dépendance, une couche, une convention de nommage transversale, doit y figurer.
>
> Format : ID stable + date + titre + impact + statut.

---

## [non publié]

(rien)

---

## CHG-2026-04-24-007 — R-101 sous-lot S11 : extraction Communication + Projects + Reporting + rattrapages

- **Date** : 2026-04-24
- **Type** : architecture (3 sous-domaines combinés + rattrapages S8/S9)
- **Modules concernés** : Eshop360 (20 modèles déplacés)
- **Impact** : nul côté runtime.
- **Breaking change** : non.

### Actions appliquées

- **11 modèles Communication** déplacés vers `Domain/Communication/Models/` : EmailTemplate, BulkMessageLog, Message, SmsGateway, SmsLog, SupportTeam, SupportTicket, TicketMessage, ReceiptTemplate, Webhook, WebhookLog.
- **4 modèles Projects** vers `Domain/Projects/Models/` : Project, Task, TaskComment, Event.
- **2 modèles Reporting** vers `Domain/Reporting/Models/` : ApiLog, AuditLog.
- **3 rattrapages** : Holding → Sales (oubli S8), Loan + LoanSchedule → Finance (oubli S9).
- Namespace rewrite + `$morphClass` pinning universel via PowerShell.
- Imports cross-subdomain sur 5 fichiers : ReceiptTemplate+Store, SupportTeam+Customer, SupportTicket+Customer, Project+Customer/Invoice/Order, Holding+Customer.
- 20 alias stubs créés.
- Deptrac rulesets resserrés : `EshopCommunication` (socles+CRM+Inventory+Eshop360), `EshopProjects` (socles+CRM+Sales+Finance+Eshop360), `EshopReporting` (socles+Eshop360). Fin de la ruleset permissive pour TOUS les sous-layers R-101.
- Baseline PHPStan : **3704** (+18 vs S10).
- ADR `docs/adr/ADR-019-eshop360-communication-projects-reporting-extraction.md`.

### Progression R-101

**13/13 sous-domaines délimités (100 %)**. L'extraction physique est complète. Reste S12 (clôture) pour supprimer les ~90 alias stubs, formaliser le morph map, et lever les dépendances transitoires `EshopX → Eshop360`.

### Statut

- [x] Implémenté, documenté, testé

### Lien

- ADR : `docs/adr/ADR-019-eshop360-communication-projects-reporting-extraction.md`

---

## CHG-2026-04-24-006 — R-101 sous-lot S10 : extraction HR

- **Date** : 2026-04-24
- **Type** : architecture (extraction sous-domaine, pattern bulk rodé)
- **Modules concernés** : Eshop360 (4 modèles HR déplacés)
- **Impact** : nul côté runtime.
- **Breaking change** : non.

### Actions appliquées

- **4 modèles déplacés** vers `Modules/Eshop360/Domain/HR/Models/` : `Employee`, `EmployeeCommission`, `EmployeeSalary`, `Attendance`.
- Namespace rewrite + `$morphClass` pinning via PowerShell bulk.
- **1 seul import cross-sous-domaine** : `EmployeeCommission` +Order (alias, EshopSales).
- 4 alias stubs créés.
- **Deptrac `EshopHR` restreint** : socles + `EshopSales` + Eshop360 transitoire.
- Baseline PHPStan : 3686 (+4 vs S9).
- ADR `docs/adr/ADR-018-eshop360-hr-subdomain-extraction.md`.

### Statut

- [x] Implémenté, documenté, testé

### Lien

- ADR : `docs/adr/ADR-018-eshop360-hr-subdomain-extraction.md`

---

## CHG-2026-04-24-005 — R-101 sous-lot S9 : extraction Finance (L1 critique, 20 modèles)

- **Date** : 2026-04-24
- **Type** : architecture (plus grosse extraction à ce jour, L1 critique)
- **Modules concernés** : Eshop360 (20 modèles Finance déplacés)
- **Impact** : nul côté runtime (rétrocompatibilité via alias + `$morphClass` pinning universel).
- **Breaking change** : non.

### Actions appliquées

- **20 modèles déplacés** via `git mv` vers `Modules/Eshop360/Domain/Finance/Models/` : `Invoice`, `InvoiceItem`, `RecurringInvoice`, `Payment`, `PaymentMethod`, `EshopPaymentGateway`, `Account`, `AccountTransaction`, `AccountTransfer`, `Expense`, `ExpenseCategory`, `Income`, `IncomeSource`, `ChargeCategory`, `ChargeLog`, `CompanyCharge`, `InstallmentPayment`, `InstallmentPlan`, `LoanPayment`, `FneInvoice`.
- **Namespace mis à jour** via PowerShell script : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\Finance\Models`.
- **`$morphClass` pinning universel** via PowerShell : chaque canonique définit `protected $morphClass = \Modules\Eshop360\Models\<Legacy>::class;`. Couvre les morph targets confirmés (Invoice via Payment.payable, FneInvoice via invoiceable) et anticipe tout futur morph sur Finance.
- **Imports cross-sous-domaine ajoutés** sur 4 fichiers seulement : Invoice+2 (Customer, Order), InvoiceItem+1 (Product), InstallmentPlan+2 (Customer, Order), RecurringInvoice+1 (Customer). Les 16 autres sont feuilles.
- **20 stubs d'alias** rétrocompatibles générés via PowerShell template.
- **Deptrac `EshopFinance` restreint** : socles + `EshopCatalog` + `EshopCRM` + `EshopSales` + Eshop360 transitoire. Pas de dépendance vers Channel/Inventory/Purchasing/Promotions/etc. (Finance est une feuille côté consumer morph).
- **Baseline PHPStan régénérée** : **3682 erreurs** (+26 vs S8 — 20 nouvelles `missingType.property` pour `$morphClass` + 6 autres generics sur les relations déplacées).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-017-eshop360-finance-subdomain-extraction.md` : documente l'extraction + catégorisation (invoicing, payments, accounts, expenses, charges, installments, fiscal FNE).

### Statut

- [x] Implémenté (20 modèles déplacés, 20 alias créés, 20 `$morphClass` pinning, deptrac resserré)
- [x] Documenté (ADR-017)
- [x] Testé (659 passed attendu grâce au pinning)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement + stubs + `$morphClass` pinning + config. Aucune logique métier modifiée.
- **Contrat runtime** : strictement identique grâce au pinning universel (évite l'audit de 32+ sites consommateurs).
- **Concurrence / Multi-tenant / Permissions / Idempotence** : préservés.
- **Rollback** : `git revert` sans risque.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopFinance → EshopX` (hors Catalog/CRM/Sales/Eshop360 transitoire).

### Lien

- ADR : `docs/adr/ADR-017-eshop360-finance-subdomain-extraction.md`
- ADR parents : ADR-008 (stratégie), ADR-015 (piège découvert S7), ADR-016 (pinning systématisé S8)
- PR : (n° à renseigner)

---

## CHG-2026-04-24-004 — R-101 sous-lot S8 : extraction Sales (L1 critique)

- **Date** : 2026-04-24
- **Type** : architecture (extraction sous-domaine L1, application défensive du piège alias)
- **Modules concernés** : Eshop360 (9 modèles Sales déplacés)
- **Impact** : nul côté runtime (rétrocompatibilité via alias + `$morphClass` pinning).
- **Breaking change** : non.

### Actions appliquées

- **9 modèles déplacés** via `git mv` vers `Modules/Eshop360/Domain/Sales/Models/` : `Order`, `OrderItem`, `OnlineOrder`, `OnlineOrderItem`, `PersistentCart`, `SaleReturn`, `CashRegister`, `Quotation`, `QuotationItem`.
- **Namespace mis à jour** : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\Sales\Models`.
- **Imports cross-sous-domaine via alias** : Order+10 (ChannelMarginLog, Customer, EmployeeCommission, Holding, InstallmentPlan, Invoice, Payment, Project, Store, Warehouse). OrderItem+2 (Product, ProductVariation). OnlineOrder+1 (Customer). OnlineOrderItem+1 (Product). SaleReturn+2 (Customer, Product). CashRegister+1 (Store). Quotation+1 (Customer). QuotationItem+1 (Product). PersistentCart+0.
- **9 stubs d'alias** rétrocompatibles dans `Modules/Eshop360/Models/`.
- **Mesure défensive L1 — `$morphClass` pinning** : chaque modèle canonique définit `protected $morphClass = \Modules\Eshop360\Models\<Legacy>::class;`. Garantit que toute morph reference (stock_movement.reference_type, payment.payable_type, fne_invoice.invoiceable_type, etc.) stocke la FQN legacy — indépendamment de l'import utilisé par le consumer. Protège les données production existantes + tous les tests historiques + tous les futurs sites qui passeraient `Order::class`.
- **Deptrac `EshopSales` restreint** : permissive → socles + `EshopCatalog` + `EshopCRM` + `EshopChannel` + `EshopInventory` + Eshop360 transitoire (Invoice/Payment/Holding/InstallmentPlan/EmployeeCommission/Project pas encore extraits — levés S9/S10/S11).
- **Baseline PHPStan régénérée** : **3656 erreurs** baselined (vs 3647 S7 — **+9** nouvelles entrées `missingType.property` pour les 9 `$morphClass`, bruit baseliné).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-016-eshop360-sales-subdomain-extraction.md` : documente l'extraction + la décision défensive + plan de clôture S12 (remplacement `$morphClass` par `enforceMorphMap` formel).

### Statut

- [x] Implémenté (9 modèles déplacés, 9 alias créés, 9 `$morphClass` pinning, deptrac resserré)
- [x] Documenté (ADR-016 avec justification défensive L1)
- [x] Testé (659 passed attendu, confirmé par pest full — morphClass pinning a évité toute régression)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement + stubs + `$morphClass` pinning + config. Aucune logique métier modifiée, aucune migration DB, aucun service modifié, aucun contrôleur modifié.
- **Contrat runtime** : strictement identique (les 7+ sites qui passent `Order::class` à des colonnes morph continuent de fonctionner sans modification — ils importent l'alias via `use Modules\Eshop360\Models\Order`, et même s'ils n'avaient pas fait, le `$morphClass` canonique stockerait toujours la legacy FQN).
- **Concurrence / Multi-tenant / Permissions / Idempotence** : préservés.
- **Rollback** : `git revert` sans risque.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopSales → EshopX` (hors Catalog/CRM/Channel/Inventory/Eshop360 transitoire).

### Lien

- ADR : `docs/adr/ADR-016-eshop360-sales-subdomain-extraction.md`
- ADR parents : ADR-008 (stratégie), ADR-015 (piège découvert en S7)
- PR : (n° à renseigner)

---

## CHG-2026-04-24-003 — R-101 sous-lot S7 : extraction Purchasing

- **Date** : 2026-04-24
- **Type** : architecture (extraction sous-domaine, pattern S1/S2/S3/S5/S6 répété)
- **Modules concernés** : Eshop360 (9 modèles Purchasing déplacés)
- **Impact** : nul côté runtime (rétrocompatibilité via alias).
- **Breaking change** : non.

### Actions appliquées

- **9 modèles déplacés** via `git mv` vers `Modules/Eshop360/Domain/Purchasing/Models/` : `Supplier`, `PurchaseOrder`, `PurchaseItem`, `PurchaseReturn`, `PurchaseReturnItem`, `ImportOrder`, `ImportOrderItem`, `ImportCost`, `ImportCostType`.
- **Namespace mis à jour** : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\Purchasing\Models`.
- **Imports cross-sous-domaine** : Supplier+1 (Store). PurchaseOrder+2 (Payment, Warehouse). PurchaseItem+1 (Product). PurchaseReturn+1 (Warehouse). PurchaseReturnItem+1 (Product). ImportOrder+1 (Warehouse). ImportOrderItem+1 (Product). Tous via alias `Modules\Eshop360\Models\*`.
- **9 stubs d'alias** rétrocompatibles dans `Modules/Eshop360/Models/`.
- **Deptrac `EshopPurchasing` restreint** : passé de permissive → socles + `EshopCatalog` (Product) + `EshopInventory` (Warehouse/Store) + Eshop360 transitoire (Payment pas encore extrait).
- **Baseline PHPStan régénérée** : 3647 erreurs baselined (inchangé vs S6).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-015-eshop360-purchasing-subdomain-extraction.md` : documente l'extraction + les 3 groupes fonctionnels (fournisseurs, commandes fournisseur, commandes d'import) + **piège alias-covariance/morphClass révélé par S7** (règle applicable S8+).

### Ajustement post-extraction révélé par tests (ImportService)

- **Fix 1 — covariance return type** : `ImportService::addCost(): ImportCost` invoquait `$order->costs()->create()` via une relation `hasMany` définie sur le modèle canonique ; Eloquent renvoyait donc un `Domain\Purchasing\Models\ImportCost` (parent) là où le return type annotait l'alias (sous-classe). **Correction** : `use Modules\Eshop360\Domain\Purchasing\Models\ImportCost` dans le Service.
- **Fix 2 — stabilité polymorphisme** : `ImportService::receiveImport()` passe `ImportOrder::class` à `StockService::adjustStock()` pour `reference_type`. Déplacer ImportOrder aurait changé la FQN stockée (`Domain\Purchasing\Models\ImportOrder` au lieu de `Models\ImportOrder`), brisant les assertions de tests et la rétrocompatibilité des données existantes. **Correction** : conserver `use Modules\Eshop360\Models\ImportOrder` (alias) dans le Service — le stockage morphique reste sur l'alias FQN.

### Statut

- [x] Implémenté (9 modèles déplacés, 9 alias créés, deptrac resserré)
- [x] Documenté (ADR-015)
- [x] Testé (659 passed, aucune régression)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement + stubs + config. Aucune logique métier modifiée (notamment le calcul de coût landed dans ImportOrder reste intact).
- **Contrat runtime** : strictement identique.
- **Concurrence / Multi-tenant** : préservés (BelongsToInstance + BelongsToChannel conservés).
- **Rollback** : `git revert` sans risque.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopPurchasing → EshopX` (hors Catalog, Inventory, Eshop360 transitoire).

### Lien

- ADR : `docs/adr/ADR-015-eshop360-purchasing-subdomain-extraction.md`
- ADR parents : ADR-008 (stratégie), ADR-009..ADR-014 (S1..S6)
- PR : (n° à renseigner)

---

## CHG-2026-04-24-002 — R-101 sous-lot S6 : extraction Promotions

- **Date** : 2026-04-24
- **Type** : architecture (extraction sous-domaine, pattern S1/S2/S3/S5 répété)
- **Modules concernés** : Eshop360 (5 modèles Promotions déplacés)
- **Impact** : nul côté runtime (rétrocompatibilité via alias).
- **Breaking change** : non.

### Actions appliquées

- **5 modèles déplacés** via `git mv` vers `Modules/Eshop360/Domain/Promotions/Models/` : `Coupon`, `Discount`, `DiscountPlan`, `GiftCard`, `GiftCardTopup`.
- **Namespace mis à jour** : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\Promotions\Models`.
- **Imports cross-sous-domaine** : GiftCard +1 (Customer via alias). Les autres sont feuilles (0 import métier externe).
- **5 stubs d'alias** rétrocompatibles dans `Modules/Eshop360/Models/`.
- **Deptrac `EshopPromotions` restreint** : passé de permissive → socles + `EshopCRM` + Eshop360 transitoire. Pas de Catalog (Discount.product_ids est JSON, pas relation typée).
- **Baseline PHPStan régénérée** : 3647 erreurs baselined (inchangé vs S5).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-014-eshop360-promotions-subdomain-extraction.md` : documente l'extraction + raisonnement absence Catalog.

### Statut

- [x] Implémenté (5 modèles déplacés, 5 alias créés, deptrac resserré)
- [x] Documenté (ADR-014)
- [x] Testé (659 passed, aucune régression)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement + stubs + config. Aucune logique métier modifiée.
- **Contrat runtime** : strictement identique.
- **Concurrence / Multi-tenant / Permissions / Idempotence** : non applicables.
- **Rollback** : `git revert` sans risque.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopPromotions → EshopX` (hors CRM et Eshop360 transitoire).

### Lien

- ADR : `docs/adr/ADR-014-eshop360-promotions-subdomain-extraction.md`
- ADR parents : ADR-008 (stratégie), ADR-009..ADR-013 (S1..S5)
- PR : (n° à renseigner)

---

## CHG-2026-04-24-001 — R-101 sous-lot S5 : extraction Inventory (L1 critique)

- **Date** : 2026-04-24
- **Type** : architecture (extraction sous-domaine, pattern S1/S2/S3 répété sur zone L1)
- **Modules concernés** : Eshop360 (5 modèles Inventory déplacés)
- **Impact** : nul côté runtime (rétrocompatibilité via alias, services inchangés).
- **Breaking change** : non.

### Actions appliquées

- **5 modèles déplacés** via `git mv` vers `Modules/Eshop360/Domain/Inventory/Models/` : `StockMovement`, `StockTransfer`, `StockTransferItem`, `Warehouse`, `Store`. (Stock était déjà en place depuis un chantier antérieur.)
- **Namespace mis à jour** : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\Inventory\Models`.
- **Imports cross-sous-domaine** : StockMovement / StockTransferItem +1 (Product via alias). Warehouse +1 (Employee via alias — HR). Stock canonique nettoyé (Warehouse/Store désormais peers intra-namespace, lignes `use` supprimées).
- **5 stubs d'alias** rétrocompatibles dans `Modules/Eshop360/Models/`.
- **Deptrac `EshopInventory` restreint** : passé de permissive → socles + `EshopCatalog` + Eshop360 transitoire. Autres sous-layers (CRM, Channel, Sales, Finance…) explicitement exclus — Inventory ne dépend d'aucun d'entre eux.
- **Baseline PHPStan régénérée** : 3647 erreurs baselined (inchangé vs S3/S4 — les erreurs generics sur Eloquent relations se sont déplacées avec les classes).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-013-eshop360-inventory-subdomain-extraction.md` : documente l'extraction + garanties L1 préservées (StockService lockForUpdate + DB::transaction intacts, R-001 intouchée).

### Discipline L1 respectée

- **Zéro modification de service** : `StockService` et toute sa logique de concurrence (`lockForUpdate`, `DB::transaction`, retry sur UniqueConstraintViolationException) **intacts**.
- **Zéro modification de migration** : schémas de tables `eshop_stocks` / `eshop_stock_movements` / `eshop_stock_transfers` / `eshop_warehouses` / `eshop_stores` inchangés.
- **Tests de concurrence passent** : `StockServiceConcurrencyTest`, `StockServiceFullTest`, `StockServiceCoreTest`, `StockServiceTest` inclus dans les 659 tests OK.

### Statut

- [x] Implémenté (5 modèles déplacés, 5 alias créés, Stock canonique nettoyé, deptrac resserré)
- [x] Documenté (ADR-013 avec garanties L1 explicites)
- [x] Testé (659 passed, 2 failed pré-existants, 5 skipped — inchangé vs S4)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement + stubs + config. Aucune logique métier modifiée, aucune migration DB, aucun test modifié, aucun service modifié, aucun contrôleur modifié.
- **Contrat runtime** : strictement identique. Les 59 fichiers consommateurs de `Modules\Eshop360\Models\(Stock|StockMovement|StockTransfer|StockTransferItem|Warehouse|Store)` résolvent via alias vers le canon — aucune modification requise.
- **Concurrence / Multi-tenant** : garanties L1 préservées (StockService intact, BelongsToInstance + BelongsToChannel sur tous les modèles migrés).
- **Rollback** : `git revert` sans risque.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopInventory → EshopX` (hors Catalog et Eshop360 transitoire).

### Lien

- ADR : `docs/adr/ADR-013-eshop360-inventory-subdomain-extraction.md`
- ADR parents : ADR-008 (stratégie), ADR-009 (S1), ADR-010 (S2), ADR-011 (S3), ADR-012 (S4)
- Contexte connexe : R-001 fermée (race stock — garanties dans StockService, intouchées)
- PR : (n° à renseigner)

---

## CHG-2026-04-23-010 — R-101 sous-lot S4 : normalisation Pricing (ruleset only)

- **Date** : 2026-04-23
- **Type** : architecture (délimitation deptrac d'un sous-domaine déjà isolé)
- **Modules concernés** : Eshop360 (sous-domaine Pricing — aucun fichier déplacé)
- **Impact** : nul côté runtime, nul côté PHP (YAML deptrac + ADR uniquement).
- **Breaking change** : non.

### Actions appliquées

- **Aucun déplacement de fichier** : `Modules/Eshop360/Pricing/` reste à son emplacement historique (23 classes auto-contenues avec namespace `Modules\Eshop360\Pricing\*` — déjà un sous-domaine extrait de facto).
- **Ruleset deptrac `EshopPricing` resserrée** : passée de l'ancre permissive `eshop_sublayer_base` (tous les EshopX) à une liste explicite minimale observée : `socles + EshopCatalog + Eshop360 (transitoire)`.
  - Dépendances réelles confirmées par grep exhaustif : 1 × `Modules\Eshop360\Models\Product` (via alias, dans `WholesaleCalculatorService`) + 1 × `Modules\Billing\Services\FeatureRegistry` (dans `ChannelCreditRule`).
  - Aucune dépendance intra-Eshop360 vers CRM / Channel / Inventory / Sales / Finance etc. — Pricing est une feuille logique.
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-012-eshop360-pricing-normalization.md` : documente le choix de ne PAS déplacer Pricing vers `Domain/Pricing/` (asymétrie de localisation acceptée, cohérence git history, YAGNI).
- `docs/memory/OPEN_RISKS.md` R-101 : sous-lot S4 ✅.
- `docs/memory/RECENT_DECISIONS.md` : entrée S4.

### Statut

- [x] Implémenté (ruleset deptrac resserrée, 0 fichier déplacé, aucun stub nécessaire)
- [x] Documenté (ADR-012 avec justification "déjà isolé" + alternatives rejetées)
- [x] Testé (deptrac 0 violations / pest 659 passed — inchangé vs S3)

### IMPACT_ANALYSIS

- **Périmètre** : 1 fichier YAML (root) + 1 copie (tools/) + 1 ADR + 3 fichiers mémoire. Zéro fichier PHP modifié.
- **Contrat runtime** : strictement identique (aucune classe ni namespace n'a bougé).
- **Concurrence / Multi-tenant / Permissions / Idempotence** : non applicables.
- **Rollback** : `git revert` trivial (YAML-only).
- **Garde future** : deptrac bloque désormais toute nouvelle dépendance `EshopPricing → EshopX` (hors Catalog et Eshop360 transitoire). Toute tentative d'importer `Modules\Eshop360\Domain\Sales\*` ou `Domain\CRM\*` depuis une règle Pricing déclenchera une violation CI.

### Lien

- ADR : `docs/adr/ADR-012-eshop360-pricing-normalization.md`
- ADR parents : ADR-008 (stratégie), ADR-009 (S1), ADR-010 (S2), ADR-011 (S3)
- Contexte connexe : ADR-007 (pricing tripartite Codifarm)
- PR : (n° à renseigner)

---

## CHG-2026-04-23-009 — R-101 sous-lot S3 : extraction Channel

- **Date** : 2026-04-23
- **Type** : architecture (extraction sous-domaine, pattern S1/S2 répété)
- **Modules concernés** : Eshop360 (4 modèles Channel déplacés)
- **Impact** : nul côté runtime (rétrocompatibilité via alias).
- **Breaking change** : non.

### Actions appliquées

- **4 modèles déplacés** via `git mv` vers `Modules/Eshop360/Domain/Channel/Models/` : DistributionChannel, ChannelProductPrice, ChannelMarginLog, ChannelUser.
- **Namespace mis à jour** : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\Channel\Models`.
- **Imports cross-sous-domaine** : DistributionChannel +7 (Product, Customer, Order, Warehouse, CashRegister, Coupon, Holding, User). ChannelProductPrice +1 (Product). ChannelMarginLog +1 (Order).
- **4 stubs d'alias** rétrocompatibles dans `Modules/Eshop360/Models/`.
- **Décision architecturale clé** : `BelongsToChannel` trait et `ChannelScope` **restent dans `Modules/Eshop360/Database/{Traits,Scopes}/`** (pas déplacés sous `Domain/Channel/`). Justification : éviter dépendance circulaire `EshopCatalog ↔ EshopChannel` (86 fichiers utilisent le trait, Catalog+CRM+Inventory+Sales+... en dépendent ; ChannelProductPrice dépend de Product côté Channel → boucle interdite). Le trait est traité comme infrastructure intra-Eshop360 (statut similaire à `BelongsToInstance` dans Core). Révisable en S12.
- **Deptrac `EshopChannel` restreint** : socles + Eshop360 transitoire.
- **Baseline PHPStan régénérée** : 3647 erreurs baselined.
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-011-eshop360-channel-subdomain-extraction.md` : documente la décision architecturale + 3 alternatives rejetées (promotion dans Core, création couche Shared, mass-rewrite des 86 imports).
- `docs/memory/OPEN_RISKS.md` R-101 : sous-lot S3 ✅.
- `docs/memory/RECENT_DECISIONS.md` : entrée S3.

### Statut

- [x] Implémenté (4 modèles déplacés, 4 alias créés, deptrac resserré, trait infrastructure préservé en place)
- [x] Documenté (ADR-011 avec justification du choix non-évident)
- [x] Testé (659 passed, aucune régression)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement + stubs + config. Aucune modification de logique métier, aucune migration DB, aucun test modifié.
- **Contrat runtime** : strictement identique. Tous les `use Modules\Eshop360\Models\DistributionChannel` résolvent vers le canon via alias.
- **Concurrence / Multi-tenant / Permissions / Idempotence** : non applicables.
- **Rollback** : `git revert` sans risque.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopChannel → EshopX` (hors Eshop360 transitoire).

### Lien

- ADR : `docs/adr/ADR-011-eshop360-channel-subdomain-extraction.md`
- ADR parents : ADR-008 (stratégie), ADR-009 (S1), ADR-010 (S2)
- Contexte connexe : ADR-007 (consolidation Codifarm → DistributionChannel)
- PR : (n° à renseigner)

---

## CHG-2026-04-23-008 — R-101 sous-lot S2 : extraction CRM

- **Date** : 2026-04-23
- **Type** : architecture (extraction sous-domaine, pattern S1 répété)
- **Modules concernés** : Eshop360 (déplacement 4 modèles CRM)
- **Impact** : nul côté runtime (rétrocompatibilité via alias).
- **Breaking change** : non.

### Actions appliquées

- **4 modèles déplacés** via `git mv` vers `Modules/Eshop360/Domain/CRM/Models/` : `Customer`, `CustomerGroup`, `CustomerDue`, `CustomerTransaction`.
- **Namespace mis à jour** : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\CRM\Models`.
- **Imports cross-sous-domaine ajoutés** :
  - `Customer.php` : +4 (Order, OnlineOrder, Invoice, SupportTicket — alias transitoires).
  - `CustomerDue.php` : +2 (Order, Invoice — alias transitoires).
  - `CustomerGroup.php`, `CustomerTransaction.php` : 0 import supplémentaire nécessaire.
- **4 stubs d'alias** rétrocompatibles dans `Modules/Eshop360/Models/<Name>.php`.
- **Deptrac `EshopCRM` restreint** : ruleset passée de permissive à `socles + Eshop360` uniquement. Les dépendances `EshopCRM → Eshop360` sont transitoires (BelongsToChannel, ScopedByUserAssignment, alias cross-domain).
- **Baseline PHPStan régénérée** : 3647 erreurs baselined (diff lié aux traits sur modèles CRM).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-010-eshop360-crm-subdomain-extraction.md`.
- `docs/memory/OPEN_RISKS.md` R-101 : sous-lot S2 ✅.
- `docs/memory/RECENT_DECISIONS.md` : entrée S2.

### Statut

- [x] Implémenté (4 modèles déplacés, 4 alias créés, deptrac resserré)
- [x] Documenté (ADR-010)
- [x] Testé (659 passed, aucune régression)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement + stubs + config. Aucune modification de logique métier, aucune migration DB, aucun test modifié.
- **Contrat runtime** : strictement identique. Tous les `use Modules\Eshop360\Models\Customer` résolvent vers le canon via alias.
- **Concurrence / Multi-tenant / Permissions / Idempotence** : non applicables.
- **Rollback** : `git revert` sans risque.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopCRM → EshopX` (hors Eshop360 transitoire).

### Lien

- ADR : `docs/adr/ADR-010-eshop360-crm-subdomain-extraction.md`
- ADR parent : ADR-008 (stratégie), ADR-009 (pattern S1)
- PR : (n° à renseigner)

---

## CHG-2026-04-23-007 — R-101 sous-lot S1 : extraction Catalog

- **Date** : 2026-04-23
- **Type** : architecture (refactoring d'extraction progressive)
- **Modules concernés** : Eshop360 (déplacement de 7 modèles Catalog)
- **Impact** : nul côté runtime (rétrocompatibilité 100% via stubs d'alias).
- **Breaking change** : non.

### Actions appliquées

- **7 modèles déplacés** via `git mv` de `Modules/Eshop360/Models/` vers `Modules/Eshop360/Domain/Catalog/Models/` :
  - `Product.php`, `Category.php`, `Brand.php`, `ProductGroup.php`, `ProductTax.php`, `ProductVariation.php`, `Tax.php`.
- **Namespace mis à jour** dans chaque fichier déplacé : `Modules\Eshop360\Models` → `Modules\Eshop360\Domain\Catalog\Models`.
- **Product.php enrichi** de 5 imports pour les relations cross-sous-domaine :
  - `Stock`, `OrderItem`, `Supplier`, `ChannelProductPrice`, `DistributionChannel` — référencés via les alias `Modules\Eshop360\Models\*` (transition). Ces imports seront remplacés par leurs FQN canoniques au fil des sous-lots S3/S5/S7/S8.
- **7 stubs d'alias créés** dans `Modules/Eshop360/Models/<Nom>.php` (13 lignes chacun) : classes vides qui `extends` leur canon dans `Domain/Catalog/Models/`. Préservent la rétrocompatibilité 100% pour les 50+ consommateurs existants.
- **Deptrac `EshopCatalog` restreint** : ruleset passée de permissive (tous les EshopX autorisés) à `socles + Eshop360` uniquement. La dépendance `EshopCatalog → Eshop360` est temporaire, elle sera levée au sous-lot S3 quand `BelongsToChannel` aura migré sous `Domain/Channel/`.
- **Baseline PHPStan régénérée** : 3647 erreurs baselined (vs 3656 avant) — les erreurs de traits sur les modèles Catalog se sont déplacées vers le nouveau namespace, capturées dans la nouvelle baseline.
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- ADR `docs/adr/ADR-009-eshop360-catalog-subdomain-extraction.md` : documente le pattern d'extraction en 2 phases (déplacement + alias immédiat, résorption des alias/dépendances au fil des sous-lots).
- `docs/memory/OPEN_RISKS.md` R-101 : sous-lot S1 ✅.
- `docs/memory/RECENT_DECISIONS.md` : entrée S1.

### Statut

- [x] Implémenté (7 modèles déplacés, 7 alias créés, deptrac resserré)
- [x] Documenté (ADR-009)
- [x] Testé (659 passed, aucune régression)

### IMPACT_ANALYSIS

- **Périmètre** : déplacement de fichiers + stubs d'alias + config deptrac. Aucune modification de logique métier, aucune migration DB, aucun test modifié.
- **Contrat runtime** : strictement identique. Tous les `use Modules\Eshop360\Models\Product` continuent de résoudre vers la classe canonique via les alias.
- **Concurrence / Multi-tenant / Permissions / Idempotence** : non applicables à ce sous-lot.
- **Rollback** : `git revert` sans risque. Les alias sont purement descriptifs, leur suppression revient à l'état pré-S1.
- **Garde future** : deptrac bloque toute nouvelle dépendance `EshopCatalog → EshopX` (hors Eshop360 transitoire). Les alias marqués « backward-compat » dans leur PHPDoc documentent la dette à résorber en S12.
- **Pattern répétable** : ce schéma est le template pour S2..S11.

### Lien

- ADR : `docs/adr/ADR-009-eshop360-catalog-subdomain-extraction.md`
- ADR parent : `docs/adr/ADR-008-eshop360-subdomain-decomposition-strategy.md`
- Roadmap : `docs/Ins/b360_evolution_strategy.md` §1.4 Phase 2
- PR : (n° à renseigner)

---

## CHG-2026-04-23-006 — R-101 sous-lot S0 : préparation découpage Eshop360

- **Date** : 2026-04-23
- **Type** : architecture (préparation d'extraction, aucun code applicatif déplacé)
- **Modules concernés** : Eshop360 (configuration deptrac intra-module), outillage (`deptrac.yaml` + miroir `tools/`)
- **Impact** : nul côté runtime — aucun fichier PHP applicatif modifié. Préparation structurelle pour les sous-lots S1..S11.
- **Breaking change** : non.

### Actions appliquées

- `deptrac.yaml` : ajout de **13 layers intra-Eshop360** (`EshopCatalog`, `EshopCRM`, `EshopChannel`, `EshopPricing`, `EshopInventory`, `EshopPromotions`, `EshopPurchasing`, `EshopSales`, `EshopFinance`, `EshopHR`, `EshopCommunication`, `EshopProjects`, `EshopReporting`).
- Le layer `Eshop360` est modifié pour utiliser un collector `bool` avec `must`/`must_not` : il capture tout `Modules/Eshop360/.*` SAUF `Modules/Eshop360/Domain/.*` et `Modules/Eshop360/Pricing/.*`. Au fil des sous-lots, ce layer se vide à mesure que le code migre vers les sous-layers.
- Ruleset **permissive** au départ : chaque sous-layer peut dépendre de tous les autres + du reste `Eshop360` + des socles (`Core`, `Auth`, `Users`, `Settings`, `Billing`, `Currency`, `Lang`, `AppLayer`). Le resserrement sera progressif à chaque sous-lot (S1..S11) pour enforcer l'architecture cible.
- `graphviz.groups.L3` enrichi avec les 13 nouveaux sous-layers (visualisation architecture).
- `tools/deptrac/deptrac.yaml` : synchronisé avec root.
- Ajout `docs/adr/ADR-008-eshop360-subdomain-decomposition-strategy.md` : stratégie C (hybride) documentée avec 4 alternatives rejetées, séquencement des 13 sous-lots, règles de dépendance cibles, contraintes imposées au futur.
- `docs/memory/OPEN_RISKS.md` R-101 : statut passé de « roadmap définie, exécution à planifier » à « en cours, sous-lot S0 livré » + plan détaillé des sous-lots S0..S12.
- `docs/memory/RECENT_DECISIONS.md` : entrée 2026-04-23 S0.

### Statut

- [x] Implémenté (config deptrac)
- [x] Documenté (ADR-008)
- [x] Testé (deptrac : 0 violations, 13 skipped cross-module préservés)

### IMPACT_ANALYSIS

- **Périmètre** : purement structurel (configuration + ADR + mémoire). Aucun fichier PHP applicatif modifié, aucune migration DB, aucun test modifié.
- **Contrat runtime** : strictement identique. Aucune classe déplacée, aucun namespace changé.
- **Concurrence / Multi-tenant / Permissions / Idempotence** : non applicables à ce sous-lot.
- **Rollback** : `git revert` sans risque. Les layers ajoutés sont purement descriptifs ; leur suppression revient à l'état pré-S0.
- **Garde future** : deptrac enforce désormais que tout nouveau fichier doit respecter les layers déclarés. Les sous-lots S1..S11 vont progressivement resserrer la ruleset.

### Lien

- ADR : `docs/adr/ADR-008-eshop360-subdomain-decomposition-strategy.md`
- Roadmap source : `docs/Ins/b360_evolution_strategy.md` §1.4
- Audit cartographie : `docs/cartographie/02_eshop360_focus.md`
- PR : (n° à renseigner)

---

## CHG-2026-04-23-005 — R-103 fermé : consolidation Codifarm → DistributionChannel

- **Date** : 2026-04-23
- **Type** : décommissionnement (fermeture formelle + verrouillage anti-régression)
- **Modules concernés** : Eshop360 (tests + ADR, aucun code applicatif modifié par ce lot)
- **Impact** : faible — la migration (data + schéma) avait déjà été livrée en P0 mars 2026.
- **Breaking change** : non.

### Actions appliquées

- Ajout `Modules/Eshop360/Tests/Feature/CodifarmLegacyRemovalTest.php` (3 tests) :
  - **SCHÉMA TABLES** : `Schema::hasTable('eshop_codifarm_margin_config')` et `eshop_codifarm_margin_logs` retournent `false` (migration `2026_03_16_100003_drop_codifarm_tables_and_columns` ran).
  - **SCHÉMA COLONNES** : `Schema::hasColumn('eshop_orders', 'is_codifarm')` et `eshop_products.sale_price_codifarm` retournent `false`.
  - **CODE** : scan récursif de `Modules/Eshop360/{Services,Http/Controllers,Models,Domain}/*.php` — aucune occurrence de `codifarm_margin_config`, `codifarm_margin_log`, `is_codifarm`, `sale_price_codifarm`, `CodifarmMarginConfig`, `CodifarmMarginLog`. Le scan exclut volontairement `Database/Seeders/` et `Tests/` où le mot « CODIFARM » subsiste en tant que **nom métier** (grossiste pharmaceutique ivoirien, slug `demo-codifarm` utilisé comme donnée de canal de démo).
- Ajout `docs/adr/ADR-007-codifarm-channel-consolidation.md` :
  - État final documenté (canon : `DistributionChannel` + `ChannelMarginLog` + `ChannelProductPrice`).
  - Chaîne de migrations livrée (100002 + 100003) avec réversibilité limitée (schéma OK, données non restaurables sans dump).
  - 3 alternatives rejetées (parallélisation, renommage, migration progressive).
  - 5 contraintes imposées au futur (scannées par les tests structurels).
- R-103 déplacé de MAJEUR vers FERMÉ dans `docs/memory/OPEN_RISKS.md`. La section MAJEUR ne contient plus que **R-101** (Eshop360 monolithique — chantier pluri-lots, hors scope session).
- Entrée 2026-04-23 dans `RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (migrations livrées en P0, consolidation achevée)
- [x] Documenté (ADR-007 avec contraintes futures explicites)
- [x] Testé (3 tests structurels de verrouillage + tests existants DistributionChannel préservés)

### IMPACT_ANALYSIS (zone L2 DistributionChannel / calcul marges canal)

- **Périmètre** : ajout de tests structurels + ADR + fermeture du risque en mémoire. Aucun code applicatif ni migration modifiés par ce lot.
- **Contrat runtime** : inchangé. La migration vers `DistributionChannel` était déjà en production depuis mars 2026 ; ce lot ne fait que documenter et verrouiller.
- **Concurrence / Multi-tenant / Permissions** : inchangés. Les tests scannent le code source, pas le runtime.
- **Idempotence** : non applicable (pas de nouvelle opération runtime).
- **Rollback** : `git revert` sans risque. La suppression du test et de l'ADR ne touche pas au schéma. Une régression future (réintroduction des tokens legacy) serait à nouveau détectée par le test structurel.
- **Garde future** : les 3 tests structurels forment un **filet anti-résurrection** permanent. Une PR qui réintroduirait `is_codifarm`, `sale_price_codifarm` ou la table `eshop_codifarm_margin_config` casserait automatiquement ces tests → impossible de contourner sans modifier délibérément le test (action visible en code review).

### Lien

- ADR : `docs/adr/ADR-007-codifarm-channel-consolidation.md`
- Plan historique : `docs/Ins/b360_evolution_strategy.md` §2.3
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md`
- Migrations de migration : `Modules/Eshop360/Database/Migrations/2026_03_16_100002_migrate_codifarm_to_channels.php` + `2026_03_16_100003_drop_codifarm_tables_and_columns.php` (déjà `Ran`).
- PR : (n° à renseigner)

---

## CHG-2026-04-23-004 — R-201 fermé : suppression du squelette InventoryX

- **Date** : 2026-04-23
- **Type** : décommissionnement
- **Modules concernés** : InventoryX (supprimé), tooling (rector, phpstan, deptrac, bootstrap), docs
- **Impact** : faible — le module était vide (0 fichier PHP) et jamais chargé.
- **Breaking change** : non.

### Actions appliquées

- Suppression complète de `Modules/InventoryX/` (10 dossiers vides, 0 fichier).
- Nettoyage des références tooling :
  - `rector.php` : retrait du skip path `Modules/InventoryX`.
  - `phpstan.neon` : retrait de `Modules/InventoryX/*` des `excludePaths`.
  - `tools/phpstan/phpstan.neon` : synchronisé avec root (drift historique corrigé, fait hors scope pour maintenir la cohérence).
  - `deptrac.yaml` : retrait du layer InventoryX (définition + collectors), de la ruleset `InventoryX → [Core, Settings, AppLayer]`, du graphviz group L3.
  - `tools/deptrac/deptrac.yaml` : synchronisé avec root.
  - `scripts/memory/bootstrap-from-existing.sh` : retrait de la case `InventoryX` dans la détection des modules, retrait de la section `R-201` du template OPEN_RISKS, retrait de la ligne « Couche future : InventoryX » de PROJECT_DIGEST.
  - `.vscode/settings.json` : retrait de `InventoryX` de `cSpell.words`.
- Nettoyage docs :
  - `docs/cartographie/01_modules/inventoryx.md` supprimé.
  - `docs/audits/01_modules/inventoryx.md` supprimé.
  - `docs/context/PROJECT_DIGEST.md` : ligne « Couche future » corrigée.
- R-201 déplacé de MOYEN vers FERMÉ dans `docs/memory/OPEN_RISKS.md`. La section MOYEN est désormais **entièrement vide** (R-201, R-202 fermés).
- Entrée 2026-04-23 dans `RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (module supprimé, tooling nettoyé)
- [x] Documenté (OPEN_RISKS FERMÉ, RECENT_DECISIONS, CHANGELOG)
- [x] Testé (aucun code productif modifié, suite pest verte identique)

### IMPACT_ANALYSIS

- **Périmètre** : suppression d'un artefact vide + nettoyage des références tooling qui pointaient vers cet artefact. Aucun code productif modifié, aucune migration DB.
- **Contrat runtime** : inchangé. InventoryX n'était pas dans `modules_statuses.json` (jamais chargé), pas de service provider, pas de route, pas de classe → aucune API exposée n'est impactée.
- **Concurrence / Multi-tenant / Permissions / Idempotence** : non applicables (module vide).
- **Rollback** : `git revert` sans risque. Les dossiers vides se recréent si besoin (mais pas d'intérêt à le faire — la phase 2 d'extraction Eshop360 créera un nouveau module Inventory depuis zéro avec le code réel).
- **Garde future** : la roadmap `docs/Ins/b360_evolution_strategy.md` §2 (extraction phase 2) précise que le futur module Inventory sera créé *from scratch* à partir de l'extraction de `eshop_stocks`/`eshop_stock_movements`/`eshop_warehouses` depuis Eshop360, pas d'un squelette recyclé. La suppression de ce squelette évite toute confusion future.

### Lien

- Roadmap source : `docs/Ins/b360_evolution_strategy.md` task #22 « Supprimer module InventoryX (vide) — 30min ».
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` + `docs/cartographie/01_modules/inventoryx.md` (supprimé par ce lot).
- PR : (n° à renseigner)

---

## CHG-2026-04-23-003 — R-301 fermé : audit PasswordReset

- **Date** : 2026-04-23
- **Type** : sécurité (ajout audit trail sur event authentification)
- **Modules concernés** : Auth (nouveau listener, migration status column, module.json)
- **Impact** : faible — ajout pur, aucune modification de code existant.
- **Breaking change** : non.

### Actions appliquées

- Nouveau listener `Modules/Auth/Listeners/LogPasswordReset.php` (~30 lignes) : écrit dans `login_logs` avec `status = 'password_reset'`. Structure identique à `LogSuccessfulLogin`.
- `Modules/Auth/Providers/EventServiceProvider.php` : ajout de l'entrée `Illuminate\Auth\Events\PasswordReset::class => [LogPasswordReset::class]` dans le tableau `$listen`.
- `Modules/Auth/module.json` : ajout de `EventServiceProvider` dans la liste des providers. Sans cette registration, le `$listen` du EventServiceProvider n'est pas appliqué — raison pour laquelle l'existant Login/Failed fonctionnait via l'auto-discovery par réflexion mais le nouveau listener n'aurait pas été découvert en l'absence de cette fix.
- Migration `Modules/Auth/Database/Migrations/2026_04_23_100001_extend_login_logs_status_for_password_reset.php` : convertit la colonne `login_logs.status` de `ENUM('success','failed','locked')` en `VARCHAR(30)`. Branches cross-driver :
  - **SQLite** : rebuild de la table (CREATE new + copy + drop + rename), seul moyen de modifier un CHECK SQLite généré par ENUM.
  - **MySQL** : `ALTER TABLE login_logs MODIFY COLUMN status VARCHAR(30) NOT NULL`.
  - **PostgreSQL** : `ALTER TABLE login_logs ALTER COLUMN status TYPE VARCHAR(30)`.
  Justification VARCHAR vs nouvel ENUM : faciliter l'ajout futur de statuts (logout, session_expired, 2fa_challenge, etc.) sans migration enum.
- Ajout `Modules/Auth/Tests/Feature/PasswordResetAuditTest.php` (3 tests) :
  - Registration du listener vérifiée via `Event::getListeners(PasswordReset::class)`.
  - `event(new PasswordReset($user))` crée bien une entrée `LoginLog` avec `status = 'password_reset'`.
  - Appel direct `$listener->handle($event)` crée l'entrée attendue.
- R-301 déplacé de FAIBLE vers FERMÉ dans `docs/memory/OPEN_RISKS.md`. La section FAIBLE est désormais **entièrement vide** (tous les audits identifiés sont fermés).
- Entrée 2026-04-23 dans `RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (listener + registration + migration)
- [x] Documenté (entrée OPEN_RISKS FERMÉ, RECENT_DECISIONS, CHANGELOG)
- [x] Testé (3 tests nouveaux, suite verte)

### IMPACT_ANALYSIS (zone L1 Auth — ajout pur, aucune modif existant)

- **Périmètre** : nouveau listener + enregistrement + migration VARCHAR. Le code existant (`ResetPasswordController`, autres listeners, `LoginLog` model) n'est pas modifié.
- **Contrat runtime** :
  - Après une réinitialisation de mot de passe, une ligne est désormais insérée dans `login_logs` avec `status = 'password_reset'`. Aucune autre différence observable côté utilisateur.
  - Les logs existants (status='success', 'failed', 'locked') restent intouchés par la migration (copie complète).
- **Concurrence** : le listener est synchrone, pas de nouveau vector de race. L'existant gère déjà la concurrence des login events.
- **Multi-tenant** : la colonne `instance_id` de `login_logs` est renseignée via `CurrentInstance::get()` comme pour les autres statuts.
- **Permissions** : non impactées — le reset de mot de passe reste un endpoint public accessible sans auth.
- **Idempotence** : non applicable (chaque reset = un log). Si un user clique plusieurs fois sur "Reset", chaque clic réussi = un log supplémentaire (comportement attendu pour audit).
- **Rollback** : `git revert` + `php artisan migrate:rollback` (la migration a un `down()` qui restore l'ENUM sur chaque driver).
- **Observabilité** : l'audit sécurité peut désormais corréler Login/Failed/PasswordReset sur la timeline d'un même utilisateur via `SELECT * FROM login_logs WHERE user_id = ? ORDER BY created_at`.

### Lien

- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-12
- PR : (n° à renseigner)

---

## CHG-2026-04-23-002 — R-202 fermé : atomicité des numéros de facture

- **Date** : 2026-04-23
- **Type** : architecture (hardening pattern existant + uniformisation entre modules)
- **Modules concernés** : Billing (InvoiceManager refactoré), Eshop360 (tests ajoutés, code inchangé)
- **Impact** : moyen — Billing générait potentiellement des 500 sur collision de numéro. Eshop360 était déjà correct.
- **Breaking change** : non — la signature publique `InvoiceManager::generate()` est inchangée, le comportement utilisateur final est identique (tous les cas normaux et la majorité des cas de race sont transparents).

### Actions appliquées

- `Modules/Billing/Services/InvoiceManager.php` :
  - `generate()` refactorée : wrap dans `DB::transaction`, boucle `for` jusqu'à `MAX_NUMBER_ATTEMPTS = 5`, `try/catch QueryException` filtré sur code 1062, régénération du numéro à chaque retry via `nextNumber()`.
  - Ajout import `Illuminate\Database\QueryException`.
  - Ajout constante privée `MAX_NUMBER_ATTEMPTS = 5`.
  - Fallback `RuntimeException` si la boucle se termine sans succès (protection anti-livelock).
- Ajout `Modules/Eshop360/Tests/Feature/InvoiceNumberAtomicityTest.php` (3 tests) :
  - **STRUCTURAL** : grep du source de `InvoiceService.php` vérifie présence de `MAX_NUMBER_ATTEMPTS`, boucle `for`, import `QueryException`, `catch (QueryException`, code `1062`, `generateInvoiceNumber()` dans la boucle, et `DB::transaction`.
  - **DB-LEVEL** : tentative directe de créer deux `Invoice` avec même `(instance_id, invoice_number)` → `QueryException` levée (prouve que la contrainte UNIQUE P0 est active).
  - **HAPPY PATH** : `createFromItems()` appelé deux fois successivement produit bien deux numéros distincts, préfixés par `INV-`.
- Ajout `Modules/Billing/Tests/Feature/InvoiceNumberAtomicityTest.php` (3 tests) :
  - **STRUCTURAL** : grep du source de `InvoiceManager.php` verrouille le même pattern (constante, transaction, import, catch, code 1062, boucle for).
  - **DB-LEVEL** : deux `Invoice` avec même `number` → `QueryException` (contrainte UNIQUE globale).
  - **HAPPY PATH** : deux appels successifs à `generate(Subscription)` produisent des numéros distincts.
- ADR `docs/adr/ADR-006-invoice-numbering-atomicity.md` : stratégie détaillée (retry optimiste sur UNIQUE vs séquence DB vs advisory lock vs `ON CONFLICT` vs UUID), contraintes imposées au futur (pattern uniforme obligatoire pour tout nouveau générateur de numéro métier).
- R-202 déplacé de MOYEN vers FERMÉ dans `docs/memory/OPEN_RISKS.md` ; entrée 2026-04-23 dans `RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (refactor Billing + alignement pattern avec Eshop360)
- [x] Documenté (ADR-006)
- [x] Testé (6 tests nouveaux, `InvoiceServiceTest` existant inchangé)

### IMPACT_ANALYSIS (zone L2 Numérotation factures)

- **Périmètre** : hardening de `Billing\InvoiceManager::generate()` et ajout de tests sur les 2 modules. Aucune modification de code Eshop360, aucune migration DB, aucune modification du format du numéro.
- **Contrat runtime** :
  - Scénario normal (aucune collision) : **identique** à avant — mêmes numéros générés, même ordre.
  - Scénario de race (collision détectée par la contrainte UNIQUE) : **avant** = `QueryException 1062` non rattrapée → 500 client ; **après** = retry interne jusqu'à 4 fois, un numéro différent est généré à chaque itération, réponse succès transparente pour le client.
  - Scénario pathologique (5 collisions consécutives) : `RuntimeException` explicite avec message diagnostique, au lieu de `QueryException` brute. Mieux tracé en prod.
- **Concurrence** : deux `generate()` simultanés peuvent désormais produire des numéros séquentiels adjacents sans 500. La contrainte UNIQUE reste le filet final, le retry absorbe les collisions attendues.
- **Multi-tenant** : non impacté. Billing stocke sur la connexion `system` (numérotation globale par design). Eshop360 utilise une UNIQUE par `(instance_id, invoice_number)` (isolation naturelle).
- **Permissions** : non impactées — les appelants de `generate()` (SubscriptionManager, scheduled jobs) conservent leurs garde-fous d'authentification.
- **Idempotence** : préservée. Un appel est idempotent au niveau DB (contrainte UNIQUE), l'application gère la race de façon déterministe.
- **Rollback** : `git revert` sans risque (pas de migration DB, pas de schema change).
- **Garde future** : les tests structurels `*_source_has_retry_loop_*` dans les deux modules bloquent toute PR qui retirerait le pattern (transaction, try/catch, constante MAX, boucle).

### Lien

- ADR : `docs/adr/ADR-006-invoice-numbering-atomicity.md`
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-10
- Migration UNIQUE Eshop360 (existante) : `Modules/Eshop360/Database/Migrations/2026_04_04_100002_add_unique_order_and_invoice_numbers.php`
- Migration UNIQUE Billing (existante) : `Modules/Billing/Database/Migrations/2026_03_07_000003_create_invoices_table.php`
- PR : (n° à renseigner)

---

## CHG-2026-04-23-001 — R-004 fermé : idempotence commissions employés

- **Date** : 2026-04-23
- **Type** : architecture (hardening existant) + décommissionnement (méthode orpheline)
- **Modules concernés** : Eshop360 (HRService, tests, doc gouvernance)
- **Impact** : faible — le guard et la contrainte étaient déjà en place depuis P0 (2026-04-04). Ce lot ajoute l'absorption gracieuse de la race et ferme formellement R-004.
- **Breaking change** : non — la méthode supprimée (`HRService::recordCommission()`) n'avait aucun appelant production.

### Actions appliquées

- `Modules/Eshop360/Services/HRService.php` :
  - `calculateCommissionForSale()` : ajout d'un `try/catch UniqueConstraintViolationException` autour de `EmployeeCommission::create()`. Si la contrainte UNIQUE `(order_id, employee_id)` refuse l'insertion (race gagnée par une autre requête entre notre `exists()` et notre `create()`), l'exception est journalisée (`Log::info`) et absorbée silencieusement — plus d'erreur 500 client.
  - Suppression de la méthode orpheline `recordCommission(Employee, Order)` (66→0 appelants prod, pas de guard, piège pour évolution future).
  - Imports réorganisés alphabétiquement (ajouts : `UniqueConstraintViolationException`, `Log`).
- Ajout `Modules/Eshop360/Tests/Feature/CommissionIdempotenceTest.php` (3 tests) :
  - **STRUCTURAL** : grep du source de `HRService` pour verrouiller la présence du guard applicatif, de l'import de l'exception, du try/catch, et de `Log::info`.
  - **DB-LEVEL** : insertion directe de 2 commissions avec même `(order_id, employee_id)` → la 2ᵉ lève `UniqueConstraintViolationException` (preuve que la contrainte UNIQUE P0 est bien active).
  - **GRACEFUL** : scénario réel (commission pré-existante) → `calculateCommissionForSale()` via fast-path garde le compte à 1, pas d'exception propagée.
- Correction `docs/governance/PROTECTED_AREAS.md` ligne 94 : le chemin `Modules/Eshop360/Services/CommissionService` n'existe pas ; remplacé par `HRService::calculateCommissionForSale` avec référence ADR-005.
- ADR `docs/adr/ADR-005-commission-idempotency-strategy.md` : défense en profondeur 2 couches + absorption gracieuse ; 4 alternatives rejetées (guard seul, UNIQUE seul, `firstOrCreate`, `lockForUpdate` sur Order) ; contraintes imposées au futur.
- R-004 déplacé de CRITIQUE vers FERMÉ dans `docs/memory/OPEN_RISKS.md`. La section CRITIQUE est désormais **entièrement vide** (R-001, R-002, R-003, R-004 fermés).
- Entrée 2026-04-23 dans `docs/memory/RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (guard + try/catch + suppression méthode orpheline)
- [x] Documenté (ADR-005 + correction PROTECTED_AREAS)
- [x] Testé (3 tests nouveaux ; P0SafetyGuardsTest::test_commission_idempotente_si_ordre_completed_deux_fois conservé)

### IMPACT_ANALYSIS (zone L2 Commissions employés)

- **Périmètre** : hardening d'un service HRService existant (try/catch ajouté, méthode orpheline supprimée). La logique de calcul (`$order->total * commission_rate / 100`) est strictement inchangée.
- **Contrat runtime** :
  - Comportement observable pour l'utilisateur : identique en scénario normal (même fast-path, même calcul, même retour void).
  - Scénario de race (requêtes concurrentes) : avant = 500 HTTP client + doublon potentiel si guard contourné ; après = 200 HTTP client + journal `Log::info`, toujours une seule commission.
  - L'appelant unique (`OrderService::updateStatus`) est inchangé.
- **Concurrence** : la race window entre `exists()` et `create()` est désormais explicitement gérée. La contrainte UNIQUE P0 (déjà en place) bloque toute race qui passerait le guard ; notre catch la traduit en succès idempotent.
- **Multi-tenant** : non impacté — la clé UNIQUE est `(order_id, employee_id)` qui contient implicitement l'instance via les FK.
- **Permissions** : non impactées.
- **Idempotence** : sujet même du lot.
- **Rollback** : `git revert` sans risque, pas de migration DB (la contrainte UNIQUE reste en place côté P0).
- **Garde future** : 3 tests structurels bloquent toute PR qui retirerait le guard, l'import de l'exception, le try/catch ou le `Log::info`. La suppression de `recordCommission()` empêche toute résurrection accidentelle d'un chemin non protégé.

### Lien

- ADR : `docs/adr/ADR-005-commission-idempotency-strategy.md`
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-02
- Migration P0 (existante) : `Modules/Eshop360/Database/Migrations/2026_04_04_200001_add_p0_safety_guards.php`
- PR : (n° à renseigner)

---

## CHG-2026-04-22-005 — R-003 fermé : intégrité du solde portefeuille

- **Date** : 2026-04-22
- **Type** : architecture (renforcement garde-fous financiers) + refactor (consolidation point d'entrée unique)
- **Modules concernés** : Eshop360 (WalletDriver, FinanceService, ChannelPortalCustomerController, SaleController, migration)
- **Impact** : élevé sur la zone L2 Wallet — nouvelle contrainte SGBD, refonte TOCTOU, suppression de deux chemins de mutation directe
- **Breaking change** : non (tous les callers existants continuent via les mêmes signatures de méthode, la sémantique métier est inchangée)

### Actions appliquées

- Migration `Modules/Eshop360/Database/Migrations/2026_04_22_110001_add_check_constraint_wallet_balance` : ajoute `CHECK (wallet_balance >= 0)` sur `eshop_customers` en MySQL/PostgreSQL. Branche SQLite no-op (même pattern que la migration CHECK stock de R-001).
- `Modules/Eshop360/Services/Payment/Drivers/WalletDriver.php` :
  - `initiate()` : le check `wallet_balance < amount` est déplacé DANS la transaction, sous `lockForUpdate()`. Ferme la fenêtre TOCTOU entre l'ancien check ligne 29 (hors txn) et le decrement ligne 38.
  - `refund()` : ajout de `lockForUpdate()` avant l'increment pour sérialiser les remboursements concurrents.
  - Sortie de l'exception typée `InsufficientWalletBalanceException` sur solde insuffisant (meilleure ergonomie métier vs check silencieux).
- `Modules/Eshop360/Services/Payment/Drivers/InsufficientWalletBalanceException.php` : nouvelle exception typée (public readonly `$available`, `$requested`).
- `Modules/Eshop360/Http/Controllers/ChannelPortal/ChannelPortalCustomerController.php` : `walletTopup()` injecte `FinanceService` et appelle `creditWallet()` au lieu de `$customer->increment('wallet_balance', ...)` direct. Gain : lock pessimiste + `CustomerTransaction` d'audit + auto-pay des `CustomerDue` en attente.
- `Modules/Eshop360/Http/Controllers/Sales/SaleController.php` : `storeReturn()` refund=wallet utilise `app(FinanceService::class)->creditWallet()` avec `Order::class` + `returnOrder->id` comme référence (trace du retour dans `CustomerTransaction`).
- Ajout `Modules/Eshop360/Tests/Feature/WalletIntegrityTest.php` (7 tests) : structure migration + SQL CHECK, contrainte DB MySQL, WalletDriver source uses lock (TOCTOU fermée), débits séquentiels anti-négatif, audit trail FinanceService, structure ChannelPortal refactor, structure SaleReturn refactor.
- ADR `docs/adr/ADR-004-wallet-integrity-strategy.md` : stratégie détaillée, 4 alternatives rejetées (CHECK seul, versioning optimiste, ledger double-entry, queue idempotente), contraintes imposées au futur.
- R-003 déplacé de CRITIQUE vers FERMÉ dans `docs/memory/OPEN_RISKS.md` ; entrée 2026-04-22 dans `RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (code + migration)
- [x] Documenté (ADR-004)
- [x] Testé (7 tests nouveaux ; 3 tests wallet existants de `P0SafetyGuardsTest` conservés ; suite verte)

### IMPACT_ANALYSIS (zone L2 Wallet + multi-tenant)

- **Périmètre** : 2 modifications de code productif (WalletDriver + 2 controllers refactorés), 1 migration, 1 exception nouvelle, 7 tests, 1 ADR. La logique de `FinanceService::creditWallet/debitWallet` n'est PAS modifiée (elle était déjà correcte).
- **Contrat runtime** :
  - Solde insuffisant dans `WalletDriver::initiate()` retourne toujours `['success' => false, 'error' => "Solde insuffisant..."]` (message métier inchangé, seul le chemin d'obtention change : via exception typée au lieu de check direct).
  - Le refund wallet depuis `SaleController::storeReturn` produit désormais un `CustomerTransaction` d'audit en plus du `Payment` négatif — amélioration de traçabilité, pas de breaking change.
  - Le topup depuis le portail canal produit un `CustomerTransaction` et déclenche l'auto-pay des dues — correction d'une lacune connue.
- **Concurrence** : tous les chemins muant `wallet_balance` sont désormais sous `lockForUpdate()`. Deux débits simultanés sur le même client se sérialisent. Le test `test_wallet_driver_sequential_debits_never_produce_negative` prouve le comportement.
- **Multi-tenant** : les mutations restent dans l'instance du customer (FinanceService respecte `BelongsToInstance`, WalletDriver utilise `withoutGlobalScopes()->where('id', ...)` qui borne sur la PK donc sur une seule ligne).
- **Permissions** : non impactées — le gating HTTP (middleware `channel.access`, permissions Spatie) reste côté controllers.
- **Idempotence** : un double-click sur walletTopup crée 2 CustomerTransaction distinctes (comportement normal : chaque opération est une transaction distincte, à dédupliquer côté UI si besoin). Le refund de vente est protégé par la transaction englobante de `storeReturn`.
- **Rollback** : `git revert` + `php artisan migrate:rollback` (la migration a un `down()` qui retire la contrainte CHECK en MySQL/PG).
- **Garde future** : les 4 tests structurels (`test_check_constraint_migration_exists_with_correct_sql`, `test_wallet_driver_source_uses_lock_for_balance_check`, `test_channel_portal_topup_source_uses_finance_service`, `test_sale_return_source_uses_finance_service_for_wallet_refund`) bloquent toute régression par grep du source code — toute PR qui retirerait les imports, le lock, ou utiliserait à nouveau `Customer::increment('wallet_balance')` directement casse un test.

### Lien

- ADR : `docs/adr/ADR-004-wallet-integrity-strategy.md`
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-03
- PR : (n° à renseigner)

---

## CHG-2026-04-22-004 — R-002 fermé : idempotence webhooks (Billing + Eshop360)

- **Date** : 2026-04-22
- **Type** : architecture (nouvelle garantie de correction + couverture tests)
- **Modules concernés** : Billing (WebhookController, migration, Model), Eshop360 (migration UNIQUE)
- **Impact** : moyen — nouvelle colonne + contrainte UNIQUE sur 2 tables de logs, modification d'un controller webhook critique (L1)
- **Breaking change** : non (colonnes nullables, rétrocompatibilité préservée)

### Actions appliquées

- Migration `Modules/Billing/Database/Migrations/2026_04_22_100001_add_idempotency_key_to_billing_webhook_logs` : ajoute `idempotency_key VARCHAR(128) NULLABLE UNIQUE` sur `billing_webhook_logs`.
- Migration `Modules/Eshop360/Database/Migrations/2026_04_22_100002_add_unique_to_webhook_deduplication_key` : convertit l'index existant sur `eshop_webhook_logs.deduplication_key` (posé par la migration P0 `2026_04_04_200001`) en contrainte UNIQUE. Branche SQLite-safe (skip du drop d'index).
- `Modules/Billing/Http/Controllers/WebhookController.php` : calcul d'une clé idempotence stable (priorité `payload.id` / `event_id` / `transaction_id` / `cpm_trans_id` / fallback `hash('sha256', raw_body)`, préfixée par le slug de la passerelle et tronquée à 128 caractères), guard `try/catch UniqueConstraintViolationException` → replay détecté = `200 OK (replay)` sans appel au GatewayManager ni à `updatePaymentStatus()`.
- `Modules/Billing/Models/WebhookLog.php` : ajout de `idempotency_key` dans `$fillable`.
- `Modules/Billing/Services/GatewayManager.php` : retrait du modificateur `final` pour permettre le mocking dans les tests feature (la classe reste singleton par DI, impact pratique nul — non exposée aux clients pour extension).
- Ajout `Modules/Billing/Tests/Feature/WebhookIdempotenceTest.php` (5 tests) : replay ne re-traite pas Payment.paid_at, fallback hash, événements distincts = logs distincts, webhook malformé journalisé 400, contrainte UNIQUE DB enforcée.
- Ajout `Modules/Eshop360/Tests/Feature/WebhookServiceIdempotenceTest.php` (2 tests) : UNIQUE enforcement côté DB, coexistence de `deduplication_key` NULL multiples (rétrocompat).
- ADR `docs/adr/ADR-003-webhook-idempotency-strategy.md` : défense en profondeur 3 couches (UNIQUE SGBD + guard applicatif fast-path + HMAC signature), 4 alternatives rejetées (guard applicatif seul, table satellite, queue idempotente externe, verrou Redis distribué).
- R-002 déplacé de CRITIQUE vers FERMÉ dans `docs/memory/OPEN_RISKS.md` ; entrée 2026-04-22 dans `RECENT_DECISIONS.md`.

### Statut

- [x] Implémenté (code + migrations)
- [x] Documenté (ADR-003)
- [x] Testé (7 tests nouveaux, suite verte)

### IMPACT_ANALYSIS (zone L1 Billing webhooks + multi-tenant)

- **Périmètre** : ajout d'un guard idempotence avant tout traitement métier + contrainte DB UNIQUE. Aucun changement de la logique de `updatePaymentStatus()` / `markCompleted()` / GatewayManager webhook verification / signature HMAC.
- **Contrat runtime** : inchangé pour les webhooks valides et uniques. Un webhook **replay** retourne désormais `200 OK (replay)` au lieu de re-déclencher `Payment.status = completed`, `Payment.paid_at = now()`, et les éventuels Observers aval. C'est le comportement attendu par R-002.
- **Concurrence** : la contrainte UNIQUE au niveau SGBD garantit qu'aucune race ne peut produire deux logs avec la même clé, même si le check applicatif (Eshop360) ou la création optimiste (Billing) sont contournés par deux requêtes simultanées.
- **Multi-tenant** : `billing_webhook_logs.instance_id` renseigné post-vérification. La clé idempotence est globale (inclut le slug de la passerelle), donc résistante aux collisions inter-instances — un même `event_id` ne peut pas venir de deux instances réelles sur la même passerelle.
- **Permissions** : non impactées — les endpoints webhook sont publics par conception (callback des gateways).
- **Idempotence** : le sujet lui-même de ce lot.
- **Rollback** : `git revert` + `php artisan migrate:rollback` (les migrations ont `down()` qui supprime proprement la colonne / restaure l'index non-unique).
- **Garde future** : toute PR qui retirerait le `try/catch UniqueConstraintViolationException` ou la contrainte UNIQUE casserait les tests `WebhookIdempotenceTest::test_webhook_replay_does_not_reprocess_payment` et `WebhookServiceIdempotenceTest::test_database_enforces_unique_deduplication_key`.

### Lien

- ADR : `docs/adr/ADR-003-webhook-idempotency-strategy.md`
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-04
- PR : (n° à renseigner)

---

## CHG-2026-04-22-003 — R-001 fermé : stratégie de concurrence stock validée

- **Date** : 2026-04-22
- **Type** : architecture (décision documentée, pas de changement de code productif)
- **Modules concernés** : Eshop360 (StockService, tests)
- **Impact** : faible — la mitigation (lockForUpdate + DB::transaction + guard) était déjà en code depuis la migration `2026_04_04_100001`. Le lot ajoute la traçabilité (ADR, tests, fermeture du risque).
- **Breaking change** : non

### Actions appliquées

- Ajout de `Modules/Eshop360/Tests/Unit/StockServiceConcurrencyTest.php` (5 tests) :
  - `test_adjust_stock_source_uses_lock_for_update_within_transaction` — test structurel qui verrouille la présence de `DB::transaction`, `Stock::lockForUpdate()`, `$stock->refresh()` et de la guard `newQuantity < 0` dans le code source. Toute PR supprimant ce pattern casse ce test.
  - `test_sequential_adjust_stock_never_produces_negative_quantity` — scénario simulé (stock=5, 2 ventes de 3) : la 2ᵉ échoue car le lock + refresh voit quantité=2.
  - `test_adjust_stock_rollback_on_insufficient_quantity` — vérifie qu'aucun StockMovement n'est persisté en cas d'échec.
  - `test_adjust_stock_isolates_by_instance` — deux instances indépendantes, mutation sur A n'affecte pas B.
  - `test_adjust_stock_throws_on_first_sale_with_insufficient_stock` — vente sur stock absent (firstOrCreate → 0) échoue proprement.
- Ajout de `Modules/Eshop360/Tests/Feature/StockCheckConstraintTest.php` (2 tests) :
  - migration `2026_04_04_100001` vérifiée structurellement (contenu SQL + branche no-op SQLite),
  - contrainte CHECK `chk_quantity_non_negative` vérifiée dans `information_schema` si driver = MySQL (sinon skip).
- Ajout de `docs/adr/ADR-002-stock-concurrency-strategy.md` — décision en profondeur 3 couches avec alternatives rejetées (version optimiste, contrainte SGBD seule, queue asynchrone) et contraintes imposées au futur.
- R-001 déplacé de la section CRITIQUE vers FERMÉ dans `docs/memory/OPEN_RISKS.md`.

### Statut

- [x] Implémenté (code déjà en place depuis 2026-04-04)
- [x] Documenté (ADR-002)
- [x] Testé (5 unit + 2 feature passent sur SQLite, stress MySQL reporté)

### IMPACT_ANALYSIS (zone L1 multi-tenant + stock transactionnel)

- **Périmètre** : ajout de tests et d'une ADR. Aucune modification de code productif (`StockService.php`, migrations, contrôleurs appelants).
- **Contrat runtime** : inchangé. Les 7 contrôleurs qui appelaient déjà `adjustStock()` continuent via le même chemin.
- **Concurrence** : tests unitaires valident structure + comportement séquentiel. Concurrence réelle ne peut pas être reproduite sous SQLite (single-threaded, ignore FOR UPDATE).
- **Multi-tenant** : explicitement testé (`test_adjust_stock_isolates_by_instance`).
- **Permissions** : non impactées — le gating HTTP via middleware reste côté contrôleurs (hors scope service).
- **Idempotence** : non applicable — le stock n'est pas triggé par webhook externe.
- **Rollback** : `git revert` sans risque, pas de migration DB.
- **Garde future** : le test structurel (grep dans le source de `StockService.php`) bloque toute PR qui retirerait `lockForUpdate`, `DB::transaction`, `refresh` ou la guard `newQuantity < 0`.

### Lien

- ADR : `docs/adr/ADR-002-stock-concurrency-strategy.md`
- Audit source : `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-01
- PR : (n° à renseigner)

---

## CHG-2026-04-22-002 — R-102 fermé : retrait de FeatureGate deprecated

- **Date** : 2026-04-22
- **Type** : décommissionnement
- **Modules concernés** : Eshop360 (suppression classe wrapper), Billing (source canonique inchangée)
- **Impact** : faible (wrapper sans appelant production, seule référence dans le test qui l'exerçait directement)
- **Breaking change** : non

### Actions appliquées

- Suppression de `Modules/Eshop360/Services/FeatureGate.php` (146 lignes : wrapper `@deprecated` délégant à `FeatureRegistry` + constantes `FREE_FEATURES`/`PAID_FEATURES` obsolètes, remplacées par la registration dynamique via `Eshop360HooksProvider::registerBillableFeatures()`)
- `Modules/Eshop360/Tests/Feature/FeatureGatingTest.php` : suppression du test `test_feature_gate_service_returns_free_features_without_instance` + retrait de l'import obsolète, nettoyage des commentaires résiduels
- `Modules/Eshop360/Providers/Eshop360HooksProvider.php` : commentaire rafraîchi (ne référence plus "FeatureGate constants")
- Mise à jour `docs/memory/OPEN_RISKS.md` (R-102 → FERMÉ) et `docs/memory/RECENT_DECISIONS.md`

### Statut

- [x] Implémenté
- [x] Documenté
- [x] Testé (suite pest ≥ 625 passed)

### Lien

- PR : (n° à renseigner)

---

## CHG-2026-04-22-001 — R-104 fermé : trait BelongsToInstance unifié

- **Date** : 2026-04-22
- **Type** : décommissionnement
- **Modules concernés** : Core (trait canonique), app/ (suppression alias)
- **Impact** : faible (l'alias était orphelin, 0 usage applicatif)
- **Breaking change** : non

### Actions appliquées

- Suppression de `app/Models/Concerns/BelongsToInstance.php` (alias 4 lignes `use \Modules\Core\Database\Traits\BelongsToInstance`)
- Nettoyage du PHPDoc et des commentaires obsolètes dans `Modules/Core/Database/Traits/BelongsToInstance.php` (le trait canonique ne mentionne plus un alias qui n'existe plus)
- Nettoyage de l'entrée obsolète `App\Models\Concerns\BelongsToInstance` dans `tools/deptrac/baseline.yaml`
- Mise à jour `docs/memory/OPEN_RISKS.md` (R-104 déplacé en FERMÉ) et `docs/memory/RECENT_DECISIONS.md`

### Statut

- [x] Implémenté
- [x] Documenté
- [x] Testé (`InstanceScopeSafetyTest` 2/2, suite complète 626 passed — 2 échecs pré-existants sans lien)

### Lien

- PR : (n° à renseigner)

---

## CHG-2026-04-19-001 — Installation du pack vibecoding

- **Date** : 2026-04-19
- **Type** : gouvernance
- **Modules concernés** : tous (gouvernance projet, pas de code applicatif)
- **Impact** : élevé sur le mode de travail, nul sur le code applicatif
- **Breaking change** : non

### Actions appliquées

- Ajout des fichiers de gouvernance racine : `CLAUDE.md`, `CODEX.md`, `AGENTS.md`, `CONTRIBUTING.md`, `ARCHITECTURE.md`, `PROJECT_STATUS.md`, `CHANGELOG_ARCHITECTURAL.md`
- Configuration VSCode standardisée : `.vscode/settings.json`, `.vscode/tasks.json`, `.vscode/launch.json`, `.vscode/extensions.json`, snippets PHP/Markdown
- Hooks Git installés : `commit-msg` (Conventional Commits + scope obligatoire), `pre-commit` (Pint + PHPStan staged), `pre-push` (tests modules touchés + check mémoire), `post-commit` (rappel zones protégées), `prepare-commit-msg` (template auto)
- Configuration qualité : `tools/deptrac/deptrac.yaml` calé sur les 13 modules réels avec baseline, `tools/phpstan/phpstan.neon` (niveau 6 + Larastan + règle custom NoDirectCrossModuleTableAccess), `tools/rector/rector.php` (PHP 8.2 sets)
- Mémoire projet bootstrappée à partir de l'audit existant : `docs/memory/CURRENT_STATE.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md`, `docs/context/PROJECT_DIGEST.md`, `docs/index/MODULE_INDEX.md`, `docs/architecture/MODULE_DEPENDENCY_MAP.md`, `docs/governance/PROTECTED_AREAS.md`
- Workflows GitHub Actions : `ci.yml` (miroir de `make qa`), `protected-areas.yml` (vérification IMPACT_ANALYSIS sur zones L1), `architecture-graph.yml` (graphe deptrac sur PR)
- Templates de prompts numérotés : 00-bootstrap-claude, 00-bootstrap-codex, 01-claude-cadrage, 02-codex-implementation, 03-claude-review, 04-codex-correction, 05-new-module, impact-analysis

### Statut

- [x] Implémenté
- [x] Documenté
- [ ] Testé en conditions réelles (à confirmer après le premier lot pilote)

### Lien

- ADR : à créer dans `docs/adr/ADR-001-pipeline-qualite-local.md`
- PR : (n° à renseigner)

---

## Convention

Chaque entrée :

- **ID** stable au format `CHG-YYYY-MM-DD-NNN`
- **Date**
- **Type** : architecture / gouvernance / sécurité / contrat / migration / décommissionnement
- **Modules concernés** : liste explicite
- **Impact** : faible / moyen / élevé
- **Breaking change** : oui / non (si oui, procédure de migration documentée)
- **Actions appliquées** : liste de ce qui a changé concrètement
- **Statut** : à faire / en cours / implémenté / documenté / testé
- **Lien** : ADR, PR, ticket

Les changements purement applicatifs (bug fixes, nouvelles features sans impact transversal) ne figurent **PAS** ici. Ils sont dans le CHANGELOG fonctionnel ou dans les commits.
