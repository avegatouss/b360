# ADR-020 — Clôture du découpage Eshop360 (R-101 S12)

> Architectural Decision Record. Sous-lot final R-101 S12 — formalisation
> de l'architecture après les 11 sous-lots d'extraction (S0..S11).

## Statut

**Accepté** — 2026-05-05

## Contexte

S0..S11 ont **physiquement** déplacé les 88 modèles Eshop360 vers
`Modules/Eshop360/Domain/<Sub>/Models/`, en gardant un *alias stub* dans
`Modules/Eshop360/Models/<X>.php` qui `extends` la canonique. Les 233
fichiers consommateurs continuaient d'importer via la legacy FQN
(`Modules\Eshop360\Models\<X>`), résolvant vers l'alias.

Pour stabiliser la polymorphie pendant l'extraction, S8 (ADR-016) puis S9
(ADR-017) ont introduit une mesure défensive : chaque canonique pinnait
`protected $morphClass = \Modules\Eshop360\Models\<Legacy>::class;` afin que
`getMorphClass()` retourne toujours la legacy FQN — peu importe l'import du
consumer. 53 canonicals ont accumulé ce pinning au fil de S8..S11.

S12 ferme le chantier en formalisant les contrats que ces mesures garantissaient.

## Décision

### 1. Morph map central, clés legacy FQN

Un unique `Relation::morphMap([...])` est posé en **première instruction** de
`Eshop360ServiceProvider::boot()`. 88 entrées : clé = legacy FQN, valeur =
classe canonique du Domain. Les 53 `protected $morphClass` individuels sont
supprimés.

**Choix `morphMap` (non-strict) plutôt qu'`enforceMorphMap` (strict)** :
`enforceMorphMap()` lève `ClassMorphViolationException` sur tout modèle non
listé. Tenté en premier, il a cassé 328 tests (tous les morphs touchant
`App\Models\User`, modèles Billing/Auth/etc., et les 2 non-stubs retenus).
On ne pin que les 88 FQN extraites — les autres morphs de l'app continuent
de fonctionner par défaut Eloquent.

**Choix clés = legacy FQN** plutôt que noms courts :

- Les rows production existent avec `payable_type = 'Modules\Eshop360\Models\Order'`.
- `Order::create([])` via la canonique → `getMorphClass()` (via `array_search`
  dans la map) → retourne la clé legacy → DB stocke legacy. Pas de migration
  de données.
- Conséquence : la legacy FQN reste un **contrat public** dans la base.
  Migrer vers des noms courts (`'order'` etc.) est un lot dédié à part
  entière (UPDATE sur 5 tables morphiques + coordination déploiement) —
  hors scope clôture.

### 2. Pas de logique métier modifiée — sauf appels morphiques fautifs

S12 est un refactor d'imports + provider. **Exception** : 12 sites en
production passaient `XYZ::class` directement à des colonnes morph ou des
filtres de query (`StockService::adjustStock` 7e arg, `FneInvoice::create
['invoiceable_type' => Order::class]`, `Payment::where('payable_type',
PurchaseOrder::class)`, etc.). Avec l'alias, `Order::class` valait la legacy
FQN — le bug était masqué. Avec le canonique imposé par S12.3, `Order::class`
devient la canonique FQN, qui ne match plus la legacy stockée → écritures
divergentes, queries vides.

**Fix appliqué** : ces 12 sites passent désormais par `$model->getMorphClass()`,
qui route via la map et reste stable indépendamment du namespace importé.
C'est l'idiome Eloquent attendu — le système d'alias l'avait camouflé pendant
des mois.

### 3. Tests d'invariants

- **`Modules\Eshop360\Tests\Feature\MorphStabilityTest`** (4 tests) :
  - chaque canonique critique (Order, OrderItem, Invoice, Payment,
    StockMovement, FneInvoice, AccountTransaction) reporte la legacy FQN
    via `getMorphClass()`
  - `Relation::getMorphedModel(legacy)` résout vers la canonique
  - un Payment créé via `Payment::create(['payable_type' => $order->getMorphClass(), ...])`
    persiste `payable_type = 'Modules\Eshop360\Models\Order'` et `morphTo()`
    réhydrate l'Order canonique
  - le morph map contient exactement 88 entrées Eshop360
- **`Modules\Eshop360\Tests\Unit\R101ClosureStructuralTest`** (3 tests) :
  - `Modules/Eshop360/Models/` ne contient que `EshopModuleSetting.php` et
    `UserAssignment.php` (anything else = stub réintroduit)
  - le morph map a exactement 88 entrées, toutes pointant vers une classe
    Domain qui existe
  - aucune classe sous `Modules/Eshop360/Domain/*/Models/` ne déclare
    `protected $morphClass` (la map centrale est la seule source de vérité)

Les deux tests sont **anti-régression structurels** — toute PR qui
réintroduit un stub, retire une entrée du map, ou repin un `$morphClass`
échouera.

### 4. Modèles non-stubs retenus

`Modules/Eshop360/Models/EshopModuleSetting.php` et
`Modules/Eshop360/Models/UserAssignment.php` ne sont **pas** des stubs et
ne sont **pas** déplacés. Justification :

- `EshopModuleSetting` : table `eshop_module_settings`, infrastructure de
  stockage des settings de modules. Pas de sous-domaine métier évident.
- `UserAssignment` : table `eshop_user_assignments`, helper de scoping
  cross-ressource utilisé par `OrderUserAssignmentScope` et `BelongsToChannel`.
  Cross-cutting par nature.

Les déplacer serait une décision d'architecture distincte (créer un
sous-domaine `Identity` ? les déplacer dans `EshopShared` ?). Hors scope
clôture.

`Modules/Eshop360/Models/` reste donc un dossier non vide — c'est explicite
et verrouillé par `R101ClosureStructuralTest`.

### 5. Layer deptrac `EshopShared`

L'OPEN_RISKS demandait la "levée des dépendances transitoires
`EshopX → Eshop360`". Post-S12.4, le layer Eshop360 ne contient plus les
stubs, mais contient toujours :

- les services (`StockService`, `ChannelAccessService`, `FinanceService`, …)
- les controllers, jobs, listeners, providers, console commands
- **les traits et scopes partagés** : `Database/Traits/BelongsToChannel`,
  `Database/Scopes/ChannelScope`, etc.

Les EshopX modèles n'utilisent (en théorie) **que** la 3e catégorie.
Lever proprement la dépendance demande de la rendre explicite : on
introduit un layer dédié `EshopShared` qui collecte
`Database/Traits/`, `Database/Scopes/`, `Support/`. Chaque EshopX
remplace `Eshop360` par `EshopShared` dans sa ruleset.

`EshopShared` lui-même dépend de :

- les socles (Core, Auth, Users, Instances, Settings, Billing, Currency, Lang)
- `Eshop360` — `BelongsToChannel`/scopes utilisent `app(ChannelAccessService::class)`
  et `app(UserResourceScopeService::class)`
- `EshopChannel` — `BelongsToChannel::channel()` déclare `belongsTo(DistributionChannel::class)`

La double dépendance `EshopShared ↔ EshopChannel` est tolérable : le trait
référence `DistributionChannel` uniquement comme **type de relation** (pas
d'instanciation). Si un futur lot supprime cette référence (typer la
relation par string), la circularité disparaît.

### 6. Baseline deptrac régénérée

S12.3 (consumer rewrite) a exposé ~185 dépendances cross-EshopX
**réelles** auparavant masquées par le routage via la layer Eshop360
(alias). Catégories :

1. **Traits propagés par deptrac** : `BelongsToChannel` est analysé
   par-classe-utilisatrice plutôt que par-trait, donc chaque modèle qui
   `use BelongsToChannel` apparaît "dépendant de" `ChannelAccessService` +
   `DistributionChannel`. Quirk d'analyse — pas un couplage réel.
2. **Relations métier authentiques** : `Customer → Order, Invoice,
   OnlineOrder, SupportTicket` ; `Product → Stock, OrderItem, Supplier,
   ChannelProductPrice, DistributionChannel` ; `Order → Invoice, Payment,
   InstallmentPlan, EmployeeCommission, Project` ; `DistributionChannel →
   Customer, Product, Warehouse, Coupon, CashRegister, Holding, Order` ;
   etc.

Toutes absorbées dans `skip_violations` (13 → 198 entrées). Une future
itération peut tightener au cas par cas en autorisant les bonnes
cross-deps dans chaque ruleset (ex. `EshopCRM → EshopSales` pour les
relations Customer→Order). C'est explicitement hors scope clôture.

## Conséquences

### Positives

- **R-101 fermée**. Les 13 sous-domaines extraits, le morph map centralisé,
  les imports tous canoniques, les tests d'invariants en place.
- **Bug masqué corrigé** : `XYZ::class` direct dans des colonnes morph est
  maintenant `$model->getMorphClass()` partout — l'idiome Eloquent attendu.
- **Architecture lisible côté deptrac** : `EshopShared` exprime explicitement
  l'infrastructure intra-Eshop360 partagée, sans la mélanger aux services.
- **Pattern réutilisable** : si Menuiserie360 (ou autre module futur)
  consomme un sous-domaine extrait, il importe directement la canonique
  Domain — pas de routage par alias.

### Négatives / coûts

- `Modules/Eshop360/Models/` survit avec 2 fichiers — ce n'est pas un dossier
  vide à supprimer.
- Le morph map keys = legacy FQN pérennise une notation legacy. Migration
  vers short names = lot dédié futur (UPDATE 5 tables × N rows production +
  coordination déploiement).
- `skip_violations` deptrac passe à 198 entrées — bruit visible. Tightening
  cross-EshopX = lot dédié futur.
- Baseline PHPStan 3650 (vs 3704 pré-S12) : -54 (les 53 `$morphClass`
  baselined + 1 cleanup).

## Implications opérationnelles

### Fichiers modifiés (récap S12.1..S12.5)

- `Modules/Eshop360/Providers/Eshop360ServiceProvider.php` (+91 lignes : morph map)
- 53 canoniques `Modules/Eshop360/Domain/*/Models/*.php` (-`$morphClass`)
- 88 stubs supprimés sous `Modules/Eshop360/Models/`
- 229 consumers : `use Modules\Eshop360\Models\X` → canonical
- 34 consumers : inline FQN canonical
- 12 sites : `XYZ::class` → `$model->getMorphClass()` sur columns morph
- 8 tests : `'X_type' => Y::class` → `'X_type' => 'Modules\Eshop360\Models\X'`
- `deptrac.yaml` + `tools/deptrac/deptrac.yaml` : layer EshopShared,
  rulesets EshopX, baseline 198 entrées
- `tools/phpstan/baseline.neon` : régénérée (3650, -54)
- 2 nouveaux tests :
  `Modules/Eshop360/Tests/Feature/MorphStabilityTest.php` (4 tests)
  `Modules/Eshop360/Tests/Unit/R101ClosureStructuralTest.php` (3 tests)

### Tests

Suite séquentielle : **666 passed / 2 failed (pré-existants R-101) / 5 skipped**.
0 régression introduite par S12.

Suite parallèle : flakiness due à Spatie/cache races inter-process,
pré-existante, non causée par S12. Référence canonique = mode séquentiel.

### Déploiement

Aucune migration SQL à exécuter. Les rows existantes avec `*_type =
'Modules\Eshop360\Models\<X>'` continuent d'être lues correctement via le
morph map. Aucun changement de contrat HTTP, événement, ou job.

## Contraintes imposées au futur

1. **Le morph map est la seule source de vérité.** Ajouter un nouveau modèle
   en Domain → ajouter une entrée dans `Eshop360ServiceProvider::boot()`.
   `R101ClosureStructuralTest` enforce.
2. **Aucun `protected $morphClass` réintroduit.** Toute classe Domain qui
   le ferait casse `R101ClosureStructuralTest::test_no_canonical_domain_model_pins_morph_class`.
3. **Aucun stub réintroduit dans `Modules/Eshop360/Models/`.** Toute classe
   ajoutée là (autre que les 2 retenues) casse
   `R101ClosureStructuralTest::test_modules_eshop360_models_directory_only_holds_retained_non_stubs`.
4. **Pour stocker un type morphique : `$model->getMorphClass()`**, jamais
   `Model::class` direct. Idiome obligatoire — le système d'alias qui le
   masquait n'existe plus.
5. **Migration vers short morph keys** : lot dédié, avec UPDATE simultané
   sur `eshop_stock_movements.reference_type`, `eshop_payments.payable_type`,
   `eshop_customer_transactions.*_type`, `eshop_account_transactions.*_type`,
   `eshop_fne_invoices.invoiceable_type`. Coordination déploiement requise.
6. **Tightening deptrac cross-EshopX** : lot dédié. Chaque entrée du
   `skip_violations` baseline R-101 S12 est candidate à passer dans la
   ruleset de l'EshopX concerné (ex. `EshopCRM → EshopSales`) si on accepte
   le couplage métier explicitement.

## Références

- ADR-008 — Stratégie globale R-101 (hybride Domain/<Sub>/ + deptrac sub-layers)
- ADR-009..ADR-019 — sous-lots S1..S11
- ADR-016 §4 — annonce du remplacement `$morphClass` → `enforceMorphMap`
- ADR-017 §plan-S12, ADR-019 §plan-S12 — confirmation
- ADR-011 §S3 — décision de garder `BelongsToChannel` dans `Database/Traits/`,
  révisable en S12 (révisée ici via `EshopShared`)
- Commit série de clôture : branche `refactor/eshop360-s12-closure`
  (5 commits S12.1..S12.5)
