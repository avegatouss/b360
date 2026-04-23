# ADR-009 — Extraction du sous-domaine Catalog d'Eshop360 (R-101 S1)

> Architectural Decision Record. Premier sous-lot d'extraction du monolithe Eshop360 (R-101 S1).

## Statut

**Accepté** — 2026-04-23

## Contexte

Le sous-lot S0 (ADR-008) a posé les layers deptrac intra-Eshop360 et la stratégie C (hybride). S1 est le **premier sous-lot d'extraction effective** : déplacement physique des modèles Catalog vers `Modules/Eshop360/Domain/Catalog/Models/` avec préservation de la compatibilité ascendante.

Le sous-domaine Catalog regroupe 7 modèles Eloquent et leur logique associée :

| Modèle | Table | Rôle |
|---|---|---|
| `Product` | `eshop_products` | Produit catalogué (master data) |
| `Category` | `eshop_categories` | Catégorie hiérarchique de produits |
| `Brand` | `eshop_brands` | Marque associée à un produit |
| `ProductGroup` | `eshop_product_groups` + pivot `eshop_product_group_items` | Regroupement transverse (ex. promo pack) |
| `ProductTax` | `eshop_product_taxes` | Pivot produit × taxe |
| `ProductVariation` | `eshop_product_variations` | Variante de produit (taille, couleur) |
| `Tax` | `eshop_taxes` | Référentiel taxes par instance |

Ces 7 modèles sont massivement référencés dans tout Eshop360 : `SaleController`, `OrderService`, `StockService`, `InvoiceService`, `ChannelPortalController`, `PricingRule*`, `Reports*`, etc. Les déplacer sans plan de compatibilité casserait immédiatement ~50+ fichiers consommateurs.

Par ailleurs, le modèle `Product` a lui-même des relations Eloquent vers d'autres sous-domaines non encore extraits :

- `Product::stocks()` → `Stock` (futur `EshopInventory`)
- `Product::orderItems()` → `OrderItem` (futur `EshopSales`)
- `Product::supplier()` → `Supplier` (futur `EshopPurchasing`)
- `Product::prices()` → `ChannelProductPrice` (futur `EshopChannel`)
- `Product::channels()` → `DistributionChannel` (futur `EshopChannel`)
- `Product::user()` → `User` (module Users)

De plus, les traits `BelongsToChannel` (dans `Modules/Eshop360/Database/Traits/`) sont utilisés par tous les modèles Catalog — ils ne seront extraits qu'au sous-lot S3 Channel.

Le défi de S1 : déplacer les fichiers physiquement, maintenir le code qui les consomme fonctionnel, sans bloquer les sous-lots suivants qui extrairont les autres sous-domaines.

## Décision

Nous adoptons un **pattern d'extraction en deux phases par sous-domaine** :

### Phase 1 (immédiate — ce lot)

1. **Déplacement physique** via `git mv` :
   - `Modules/Eshop360/Models/<Model>.php` → `Modules/Eshop360/Domain/Catalog/Models/<Model>.php`
2. **Mise à jour du namespace** dans chaque fichier déplacé :
   - `namespace Modules\Eshop360\Models;` → `namespace Modules\Eshop360\Domain\Catalog\Models;`
3. **Ajout d'imports explicites** dans les modèles qui référencent des classes hors du nouveau namespace (Product a besoin d'importer Stock, OrderItem, etc. qui restent dans `Modules\Eshop360\Models\`).
4. **Création de stubs d'alias** de compatibilité ascendante dans `Modules/Eshop360/Models/<Model>.php` :
   ```php
   <?php
   namespace Modules\Eshop360\Models;

   /**
    * Backward-compatibility alias.
    * Canonical location: Modules\Eshop360\Domain\Catalog\Models\Product
    */
   class Product extends \Modules\Eshop360\Domain\Catalog\Models\Product
   {
   }
   ```
   **Impact** : tout `use Modules\Eshop360\Models\Product` continue de fonctionner sans aucune modification. Les 50+ consommateurs restent intacts.
5. **Resserrement de la ruleset deptrac** pour `EshopCatalog` :
   - Dépendances autorisées : socles (`Core`, `Auth`, `Users`, `Settings`, `Billing`, `Currency`, `Lang`, `AppLayer`) + `Eshop360` (transition, nécessaire pour `BelongsToChannel` et les alias cross-sous-domaine).
   - Non autorisées : les autres `EshopX` (Inventory, Channel, Sales, …) — les relations passent par les alias `Modules\Eshop360\Models\*` qui sont dans le layer `Eshop360`.
6. **Régénération de la baseline PHPStan** : les erreurs de traits sur les modèles Catalog se déplacent vers le nouveau namespace, la baseline est régénérée pour les capturer (3647 erreurs baselined vs 3656 avant, comparable).

### Phase 2 (future — sous-lots S3 et S5)

- Au sous-lot **S3 Channel** : `BelongsToChannel` sera déplacé sous `Domain/Channel/Database/Traits/`. À ce moment, la dépendance `EshopCatalog → Eshop360` pourra être retirée.
- Au sous-lot **S5 Inventory** : `Stock` sera déplacé sous `Domain/Inventory/Models/`. L'import de `Modules\Eshop360\Models\Stock` dans `Product.php` sera remplacé par `Modules\Eshop360\Domain\Inventory\Models\Stock`.
- De même pour `OrderItem` (S8), `Supplier` (S7), `ChannelProductPrice` (S3), `DistributionChannel` (S3).

Les alias de compatibilité resteront en place tant que des consommateurs externes (ex. tests d'intégration avec des strings de FQN) les utilisent. Leur suppression fera l'objet d'un sous-lot de nettoyage final (S12).

## Conséquences

### Positives

- **Zéro casse runtime** : tous les imports existants `use Modules\Eshop360\Models\Product` continuent de fonctionner. Les 659 tests passent inchangés.
- **Déplacement git-friendly** : `git mv` préserve l'historique des fichiers. Les stubs d'alias sont de nouveaux fichiers très courts (6 lignes chacun).
- **Pattern répétable** : S2..S11 suivront exactement ce template (déplacement + alias + resserrement deptrac).
- **Visibilité architecturale** : la structure `Modules/Eshop360/Domain/Catalog/` rend le sous-domaine immédiatement identifiable dans l'arborescence et dans les graphs deptrac.
- **Dette technique cantonnée** : les alias sont marqués comme « backward-compat » dans leur PHPDoc. Le sous-lot S12 en documentera la suppression programmée.

### Négatives / coûts

- **Double emplacement temporaire** : chaque modèle Catalog existe en 2 fichiers (le canon dans `Domain/Catalog/Models/` + le stub dans `Models/`). Acceptable pendant la transition, supprimé en S12.
- **Product.php contient maintenant des imports cross-sous-domaine** (Stock, OrderItem, etc.) qui référencent les alias. Ils seront remplacés au fil des sous-lots.
- **Baseline PHPStan régénérée** : les entrées baseline pour les modèles Catalog pointent désormais vers `Domain/Catalog/Models/`. Toute régression serait détectée, mais la baseline globale est un filet moins fin qu'une résolution individuelle.
- **Dépendance transitoire `EshopCatalog → Eshop360`** autorisée dans la ruleset, à lever au sous-lot S3.

### Neutres

- Les migrations restent dans `Modules/Eshop360/Database/Migrations/` — pas de déplacement prévu (§ ADR-008).
- Les controllers Catalog restent dans `Modules/Eshop360/Http/Controllers/Catalog/` — leur migration éventuelle sous `Domain/Catalog/Http/Controllers/` fera l'objet d'un sous-lot futur, hors S1.
- Les tests Catalog restent dans `Modules/Eshop360/Tests/` — réorganisation par sous-domaine au fil des sous-lots.

## Alternatives considérées

### Alternative A : déplacer sans alias, réécrire tous les imports en une passe

Déplacer les 7 modèles + grep-remplacer tous les `use Modules\Eshop360\Models\Product` par `use Modules\Eshop360\Domain\Catalog\Models\Product` dans les 50+ consommateurs.

**Rejetée parce que** : énorme diff en une passe (plusieurs centaines de fichiers touchés), risque de régression élevé, aucun filet de sécurité si un consommateur utilise une chaîne de FQN non détectée par le grep (ex. `resolve('Modules\Eshop360\Models\Product')` dans un container binding, ou une référence dans `config/` ou dans `Database/Seeders/`). Le pattern alias est le standard en refactoring incrémental.

### Alternative B : move symbolic (namespace alias via composer)

Utiliser `"autoload": { "classmap": ["old → new"] }` de composer pour faire un alias au niveau autoload.

**Rejetée parce que** : non-standard, fragile (dépend de la résolution composer), illisible pour les développeurs qui ne lisent pas `composer.json`. Les stubs PHP sont plus lisibles et grep-friendly.

### Alternative C : laisser les modèles Catalog dans `Modules/Eshop360/Models/` et simplement ajouter un deptrac layer pointant vers cet emplacement

Ne rien déplacer, juste annoter que certains fichiers appartiennent à EshopCatalog via le layer.

**Rejetée parce que** : ne délivre pas le bénéfice visuel et structurel attendu. Un développeur qui ouvre `Modules/Eshop360/Models/` ne voit toujours qu'un gros tas de modèles. Le but de R-101 est de rendre la structure physiquement lisible.

## Implications opérationnelles

### Fichiers déplacés par ce lot

- `Modules/Eshop360/Models/Product.php` → `Modules/Eshop360/Domain/Catalog/Models/Product.php` (+ ajouts d'imports)
- `Modules/Eshop360/Models/Category.php` → `Modules/Eshop360/Domain/Catalog/Models/Category.php`
- `Modules/Eshop360/Models/Brand.php` → `Modules/Eshop360/Domain/Catalog/Models/Brand.php`
- `Modules/Eshop360/Models/ProductGroup.php` → `Modules/Eshop360/Domain/Catalog/Models/ProductGroup.php`
- `Modules/Eshop360/Models/ProductTax.php` → `Modules/Eshop360/Domain/Catalog/Models/ProductTax.php`
- `Modules/Eshop360/Models/ProductVariation.php` → `Modules/Eshop360/Domain/Catalog/Models/ProductVariation.php`
- `Modules/Eshop360/Models/Tax.php` → `Modules/Eshop360/Domain/Catalog/Models/Tax.php`

### Fichiers créés par ce lot (stubs d'alias)

- `Modules/Eshop360/Models/Product.php` (nouveau, 13 lignes, extends canon)
- `Modules/Eshop360/Models/Category.php` (nouveau)
- `Modules/Eshop360/Models/Brand.php` (nouveau)
- `Modules/Eshop360/Models/ProductGroup.php` (nouveau)
- `Modules/Eshop360/Models/ProductTax.php` (nouveau)
- `Modules/Eshop360/Models/ProductVariation.php` (nouveau)
- `Modules/Eshop360/Models/Tax.php` (nouveau)

### Fichiers modifiés par ce lot (outillage + doc)

- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : resserrement ruleset `EshopCatalog`.
- `tools/phpstan/baseline.neon` : régénérée pour capturer les erreurs déplacées vers le nouveau namespace.
- `docs/memory/OPEN_RISKS.md` : R-101 cochage S1 ✅.
- `docs/memory/RECENT_DECISIONS.md` : entrée S1.
- `CHANGELOG_ARCHITECTURAL.md` : `CHG-2026-04-23-007`.

### Tests

- Aucun test modifié. Les 659 tests existants passent inchangés.
- Aucun nouveau test ajouté par ce lot (les tests structurels anti-régression des sous-lots S2..S11 se chargeront de verrouiller le pattern).

### Validation

- `vendor/bin/pest` : 659 passed / 2 failed (pré-existants) / 5 skipped — identique à l'avant-S1.
- `vendor/bin/phpstan analyse --memory-limit=2G` : OK no errors (baseline régénérée).
- `vendor/bin/deptrac analyse` : 0 violations, 13 skipped cross-module (baseline préservée), 1241 dépendances autorisées (vs 1138 avant, augmentation naturelle liée au resserrement EshopCatalog).

## Contraintes imposées au futur

1. **Tout nouveau modèle Catalog** (nouveau produit, attribut, variante) doit être créé directement dans `Modules/Eshop360/Domain/Catalog/Models/` avec le namespace `Modules\Eshop360\Domain\Catalog\Models`. Aucun nouveau modèle dans `Modules/Eshop360/Models/` hors stub d'alias.
2. **Les stubs d'alias** existants dans `Modules/Eshop360/Models/` ne doivent **pas** être modifiés fonctionnellement. Ils ne contiennent que `extends` vers le canon. Toute modification du comportement métier doit se faire dans le fichier canon.
3. **Aucun nouveau consommateur** ne doit importer `Modules\Eshop360\Models\Product` (alias). Les nouveaux appelants doivent utiliser `Modules\Eshop360\Domain\Catalog\Models\Product`. Les consommateurs existants continuent via l'alias jusqu'à migration programmée.
4. **La dépendance `EshopCatalog → Eshop360`** déclarée dans la ruleset deptrac **est temporaire**. Elle sera levée au sous-lot S3 lorsque `BelongsToChannel` aura migré sous `Domain/Channel/Database/Traits/`.
5. **La suppression des alias** sera planifiée au sous-lot S12 (clôture R-101), après migration complète des consommateurs vers les FQN canoniques.

## Références

- ADR-008 — Stratégie de découpage Eshop360 en sous-domaines.
- `docs/Ins/b360_evolution_strategy.md` §1.4 — roadmap Phase 2.
- `docs/cartographie/02_eshop360_focus.md` — détail Catalog.
- `docs/memory/OPEN_RISKS.md` — R-101 S1.
- Commit de clôture : branche `refactor/eshop360-s1-catalog-extraction`.

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la stratégie d'alias évolue (ex. suppression accélérée en production), créer une ADR qui remplace celle-ci.
