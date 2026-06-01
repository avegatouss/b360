# ADR-017 — Extraction du sous-domaine Finance d'Eshop360 (R-101 S9, L1 critique)

> Architectural Decision Record. Neuvième sous-lot d'extraction du monolithe Eshop360 (R-101 S9).

## Statut

**Accepté** — 2026-04-24

## Contexte

Le sous-lot S9 extrait le sous-domaine Finance — **20 modèles** couvrant factures, paiements, comptes, dépenses, revenus, charges, installments, loans, FNE (Facture Normalisée Electronique Côte d'Ivoire). **L1 CRITIQUE** : ces modèles portent les invariants comptables et financiers (wallets, transactions, soldes), et plusieurs sont cibles polymorphiques (Invoice appears as `payable_type`, FneInvoice appears as `invoiceable_type`).

Retour d'expérience S7/S8 :

- S7 a révélé le piège covariance + morphClass, fix chirurgical dans ImportService.
- S8 a systématisé la mesure défensive `$morphClass` pinning sur les 9 canonicals.

Pour S9, vu le volume (20 modèles) et la criticité L1 + le grand nombre de relations polymorphiques potentielles (Payment.payable, FneInvoice.invoiceable, AccountTransaction.transactionable, etc.), on **applique le pinning `$morphClass` sur tous les 20 canonicals** par défaut.

## Décision

### Ce qui est extrait

20 modèles Finance déplacés vers `Modules/Eshop360/Domain/Finance/Models/` :

| Catégorie | Modèles |
|---|---|
| **Invoicing** | Invoice, InvoiceItem, RecurringInvoice |
| **Payments** | Payment, PaymentMethod, EshopPaymentGateway |
| **Accounts** | Account, AccountTransaction, AccountTransfer |
| **Expenses/Income** | Expense, ExpenseCategory, Income, IncomeSource |
| **Charges** | ChargeCategory, ChargeLog, CompanyCharge |
| **Installments/Loans** | InstallmentPayment, InstallmentPlan, LoanPayment |
| **Fiscal FNE** | FneInvoice |

### `$morphClass` pinning universel

Tous les 20 canonicals définissent :
```php
protected $morphClass = \Modules\Eshop360\Models\<Legacy>::class;
```

Bénéfices :

- Aucun audit de consumers requis (32+ sites passent potentiellement Invoice/Payment/etc. à des colonnes morph).
- Données production existantes (rows avec `_type = 'Modules\Eshop360\Models\Payment'`) parfaitement compatibles.
- Tests historiques (assertions `Invoice::class` via alias) passent sans modification.

Coût : +20 erreurs phpstan baselined (`missingType.property` sur chaque `$morphClass`).

### Imports cross-sous-domaine

Seuls 4 modèles ont des dépendances externes :

| Modèle | Imports externes |
|---|---|
| `Invoice` | Customer (CRM), Order (Sales) |
| `InvoiceItem` | Product (Catalog) |
| `InstallmentPlan` | Customer (CRM), Order (Sales) |
| `RecurringInvoice` | Customer (CRM) |

Les 16 autres (Account, AccountTransaction, Expense, Income, ChargeLog, Payment, FneInvoice, etc.) sont **feuilles** — ne dépendent que d'autres Finance models (peers) ou de socles (User via App\Models).

### Ruleset deptrac `EshopFinance`

Passée de permissive → :

- **Socles** : Core, Auth, Users, Settings, Billing, Currency, Lang.
- **`EshopCatalog`** : Product (InvoiceItem).
- **`EshopCRM`** : Customer (Invoice, InstallmentPlan, RecurringInvoice).
- **`EshopSales`** : Order (Invoice, InstallmentPlan).
- **`Eshop360` (transitoire)** : pour modèles non encore extraits référencés indirectement (Project, Loan, etc.).

### Impact sur EshopSales

La ruleset EshopSales S8 listait `EshopFinance` comme dépendance transitoire via Eshop360 (Invoice/Payment). Avec S9, on pourrait théoriquement lever cette transition — mais Order → `$this->hasOne(Invoice::class)` utilise l'alias `Modules\Eshop360\Models\Invoice`, donc cette dépendance reste via le layer Eshop360 pour le moment. Nettoyage en S12.

## Conséquences

### Positives

- **9ᵉ sous-domaine délimité** sur 13. Progression R-101 : **69 %**.
- **Plus gros sous-lot à ce jour** (20 modèles) géré en passe unique via PowerShell scripting.
- **Défense `$morphClass` systématique** : pattern désormais stable pour les sous-lots à morphs futurs.
- **0 régression** attendue : consumers passent par aliases, pinning garantit stabilité morph, 4 imports externes explicites.

### Négatives / coûts

- **+20 erreurs phpstan baselined** (missingType.property). Baseline : 3682 (vs 3656 S8). Bruit, non-fonctionnel.
- **Approche bulk PowerShell** : moins traçable ligne-par-ligne qu'Edit tool — mais cohérente (le même pattern appliqué à 20 fichiers, diff auditable via git).

## Implications opérationnelles

### Fichiers déplacés

- 20 fichiers → `Modules/Eshop360/Domain/Finance/Models/` via `git mv`.

### Stubs créés

- 20 dans `Modules/Eshop360/Models/` (template identique via PowerShell).

### Configuration

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : ruleset `EshopFinance` restreinte.
- `tools/phpstan/baseline.neon` : régénérée (3682).

### Validation

- `pest` : **659 passed / 2 failed (pré-existants) / 5 skipped** grâce au pinning universel.
- `phpstan` : OK.
- `deptrac` : 0 violations.

## Contraintes imposées au futur

1. **Tout nouveau modèle Finance** dans `Domain/Finance/Models/` + `$morphClass` pin.
2. **Pas de dépendance Finance → EshopChannel/EshopInventory/EshopPurchasing/EshopPromotions/EshopPricing/EshopHR/EshopCommunication/EshopProjects/EshopReporting** : Finance est une feuille côté morph (reçu par Payment.payable), pas un consommateur d'autres sous-domaines.
3. **Suppression programmée** :
   - Des 20 alias stubs au sous-lot S12.
   - Du pinning `$morphClass` → remplacement par un `Relation::enforceMorphMap` formel dans `Eshop360ServiceProvider` au S12.
4. **Bascule S12** : `Order::invoice()` devrait passer du FQN alias `\Modules\Eshop360\Models\Invoice::class` au canonique `\Modules\Eshop360\Domain\Finance\Models\Invoice::class` — levera la dépendance `EshopSales → Eshop360` transitoire.

## Références

- ADR-008 — Stratégie de découpage Eshop360.
- ADR-009..ADR-016 — sous-lots S1..S8.
- ADR-015 (S7) — découverte des pièges covariance + morphClass.
- ADR-016 (S8) — application défensive systématique de `$morphClass` pinning.
- Commit de clôture : branche `refactor/eshop360-s9-finance-extraction`.
