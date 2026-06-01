# Incohérences et optimisations structurelles

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](../DOCUMENTATION_INDEX.md) pour la taxonomy.

> **Date :** 2026-04-04
> **Source :** Analyse croisée code source, migrations, services, contrôleurs.

---

## 1. Duplication de fonctionnalités

### 1.1 ~~Double système de marge (Codifarm vs DistributionChannel)~~ ✅ RÉSOLU (vérifié 2026-04-06)

> **Mise à jour 2026-04-06** — Cette section était **obsolète au moment de sa rédaction** : le système Codifarm a en réalité été supprimé en code applicatif depuis la migration `2026_03_16_100003_drop_codifarm_tables_and_columns.php` (rapatriement des données via `2026_03_16_100002_migrate_codifarm_to_channels.php`). Vérifié par audit du 2026-04-06 :
>
> - `Glob Modules/Eshop360/**/Codifarm*.php` → **0 résultat** (aucun modèle, service, controller)
> - Tables `eshop_codifarm_margin_*` droppées au runtime
> - Colonnes `is_codifarm` / `sale_price_codifarm` également droppées
> - **Système unifié** : `DistributionChannel` + `ChannelMarginLog` + `MarginService` (45 fichiers de production + tests)
> - Seules subsistent **les 7 migrations historiques** (création legacy + migration de données + drop) — **à conserver intactes** car nécessaires pour `migrate:fresh` sur les bases existantes
> - Quelques traces cosmétiques dans les seeders demo (`demo-codifarm` slug, label "CODIFARM" pour un canal de démo) — non bloquant
>
> **Aucune action de code requise.** Le seul écart restant est documentaire (cette section et celle dans `audits/03_incoherences_et_optimisations.md`).

### 1.2 Double système de feature flags

| Aspect | FeatureGate (Eshop360) | FeatureRegistry (Billing) |
|--------|----------------------|-------------------------|
| **Fichier** | `Modules/Eshop360/Services/FeatureGate.php` | `Modules/Billing/Services/FeatureRegistry.php` |
| **Constantes** | FREE_FEATURES, PAID_FEATURES (hardcodées) | Dynamique via HookRegistry |
| **Statut** | Deprecated (wrapper) | Actif (source de vérité) |

- **Impact :** **Moyen** — `FeatureGate` est marqué deprecated et délègue à `FeatureRegistry`, mais les constantes hardcodées persistent et pourraient induire en erreur.
- **Suggestion :** Supprimer `FeatureGate`, remplacer tous les appels par `FeatureRegistry` directement.

### 1.3 Double middleware feature

| Alias | Classe | Condition |
|-------|--------|-----------|
| `eshop.feature` | `Billing\EnsureFeature` (si Billing actif) | Prioritaire |
| `eshop.feature` | `Eshop360\EnsurePaidFeature` (fallback) | Si Billing absent |
| `billing.feature` | `Billing\EnsureFeature` | Toujours |

- **Localisation :** `Eshop360ServiceProvider.php:117`, `BillingServiceProvider.php:38`
- **Impact :** **Moyen** — Le fallback fonctionne mais ajoute de la complexité. Si les deux middleware ont des comportements différents, le résultat dépend de l'ordre de boot.
- **Suggestion :** Utiliser uniquement `billing.feature` partout, rendre Billing obligatoire.

### 1.4 Double audit log

| Table | Module | Usage |
|-------|--------|-------|
| `audit_logs` | Core | Audit système global |
| `eshop_audit_logs` | Eshop360 | Audit module Eshop360 |

- **Localisation :** `Modules/Core/Models/AuditLog.php`, `Modules/Eshop360/Models/AuditLog.php`
- **Impact :** **Moyen** — Même structure (action, model, model_id, old/new values). Les données sont dispersées dans deux tables.
- **Suggestion :** Unifier sur la table Core `audit_logs`, supprimer `eshop_audit_logs`.

### 1.5 Double système de factures

| Table | Module | Usage |
|-------|--------|-------|
| `invoices` (Billing) | Billing | Factures plateforme (SaaS) |
| `eshop_invoices` | Eshop360 | Factures commerciales (ventes) |

- **Impact :** **Faible** — Usage légitime différent (B2B plateforme vs B2C commerce). Pas de confusion si bien documenté.
- **Suggestion :** Aucune action nécessaire, mais renommer la table Billing en `billing_invoices` pour plus de clarté.

---

## 2. Duplication de tables ou données

### 2.1 Payments polymorphiques vs Billing payments

| Table | Module | Polymorphique |
|-------|--------|---------------|
| `eshop_payments` | Eshop360 | Oui (`payable_type`, `payable_id`) |
| `payments` | Billing | Non (FK `invoice_id`) |

- **Impact :** **Faible** — Contextes différents mais le concept "paiement" est implémenté deux fois avec des structures différentes.

### 2.2 Webhook logs dupliqués

| Table | Module |
|-------|--------|
| `billing_webhook_logs` | Billing |
| `eshop_webhook_logs` | Eshop360 |

- **Impact :** **Faible** — Contextes différents (webhooks plateforme vs webhooks commerce).

---

## 3. Fonctionnalités sous-exploitées

### 3.1 Code présent mais jamais appelé

| Composant | Fichier | Problème |
|-----------|---------|----------|
| `RecaptchaV3` rule | `Modules/Auth/Rules/RecaptchaV3.php` | Présente mais **non câblée** dans `LoginRequest` ni aucune route |
| `reserved_quantity` | `eshop_stocks.reserved_quantity` | Colonne existe, calculée dans `getAvailableQuantity()` mais **jamais incrémentée** au checkout |
| `CustomAuthController` | `app/Http/Controllers/CustomAuthController.php` | Controller d'auth alternatif avec registration — **legacy**, remplacé par le module Auth |
| `Personne` / `PersonnePhysique` / `PersonneMorale` | `app/Models/` | Modèles de personnes (physiques/morales) avec représentants — **non utilisés** par Eshop360 ni Auth. Probable vestige d'un autre module (CRM/treso). |
| `AdminDashboard` Livewire | `app/Livewire/AdminDashboard.php` | Composant Livewire isolé — probablement remplacé par le module Dashboard |
| `GeneratorService` | `app/Services/GeneratorService.php` | Génère des références pour PersonneRole — lié aux modèles Personne non utilisés |
| `SelectInventoryService` | `app/Services/SelectInventoryService.php` | Service d'inventaire des selects UI — utilitaire documentaire, pas fonctionnel |
| `InventoryX` module | `Modules/InventoryX/` | Squelette vide — aucune fonctionnalité |

- **Impact global :** **Moyen** — Code mort qui pollue le projet, risque de confusion pour les développeurs.
- **Suggestion :** Nettoyer `CustomAuthController`, les modèles `Personne*` (sauf si prévus pour un autre module), `AdminDashboard` Livewire, `InventoryX`.

### 3.2 Commandes artisan dupliquées

| Commande | Service Provider | Schedule |
|----------|-----------------|----------|
| `eshop360:recurring-invoices` | Eshop360ServiceProvider | Daily 06:00 |
| `eshop:generate-recurring-invoices` | Eshop360ServiceProvider | Daily 07:00 |

- **Impact :** **Moyen** — Deux commandes pour le même job, exécutées à 1h d'intervalle. Risque de double génération de factures récurrentes.
- **Suggestion :** Supprimer l'une des deux et garder un seul schedule.

---

## 4. Incohérences fonctionnelles

### 4.1 Nommage colonnes Order/Invoice

| Modèle | Colonne migration | Colonne attendue par les vues/exports |
|--------|------------------|---------------------------------------|
| Order | `order_number` | `reference` (certaines vues) |
| Invoice (Eshop) | `invoice_number` | `reference` (certaines vues) |

- **Localisation :** Migrations Eshop360, contrôleurs Invoice/Order
- **Impact :** **Haut** — Vues et exports qui référencent `reference` au lieu de `order_number`/`invoice_number` cassent.
- **Suggestion :** Auditer toutes les vues Blade et harmoniser sur `order_number`/`invoice_number` ou ajouter un accessor `getReference()`.

### 4.2 PurchaseReturn utilise le mauvais modèle

- **Problème :** Le `PurchaseReturnController` semble utiliser un pattern incohérent entre `PurchaseReturn` (retour) et `PurchaseOrder` (commande d'achat).
- **Localisation :** `Modules/Eshop360/Http/Controllers/Purchase/PurchaseReturnController.php`
- **Impact :** **Haut** — Le workflow de retour fournisseur peut être cassé.
- **Suggestion :** Vérifier et aligner le modèle utilisé avec le flux métier attendu.

### 4.3 tax_rate manquant sur invoice_items

- **Problème :** La migration `eshop_invoice_items` ne contient pas de colonne `tax_rate`. L'`InvoiceService` calcule la taxe à la volée mais ne peut pas la stocker par ligne.
- **Impact :** **Haut** — Impossible de recalculer correctement les taxes d'une facture historique si les taux changent.
- **Suggestion :** Ajouter `tax_rate` (decimal) à la migration `eshop_invoice_items`.

### 4.4 Variation prix = 0 non validé

- **Problème :** `OrderService` utilise `variation.price ?? product.price`. Si `variation.price = 0` (et non null), le prix sera 0.
- **Localisation :** `OrderService::createFromItems()` normalization loop
- **Impact :** **Moyen** — Variations gratuites non intentionnelles possibles.
- **Suggestion :** Utiliser `variation.price ?: product.price` (truthy check) ou valider prix > 0.

---

## 5. Mauvais découpage modulaire

### 5.1 Eshop360 : monolithe dans le modulaire

| Métrique | Valeur | Seuil raisonnable |
|----------|--------|-------------------|
| Modèles | 83 | < 20 par module |
| Contrôleurs | 78 | < 15 par module |
| Migrations | 128 | < 30 par module |
| Services | 45+ | < 10 par module |
| Tables | 70+ | < 20 par module |

**Domaines identifiés pour découpage :**

| Sous-module proposé | Modèles | Justification |
|--------------------|---------|---------------|
| **Catalog** | Product, Category, Brand, ProductVariation, ProductGroup, ProductTax, Tax | Domaine autonome |
| **Inventory** | Stock, StockMovement, StockTransfer, Warehouse, Store | Logique indépendante |
| **Sales** | Order, OrderItem, Cart, CashRegister, Holding, SaleReturn | Cycle de vente |
| **Invoicing** | Invoice, InvoiceItem, Quotation, RecurringInvoice, FneInvoice | Facturation |
| **CRM** | Customer, CustomerGroup, CustomerTransaction, CustomerDue | Relation client |
| **Purchasing** | Supplier, PurchaseOrder, PurchaseItem, PurchaseReturn, ImportOrder, ImportCost | Achats |
| **Finance** | Account, AccountTransaction, Expense, Income, Loan, GiftCard, InstallmentPlan | Finance |
| **Channel** | DistributionChannel, ChannelProductPrice, ChannelMarginLog, ChannelUser | Réseau revendeur |
| **HR** | Employee, EmployeeSalary, EmployeeCommission, Attendance | Ressources humaines |
| **ProjectMgmt** | Project, Task, TaskComment, Event | Gestion de projets |
| **Communication** | Message, BulkMessageLog, EmailTemplate, SmsGateway, SmsLog, SupportTicket | Communication |

### 5.2 Code Eshop dans le Core (ou vice versa)

| Composant | Localisation | Problème |
|-----------|-------------|----------|
| `Personne*` modèles | `app/Models/` | Modèles métier dans le namespace global app, pas dans un module |
| `Instance` model | `app/Instances/` | Modèle central dans app, pas dans le module Instances |
| `BelongsToInstance` (app/) | `app/Models/Concerns/` | Trait dupliqué aussi dans Core |
| `BelongsToInstance` (Core) | `Modules/Core/Database/Traits/` | Duplication du trait app/ |

- **Suggestion :** Déplacer `Instance` dans le module Core (ou Instances), supprimer le trait dupliqué, déplacer les modèles `Personne*` dans un module CRM ou les supprimer.

---

## 6. Propositions d'optimisation

### 6.1 Court terme (quick wins)

| # | Action | Fichiers | Impact | Effort |
|---|--------|----------|--------|--------|
| 1 | **Fixer Project/Task** — Ajouter `BelongsToInstance` trait | `Modules/Eshop360/Models/Project.php`, `Task.php` | Critique → fonctionnel | 5 min |
| 2 | **Supprimer commande dupliquée** — Garder un seul recurring-invoices | `Eshop360ServiceProvider.php` | Moyen → évite double génération | 5 min |
| 3 | **Ajouter tax_rate à invoice_items** — Migration + update service | Nouvelle migration | Haut → intégrité fiscale | 30 min |
| 4 | **Unifier audit_logs** — Migrer eshop → core | Migration + update `AuditService` | Moyen → simplification | 1h |
| 5 | **Supprimer code mort** — `CustomAuthController`, `AdminDashboard`, `Personne*` | `app/` | Moyen → propreté | 30 min |
| 6 | **Fix variation prix 0** — `?:` au lieu de `??` | `OrderService.php` | Moyen → évite prix 0 | 5 min |

### 6.2 Moyen terme (stabilisation)

| # | Action | Impact | Effort |
|---|--------|--------|--------|
| 7 | **Ajouter `lockForUpdate()`** dans StockService | Critique → élimine race condition | 2h |
| 8 | **Remplacer TOCTOU** par UNIQUE constraint + retry sur order/invoice numbers | Haut → élimine doublons | 2h |
| ~~9~~ | ~~Migrer CodifarmMarginConfig → DistributionChannel~~ | ✅ **DÉJÀ FAIT** par migration `2026_03_16_100002→100003` (vérifié 2026-04-06) | 0 |
| 10 | **Supprimer FeatureGate** — remplacer par FeatureRegistry direct | Moyen → simplification | 2h |
| 11 | **Wrapper transactionnel** pour `CostCalculatorService::updateProductPricing()` | Moyen → atomicité | 30 min |
| 12 | **Pessimistic locking** sur `FinanceService::creditWallet()` | Haut → évite double-paiement | 1h |

### 6.3 Long terme (refactoring structurel)

| # | Action | Impact | Effort |
|---|--------|--------|--------|
| 13 | **Découper Eshop360** en sous-modules (Catalog, Inventory, Sales, Finance, CRM, Channel, HR, ProjectMgmt) | Critique → maintenabilité, testabilité, évolutivité | 2-4 semaines |
| 14 | **Extraire services génériques** (Cart, Payment, SMS, PDF, Export, Webhook) en packages partagés | Haut → réutilisabilité multi-modules | 1-2 semaines |
| 15 | **Centraliser le modèle Instance** dans le module Core | Moyen → cohérence architecturale | 1 jour |
| 16 | **Créer un module CRM** pour les modèles Personne (si besoin métier) | Moyen → découpage propre | 3-5 jours |
| 17 | **Ajouter des Observers** pour déclencher les effets de bord (au lieu de logique dans les controllers) | Haut → séparation préoccupations | 1-2 semaines |
| 18 | **Ajouter des Events Laravel** pour les flux critiques (order.created, payment.received, stock.adjusted) | Haut → découplage, extensibilité | 1 semaine |

---

## 7. Matrice de priorisation

```
Impact ↑
CRITIQUE │  [1] Fix Project/Task    [7] Lock stock
         │  [3] tax_rate invoice     [8] TOCTOU numbers
         │  [9] Unify margins        [13] Découper Eshop360
    HAUT │  [12] Wallet locking      [14] Extract packages
         │  [6] Variation prix 0     [17] Observers
         │  [10] Kill FeatureGate    [18] Events
   MOYEN │  [2] Dedup commands       [4] Unify audit
         │  [5] Clean dead code      [11] Tx CostCalc
         │  [15] Centralize Instance [16] Module CRM
  FAIBLE │
         └──────────────────────────────────────────────►
           5min      1h       1jour    1sem    1mois  Effort
```
