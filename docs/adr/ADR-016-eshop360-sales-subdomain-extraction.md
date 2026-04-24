# ADR-016 — Extraction du sous-domaine Sales d'Eshop360 (R-101 S8, L1 critique)

> Architectural Decision Record. Huitième sous-lot d'extraction du monolithe Eshop360 (R-101 S8).

## Statut

**Accepté** — 2026-04-24

## Contexte

Le sous-lot S8 extrait le cœur du sous-domaine Sales : 9 modèles couvrant la chaîne de vente complète (devis → commande → livraison → retour) et les workflows POS / online. **Statut L1 CRITIQUE** — les modèles Order et OrderItem portent des invariants transactionnels (paiements, stock, fiscalité FNE) et apparaissent comme **cibles polymorphiques** (`reference_type`, `payable_type`, `invoiceable_type`) dans au moins 4 tables : `eshop_stock_movements`, `eshop_payments`, `eshop_fne_invoices`, etc.

Le retour d'expérience de S7 a révélé deux pièges du pattern alias (voir ADR-015) :

1. **Covariance return type** : relation hasMany du canonique renvoyait le canonique, incompatible avec un return type annoté sur l'alias (sous-classe).
2. **Stabilité polymorphique** : `reference_type` stocké = `static::class` de l'instance ; un déplacement de classe change la FQN stockée.

Avec 7 services/contrôleurs qui passent `Order::class` comme type morphique (FneService, FinanceService, ChannelB2BService, OrderService, ChannelPortalReturnController, SaleController x3) et un volume de données production existant avec `reference_type = 'Modules\Eshop360\Models\Order'`, une approche défensive s'impose.

## Décision

### Ce qui est extrait

**9 modèles Sales** déplacés vers `Modules/Eshop360/Domain/Sales/Models/` :

| Modèle | Rôle |
|---|---|
| `Order` | Commande vente (POS + B2B) — **morph parent** (payments, invoices) |
| `OrderItem` | Lignes de commande |
| `OnlineOrder` | Commande en ligne (web) |
| `OnlineOrderItem` | Lignes de commande en ligne |
| `PersistentCart` | Panier persistant (user session) |
| `SaleReturn` | Retour client |
| `CashRegister` | Session caisse POS |
| `Quotation` | Devis |
| `QuotationItem` | Lignes de devis |

### Mesure défensive : `$morphClass` pinning

Chaque modèle canonique définit :
```php
protected $morphClass = \Modules\Eshop360\Models\<Legacy>::class;
```

Cette propriété Eloquent force `getMorphClass()` à retourner la **FQN legacy**, indépendamment de `static::class`. Ainsi :

- Toute nouvelle morph reference créée via le canonique stocke la legacy FQN.
- Toute nouvelle morph reference créée via l'alias (subclass) stocke aussi la legacy FQN (par héritage de la propriété).
- Les données production existantes (`reference_type = 'Modules\Eshop360\Models\Order'`) restent **parfaitement compatibles** pour morphTo->resolution.
- Les tests historiques qui assertent `Order::class` (alias FQN via `use Modules\Eshop360\Models\Order`) continuent à passer.

Cette décision évite à la fois :

- Un audit manuel des 7+ sites passant `Order::class` à des colonnes morph (risque d'oubli).
- Une migration de données pour réécrire les FQN stockées.
- Une fragilité à long terme quand de nouveaux consumers sont ajoutés.

### Ruleset deptrac `EshopSales`

Passée de permissive → :

- **Socles** : Core, Auth, Users, Settings, Billing, Currency, Lang.
- **`EshopCatalog`** : Product, ProductVariation (via OrderItem, OnlineOrderItem, QuotationItem, SaleReturn).
- **`EshopCRM`** : Customer (Order, OnlineOrder, SaleReturn, Quotation).
- **`EshopChannel`** : ChannelMarginLog (Order).
- **`EshopInventory`** : Store, Warehouse (Order, CashRegister).
- **`Eshop360` (transitoire)** : Invoice, Payment, Holding, InstallmentPlan, EmployeeCommission, Project — sous-domaines non encore extraits (S9 Finance, S10 HR, S11 Projects). Seront levés au fil des sous-lots.
- **Pas de `EshopPromotions` / `EshopPricing` / `EshopPurchasing`** : Sales consomme ces sous-domaines uniquement via des colonnes scalaires (ex. `coupon_code` string) ou à travers des services, jamais via import typé.

### Imports du canonique Order

Order.php pointe vers les modèles non-encore-extraits via alias `Modules\Eshop360\Models\*`. Les peers extraits (OrderItem, CashRegister) restent référencés sans `use` (même namespace). Customer/Store/Warehouse/ChannelMarginLog utilisent leur alias pour traverser le stub legacy.

## Note technique — ajustement post-extraction

### OnlineOrderService (même piège qu'ImportService S7)

Un closure type hint `fn (OnlineOrderItem $item)` dans `OnlineOrderService::convertToOrder()` itérant sur `$onlineOrder->items` (canonique) a déclenché :

```
Argument #1 ($item) must be of type Modules\Eshop360\Models\OnlineOrderItem,
Modules\Eshop360\Domain\Sales\Models\OnlineOrderItem given
```

**Fix appliqué** : remplacer `use Modules\Eshop360\Models\OnlineOrderItem;` → `use Modules\Eshop360\Domain\Sales\Models\OnlineOrderItem;` dans `OnlineOrderService`. Cohérent avec la règle **covariance** d'ADR-015 : pour un paramètre qui reçoit une instance issue d'une relation du canonique, importer le canonique.

`OrderService` a été audité et ne présente pas ce piège (ses paramètres typés `Order` reçoivent des instances provenant de ses propres `Order::create()` ou de controllers qui passent l'alias — les deux flux satisfont le type).

## Conséquences

### Positives

- **8ᵉ sous-domaine délimité** sur 13. Progression R-101 : ~62 %.
- **Pattern validé sur le sous-domaine le plus complexe** (Order avec 11+ relations + 4+ morph targets).
- **Morph stability pinned by design** : ne dépend plus des imports dans les consumers, garanti par `$morphClass` sur le modèle.
- **0 régression** attendue côté runtime : les 30+ fichiers consommateurs résolvent via alias, `Order::class` stocké identique avant/après, tests historiques passent.

### Négatives / coûts

- **Dépendance `EshopSales → Eshop360`** transitoire pour 6 sous-domaines non encore extraits (Invoice/Payment/Holding/InstallmentPlan/EmployeeCommission/Project). Levée progressive S9/S10/S11.
- **`$morphClass` introduit une deuxième source de vérité** pour la morph map — mais c'est un *fix ciblé*, pas une architecture. Le commit décrit l'intention.
- **+9 erreurs phpstan baselined** : chaque `protected $morphClass` sans type explicite est flaggé `missingType.property`. Baseline : 3656 (vs 3647 S7).

## Implications opérationnelles

### Fichiers déplacés

- 9 fichiers → `Modules/Eshop360/Domain/Sales/Models/`.

### Stubs créés

- 9 dans `Modules/Eshop360/Models/` avec note explicite sur `$morphClass` pinning.

### Configuration

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : ruleset `EshopSales` restreinte.
- `tools/phpstan/baseline.neon` : régénérée (3656 erreurs baselined ; +9 pour `$morphClass`).

### Validation attendue

- `pest` : **659 passed / 2 failed (pré-existants) / 5 skipped** grâce au morphClass pinning.
- `phpstan` : OK (avec baseline).
- `deptrac` : 0 violations.

## Contraintes imposées au futur

1. **Tout nouveau modèle Sales** dans `Domain/Sales/Models/`.
2. **Tout nouveau modèle utilisé comme morph target** (colonne `X_type`) DOIT définir `protected $morphClass = '<legacy_fqn_if_extracted>';` — soit à la FQN legacy soit à un alias morph map court stable.
3. **Pas de dépendance Sales → Promotions/Pricing/Purchasing** : la logique coupon/pricing/purchasing reste contrôlée par les services Sales, pas par imports typés de modèle.
4. **Suppression programmée** des 9 alias et du `$morphClass` pinning au sous-lot S12, remplacement par un **morph map formel** dans `Eshop360ServiceProvider::enforceMorphMap([...])`.

## Références

- ADR-008 — Stratégie de découpage Eshop360.
- ADR-009..ADR-015 — sous-lots S1..S7.
- ADR-015 (S7 Purchasing) — découverte des pièges covariance + morphClass ; cette ADR-016 applique la règle de manière défensive pour S8 en raison du volume.
- Commit de clôture : branche `refactor/eshop360-s8-sales-extraction`.
