# ADR-015 — Extraction du sous-domaine Purchasing d'Eshop360 (R-101 S7)

> Architectural Decision Record. Septième sous-lot d'extraction du monolithe Eshop360 (R-101 S7).

## Statut

**Accepté** — 2026-04-24

## Contexte

Le sous-lot S7 extrait les 9 modèles du sous-domaine Purchasing, qui couvre la gestion des fournisseurs, des commandes fournisseur locales (PurchaseOrder) et des commandes d'import international (ImportOrder) avec allocation de coûts landed.

Groupes fonctionnels dans Purchasing :

1. **Fournisseurs** : `Supplier` (+ relation `belongsToMany Store`).
2. **Commandes fournisseur** : `PurchaseOrder`, `PurchaseItem`, `PurchaseReturn`, `PurchaseReturnItem`.
3. **Commandes d'import** : `ImportOrder`, `ImportOrderItem`, `ImportCost`, `ImportCostType`.

Application du pattern S1/S2/S3/S5/S6 — déplacement + stubs d'alias + deptrac resserré.

## Décision

### Ce qui est extrait

**9 modèles Purchasing** déplacés vers `Modules/Eshop360/Domain/Purchasing/Models/` avec namespace `Modules\Eshop360\Domain\Purchasing\Models`.

Imports cross-sous-domaine ajoutés via alias :

| Modèle | Imports externes |
|---|---|
| `Supplier` | `Store` (EshopInventory) |
| `PurchaseOrder` | `Payment` (EshopFinance — pas encore extrait), `Warehouse` (EshopInventory) |
| `PurchaseItem` | `Product` (EshopCatalog) |
| `PurchaseReturn` | `Warehouse` (EshopInventory) |
| `PurchaseReturnItem` | `Product` (EshopCatalog) |
| `ImportOrder` | `Warehouse` (EshopInventory) |
| `ImportOrderItem` | `Product` (EshopCatalog) |
| `ImportCost` | aucun externe |
| `ImportCostType` | aucun externe |

### Ruleset deptrac `EshopPurchasing`

Passée de permissive → :

- **Socles** : Core, Auth, Users, Settings, Billing, Currency, Lang.
- **`EshopCatalog`** : pour Product (PurchaseItem, PurchaseReturnItem, ImportOrderItem).
- **`EshopInventory`** : pour Warehouse (PurchaseOrder, PurchaseReturn, ImportOrder) et Store (Supplier).
- **`Eshop360` (transitoire)** : BelongsToChannel infrastructure + alias Payment (Finance pas encore extrait — sera traité en S9).
- **Pas de EshopCRM** : Purchasing n'interagit pas directement avec Customer (les paiements fournisseur sont via Payment MorphMany, pas Customer).

## Note technique — piège révélé par S7 (et pattern appliqué dès maintenant)

Le test `ImportOrderTest::test_can_add_cost_to_import` a révélé un **piège spécifique au pattern alias stub** (`class Legacy extends \Canonical\X {}`) :

### Cas 1 : covariance des return types

Dans `ImportService::addCost(...): ImportCost`, l'appel `$order->costs()->create(...)` passe par la relation `hasMany(ImportCost::class)` déclarée dans le modèle **canonique** `Domain\Purchasing\Models\ImportOrder`. Cette relation instancie donc le modèle canonique. Si le return type est annoté avec la classe **alias** `Modules\Eshop360\Models\ImportCost` (qui est maintenant SOUS-classe du canonique), PHP lève `Return value must be of type Modules\Eshop360\Models\ImportCost, Modules\Eshop360\Domain\Purchasing\Models\ImportCost returned` — le parent ne satisfait pas un return type sous-classe.

**Fix appliqué** : `use Modules\Eshop360\Domain\Purchasing\Models\ImportCost;` dans `ImportService`. Le return type devient le canonique, PHP accepte.

### Cas 2 : polymorphisme (`$morphClass`)

Pour stocker une référence morphique, Eloquent utilise `$parent->getMorphClass()` — qui retourne `static::class` par défaut, c'est-à-dire la FQN de l'instance concrète. Si l'instance est de la classe alias (le service/contrôleur importait le legacy `use Modules\Eshop360\Models\ImportOrder`), le storage est `Modules\Eshop360\Models\ImportOrder` (legacy FQN). Si l'instance est canonique, c'est l'inverse.

**Fix appliqué** : dans `ImportService`, **garder** `use Modules\Eshop360\Models\ImportOrder` (alias) — c'est délibéré. Ainsi `ImportOrder::class` en ligne 144 résout vers le legacy FQN, et le `reference_type` stocké reste identique à ce qu'il était pré-S7. Données et tests historiques préservés.

### Règle pour les sous-lots futurs

Dans un Service ou Controller qui :
- **Utilise un modèle pour du polymorphisme (`reference_type`, morphMany)** → importer via **alias** legacy.
- **Renvoie un objet issu d'une relation `hasMany()`/`belongsTo()` créée sur le canonique** → importer via **canonique** et annoter le return type avec la canonique.

Tant que les deux contraintes ne se croisent pas sur le même modèle, pas de friction.

### Risque résiduel

Si un sous-lot futur déplace un modèle utilisé EN MÊME TEMPS comme (a) return type d'une relation hasMany depuis le canonique ET (b) morph target, il faudra prévoir un `protected $morphClass = \Modules\Eshop360\Models\<Legacy>::class;` dans la classe canonique pour figer la FQN stockée. Non nécessaire en S7.

## Conséquences

### Positives

- **7ᵉ sous-domaine délimité** sur 13. Progression R-101 : ~54 %.
- **Pattern validé pour la 6ᵉ extraction physique** (S1/S2/S3/S5/S6/S7).
- **Ruleset stricte** : toute dépendance croisée Purchasing → Sales/CRM/etc. sera bloquée.
- **0 régression** : 659 tests passent (inchangé vs S6), deptrac 0 violations, phpstan OK.
- **Piège alias-covariance + morphClass documenté** : règle écrite ci-dessus, applicable S8+.

### Négatives / coûts

- **Dépendance `EshopPurchasing → Eshop360`** transitoire pour alias Payment. Sera levée en S9 (Finance) quand Payment passera sous `Domain/Finance/`.
- **ImportOrder et ImportCost** sont le cœur du pricing landed (chemin réel = factory + freight + customs + …). La logique de répartition vit dans un Service (non modifié par S7).

## Implications opérationnelles

### Fichiers déplacés

- 9 fichiers (Supplier, PurchaseOrder, PurchaseItem, PurchaseReturn, PurchaseReturnItem, ImportOrder, ImportOrderItem, ImportCost, ImportCostType) → `Modules/Eshop360/Domain/Purchasing/Models/`.

### Stubs créés

- 9 dans `Modules/Eshop360/Models/`.

### Configuration

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : ruleset `EshopPurchasing` restreinte.
- `tools/phpstan/baseline.neon` : régénérée (3647 erreurs baselined — inchangé vs S6).

### Validation

- `pest` : 659 passed / 2 failed (pré-existants) / 5 skipped — inchangé.
- `phpstan` : OK.
- `deptrac` : 0 violations, 13 skipped cross-module.

## Contraintes imposées au futur

1. **Tout nouveau modèle Purchasing** dans `Domain/Purchasing/Models/`.
2. **Pas de dépendance vers Sales/CRM/Promotions depuis Purchasing** : si un besoin typé émerge (ex. lier une PurchaseOrder à une Customer request), remonter la logique plutôt qu'élargir la ruleset.
3. **Bascule `Payment` à planifier en S9** : actuellement importé via alias `Modules\Eshop360\Models\Payment`, basculera vers FQN canonique `Modules\Eshop360\Domain\Finance\Models\Payment` et la dépendance `EshopPurchasing → Eshop360` (transitoire) sera remplacée par `EshopPurchasing → EshopFinance`.
4. **Suppression programmée** des 9 alias au sous-lot S12.

## Références

- ADR-008 — Stratégie de découpage Eshop360.
- ADR-009..ADR-014 — sous-lots S1..S6.
- Commit de clôture : branche `refactor/eshop360-s7-purchasing-extraction`.
