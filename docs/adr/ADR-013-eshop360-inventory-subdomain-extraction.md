# ADR-013 — Extraction du sous-domaine Inventory d'Eshop360 (R-101 S5)

> Architectural Decision Record. Cinquième sous-lot d'extraction du monolithe Eshop360 (R-101 S5).

## Statut

**Accepté** — 2026-04-24

## Contexte

Le sous-lot S5 extrait les modèles du sous-domaine Inventory. **Statut L1 CRITIQUE** : ces modèles portent la logique de stock (R-001, race condition décrémentation stock concurrent), mais les contraintes de concurrence vivent dans les services (`StockService` + `lockForUpdate` + `DB::transaction` + UniqueConstraintViolationException retry) — pas dans les modèles Eloquent eux-mêmes. L'extraction de ces classes ne modifie donc pas les invariants runtime.

**État avant S5** : `Stock` avait déjà été déplacé sous `Modules/Eshop360/Domain/Inventory/Models/Stock.php` (hors R-101, probablement au cours d'un chantier antérieur), avec son alias rétrocompatible dans `Modules/Eshop360/Models/Stock.php`. Les 5 autres modèles Inventory (`StockMovement`, `StockTransfer`, `StockTransferItem`, `Warehouse`, `Store`) vivaient encore sous `Modules/Eshop360/Models/`.

**Analyse des consommateurs** : `grep` exhaustif `use Modules\Eshop360\Models\(Stock|StockMovement|StockTransfer|StockTransferItem|Warehouse|Store)` → 59 fichiers (Services, Controllers, Tests, Seeders, Commands). Tous continuent de fonctionner via les alias stubs après extraction.

## Décision

### Ce qui est extrait

**5 modèles Inventory** déplacés vers `Modules/Eshop360/Domain/Inventory/Models/` :

- `StockMovement` : journal des mouvements de stock (entrées, sorties, ajustements). +1 import cross-sous-domaine (Product via alias).
- `StockTransfer` : transfert de stock entre warehouses. Peers intra-domain (Warehouse, StockTransferItem) référencés sans `use` (même namespace).
- `StockTransferItem` : lignes d'un transfert. Peer intra-domain (StockTransfer) sans import ; +1 import Product (alias).
- `Warehouse` : entrepôt. +1 import cross-sous-domaine (Employee via HR alias — manager d'un entrepôt).
- `Store` : point de vente (rattaché à un warehouse). Peer intra-domain Warehouse sans import.

**Stock.php canonique** : nettoyage des imports — `Warehouse` et `Store` étaient importés via alias `Modules\Eshop360\Models\*`, désormais peers intra-namespace (suppression des lignes `use`).

### Ce qui reste en place

- **5 alias rétrocompatibles** dans `Modules/Eshop360/Models/` (`class X extends \Modules\Eshop360\Domain\Inventory\Models\X {}`) pour préserver les 59 consommateurs actuels.
- **`BelongsToChannel` + `ScopedByUserAssignment`** restent sous `Modules/Eshop360/Database/Traits/` (cf. ADR-011 — infrastructure partagée intra-Eshop360).
- **Services et Controllers Inventory** non déplacés — `StockService`, `StockController`, `WarehouseController`, `StockTransferController`, `StockAdjustmentController` restent dans `Modules/Eshop360/{Services,Http/Controllers/Inventory}/`. Scope S5 = modèles uniquement, conformément au pattern S1/S2/S3.

### Ruleset deptrac `EshopInventory`

Passée de l'ancre permissive `eshop_sublayer_base` (tous les EshopX) → restriction :

- **Socles autorisés** : Core, Auth, Users, Settings, Billing, Currency, Lang — standard.
- **`EshopCatalog`** : pour `Product` canonique (référencé par Stock, StockMovement, StockTransferItem).
- **`Eshop360`** (transitoire) : alias de `Employee` (Warehouse::manager → HR subdomain pas encore extrait) + BelongsToChannel/ScopedByUserAssignment infrastructure. Sera levé en S10 (HR) et S12 (clôture).
- **Pas d'EshopCRM, EshopChannel, EshopSales, EshopFinance** : Inventory ne dépend directement d'aucun autre sous-domaine métier.

### Garanties L1 préservées

1. **Table schémas inchangés** : aucune migration modifiée.
2. **Services inchangés** : `StockService` et son `lockForUpdate`/`DB::transaction` restent strictement identiques — aucun code de StockService n'est modifié par S5.
3. **Tests de concurrence** : `StockServiceConcurrencyTest`, `StockServiceFullTest`, `StockServiceCoreTest`, `StockServiceTest` continuent de passer — le runtime est strictement identique.
4. **Relation Eloquent `Stock::product`** : pointe toujours sur `Modules\Eshop360\Models\Product` (alias) → résolution transparente vers `Domain\Catalog\Models\Product`. Aucun changement de clé étrangère ni de logique de foreign key.

## Conséquences

### Positives

- **5ᵉ sous-domaine délimité** (Catalog S1, CRM S2, Channel S3, Pricing S4, Inventory S5) — progression R-101 : 5/13.
- **Pattern validé pour la 4ᵉ fois** (extraction physique) sur un sous-domaine L1 critique — renforce la confiance pour S8 (Sales L1) et S9 (Finance L1).
- **Ruleset deptrac stricte** : toute nouvelle dépendance `EshopInventory → EshopX` (hors Catalog/Eshop360) bloquée en CI.
- **0 régression** : 659 tests passent inchangés (vs S4), 0 violation deptrac, 0 erreur phpstan.
- **Nettoyage des imports Stock.php** : Warehouse et Store maintenant référencés en peers intra-namespace (plus propre).

### Négatives / coûts

- **Dépendance `EshopInventory → Eshop360` transitoire** : demeure pour alias Employee + infrastructure BelongsToChannel. Examinée en S10 / S12.
- **Imports via alias restant dans les modèles** : Product, Employee — cohérent avec le pattern S1/S2/S3 (basculement FQN canonique prévu en S12).

## Implications opérationnelles

### Fichiers déplacés

- `StockMovement.php`, `StockTransfer.php`, `StockTransferItem.php`, `Warehouse.php`, `Store.php` → `Modules/Eshop360/Domain/Inventory/Models/`.
- `Stock.php` canon nettoyé (imports peers supprimés).

### Stubs d'alias créés

- 5 dans `Modules/Eshop360/Models/` (StockMovement, StockTransfer, StockTransferItem, Warehouse, Store).

### Configuration

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : ruleset `EshopInventory` restreinte.
- `tools/phpstan/baseline.neon` : régénérée (3647 erreurs baselined — inchangé vs S3/S4, les erreurs « missingType.generics » se sont déplacées avec les classes).

### Validation

- `pest` : **659 passed / 2 failed (pré-existants EshopSettingsService) / 5 skipped** — identique à S4.
- `phpstan` : OK, 0 erreur.
- `deptrac` : 0 violations, 13 skipped cross-module.

## Contraintes imposées au futur

1. **Tout nouveau modèle Inventory** dans `Domain/Inventory/Models/`. Pas de création dans `Modules/Eshop360/Models/` hors stub.
2. **Pas de dépendance intra-Eshop360 depuis Inventory** vers les sous-domaines non-Catalog. Si un besoin émerge (ex. Inventory consomme une entité Sales ou Purchasing), **remonter la logique** plutôt qu'élargir la ruleset.
3. **Les services StockService, WarehouseService, etc.** restent sous `Modules/Eshop360/Services/` tant que leur déplacement n'est pas planifié explicitement (post-S5, potentiellement intégré à S12 ou différé).
4. **Les contrôleurs Inventory** (`Http/Controllers/Inventory/`) restent à leur emplacement — leur déplacement éventuel vers `Domain/Inventory/Http/Controllers/` sera décidé quand la maturité du pattern sera établie.
5. **Suppression programmée** des 5 alias au sous-lot S12.

## Références

- ADR-008 — Stratégie de découpage Eshop360.
- ADR-009 — Pattern d'extraction (S1 Catalog).
- ADR-010 — Extraction CRM (S2).
- ADR-011 — Extraction Channel (S3) + trait BelongsToChannel.
- ADR-012 — Normalisation Pricing (S4, ruleset-only).
- R-001 fermée — Protection race condition stock (StockService + lockForUpdate).
- Commit de clôture : branche `refactor/eshop360-s5-inventory-extraction`.
