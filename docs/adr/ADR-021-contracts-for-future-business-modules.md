# ADR-021 — Contrats inter-modules pour modules métier futurs

> Architectural Decision Record. Définit comment Menuiserie360 et tout futur module métier consommera Eshop360 sans importer de modèle Eloquent.

## Statut

**Proposé** — 2026-05-08
> Statut promu à "Accepté" après validation humaine explicite (cf. design spec §7).

## Contexte

Après la clôture de R-101 (cf. ADR-020, 2026-05-05), Eshop360 est décomposé en 13 sous-domaines `Modules/Eshop360/Domain/<Sub>/Models/`. La règle de dépendance `MODULE_DEPENDENCY_MAP.md` ligne 71 interdit explicitement à un module futur (L4) d'importer un modèle Eloquent d'Eshop360 :

```
- ❌ use Modules\Eshop360\Models\Product depuis Menuiserie360 → utiliser un contrat
```

Cette règle existe sans pattern technique défini. La spec Menuiserie360 v1.0 (2026-04-04) référence des modèles Eshop360 en direct dans plusieurs sections, ce qui n'est plus admissible. Avant de démarrer Menuiserie360 (ou tout autre module L4 type CCC360), il faut figer le mécanisme.

Le besoin est triple :
1. **Lecture synchrone** : Menuiserie360 doit pouvoir lire un produit, un client, un prix résolu, sans coupler à l'implémentation.
2. **Notifications asynchrones** : certains événements Eshop360 (commande créée, prix changé) intéressent Menuiserie360 sans qu'il faille interroger.
3. **Extension du noyau** : menus, permissions, features, paiements doivent rester découplés (déjà couvert par HookRegistry).

## Décision

Nous adoptons trois mécanismes complémentaires.

### 1. Interfaces + adapters (lecture synchrone) — pattern principal

Les contrats publics d'Eshop360 vivent dans un nouveau namespace `Modules/Eshop360/Contracts/` :

```
Modules/Eshop360/
├── Contracts/
│   ├── Catalog/
│   │   ├── CatalogReader.php      (interface)
│   │   ├── ProductDto.php         (DTO immutable)
│   │   └── CategoryDto.php
│   ├── Customer/
│   │   ├── CustomerReader.php
│   │   └── CustomerDto.php
│   └── Pricing/
│       ├── PricingResolver.php
│       ├── PricingContextDto.php
│       └── PricingResultDto.php
└── Adapters/
    ├── EloquentCatalogReader.php  (implémentation par défaut)
    ├── EloquentCustomerReader.php
    └── EloquentPricingResolver.php
```

Eshop360 enregistre ses adapters Eloquent par défaut dans `Eshop360ServiceProvider::register()` :

```php
$this->app->bind(
    \Modules\Eshop360\Contracts\Catalog\CatalogReader::class,
    \Modules\Eshop360\Adapters\EloquentCatalogReader::class
);
```

Menuiserie360 (et tout autre L4) consomme via DI :

```php
public function __construct(
    private \Modules\Eshop360\Contracts\Catalog\CatalogReader $catalog,
) {}
```

**Périmètre minimum des contrats à exposer** (élargissable à la demande) :
- **Catalog** : lecture produit / catégorie / marque (`findProduct`, `searchProducts`, `productsByCategory`)
- **Customer** : lecture identité (`findCustomer`, `customerExists`)
- **Pricing** : résolution prix par contexte (`resolvePrice(ProductDto, PricingContextDto)`)

**Hors périmètre minimum** (à ajouter quand un consumer le demande) : Channel, Inventory, Finance, Sales, Promotions, HR.

**Justification du minimum** : c'est ce que la spec Menuiserie360 v1.0 référence directement. Tout le reste peut attendre que le besoin émerge.

### 2. Événements DomainEvent (notifications asynchrones)

Eshop360 dispatche des événements typés dans un namespace dédié :

```
Modules/Eshop360/Events/
├── CustomerCreated.php
├── CustomerUpdated.php
├── ProductPriceChanged.php
├── OrderCompleted.php
└── ...
```

Les payloads sont des DTO immutables (les mêmes que ceux de `Contracts/`), pas des modèles Eloquent. Un module L4 listen via `EventServiceProvider` standard Laravel — aucun couplage typé sur le modèle source.

**Liste initiale d'événements exposés** : à compléter au cas par cas. Aucun engagement de stabilité avant qu'un consumer s'enregistre.

### 3. HookRegistry — déjà existant, confirmé

Pour les extensions du noyau (menu, widgets, permissions, features, gateways de paiement, demo providers, notification types), le canal reste **HookRegistry** dans Core (cf. `Modules/Core/Services/HookRegistry.php`). Pattern de référence : `Modules/Eshop360/Providers/Eshop360HooksProvider.php`.

Aucun changement architectural ici — l'ADR-021 confirme et formalise.

### 4. Morphs cross-module — décision différée

Menuiserie360 introduira ses propres tables (`menuiserie_orders`, `menuiserie_invoices`, etc.). Question ouverte : Menuiserie360 peut-il créer des `eshop_payments.payable_type = MenuiserieInvoice` (réutiliser le portefeuille de paiements Eshop360) ?

**Décision** : différée jusqu'au démarrage effectif de Menuiserie360. Contrainte minimale jusqu'à cette décision :
- Si Menuiserie360 introduit des morphs vers Eshop360 → il **doit** ajouter ses entrées dans le morph map central de `Eshop360ServiceProvider::boot()` (cf. ADR-020 §contraintes).
- Si non → il expose son propre `FinanceContract` qui dispatche vers Eshop360 via interface (cf. §1).

### 5. Tests structurels d'isolation

Au démarrage effectif de Menuiserie360 (lot dédié, hors scope de cet ADR), un test PHPStan custom et une règle deptrac devront interdire :
- `use Modules\Eshop360\Domain\*\Models\*` depuis `Modules/Menuiserie360/`
- `use Modules\Eshop360\Models\*` depuis `Modules/Menuiserie360/`
- `DB::table('eshop_*')` depuis `Modules/Menuiserie360/`

Ces tests sont **prévus** dans cet ADR mais **implémentés** au démarrage du module — pas dans ce lot.

## Conséquences

### Positives

- Menuiserie360 (et tout L4) peut consommer Eshop360 sans coupler à son implémentation interne.
- L'extraction d'Eshop360 en vrais modules Laravel (Phase 2 d'ADR-008) reste possible sans rupture chez les consumers.
- Tests unitaires des modules L4 triviaux à isoler (stub d'interface).

### Négatives / coûts

- **Surface à maintenir** : chaque contrat exposé = code Eshop360 à maintenir comme API publique.
- **DTO duplication** : un `ProductDto` n'est pas un `Product` Eloquent — duplication de surface, conversion à la frontière.
- **Lazy loading impossible** via contrat : un consumer doit être explicite sur ce qu'il consomme (compromis acceptable).
- **Pas de transaction cross-module** : un module L4 ne peut pas démarrer une transaction qui couvre Eshop360 + ses propres tables sans pattern explicite (Saga ou orchestrateur).

### Neutres

- HookRegistry (registre central pour menus/permissions/features/etc.) reste inchangé.
- Le morph map central R-101 reste la seule source de vérité pour les types morphiques.

## Alternatives considérées

### Alternative A : API REST/GraphQL interne

Eshop360 expose une API HTTP qu'un module L4 consomme.

**Rejetée parce que** : over-engineering pour cohabitation in-process. Coût latence, sérialisation, authentification interne. Réservé au cas où un L4 deviendrait un service externe (hors scope actuel).

### Alternative B : Événements seuls (event sourcing partiel)

Pas d'interface de lecture synchrone — Menuiserie360 maintient sa propre projection à partir des événements Eshop360.

**Rejetée parce que** : complexité projection trop élevée pour un MVP Menuiserie360. Adoptable plus tard si le pattern §1 montre des limites.

### Alternative C : Modèles Eloquent partagés via un "shared kernel"

Un namespace `Shared/Models/` héberge les modèles partagés entre modules.

**Rejetée parce que** : viole la règle « pas d'import de modèle entre modules » et recrée un couplage invisible. Refusé par le principe modular monolith.

## Implications opérationnelles

- **Code** : à créer au démarrage de Menuiserie360 — `Modules/Eshop360/Contracts/`, `Modules/Eshop360/Adapters/`, bindings dans `Eshop360ServiceProvider::register()`.
- **Tests** : règles deptrac/PHPStan à ajouter en même temps que le code.
- **Documentation** : `MODULE_DEPENDENCY_MAP.md` mis à jour dans ce lot. `docs/index/API_INDEX.md` à enrichir avec les contrats publiés (au démarrage Menuiserie360).
- **Migration** : aucune — les modules existants (Eshop360 lui-même, Demo, etc.) continuent d'utiliser les modèles Eloquent en direct. Seuls les modules L4 sont contraints.
- **Formation** : pattern à expliquer dans `AGENTS.md` et `CLAUDE.md` quand le premier module L4 est créé.

## Contraintes imposées au futur

1. Tout module L4 (couche métier nouveau) **interdit** d'importer `Modules\Eshop360\Domain\*\Models\*` ou `Modules\Eshop360\Models\*`.
2. Tout module L4 **interdit** d'écrire `DB::table('eshop_*')`.
3. Toute extension du périmètre des contrats Eshop360 (ajout d'une interface, ajout d'une méthode) suit la procédure ADR.
4. Les DTO publiés dans `Contracts/` sont **immutables** (constructor-injected, getters seulement).
5. Tout événement publié dans `Modules/Eshop360/Events/` doit être documenté dans `docs/index/EVENT_INDEX.md` (au démarrage du premier consumer).
6. Le morph map central reste la seule source de vérité — un module L4 qui introduit ses propres types morphiques doit ajouter ses entrées dans `Eshop360ServiceProvider::boot()` (cf. ADR-020).

## Références

- [ADR-008](ADR-008-eshop360-subdomain-decomposition-strategy.md) — découpage Eshop360 en sous-domaines (Phase 1 hybride, Phase 2 différée).
- [ADR-020](ADR-020-eshop360-r101-closure.md) — clôture R-101, morph map central, contraintes futures.
- [`docs/architecture/MODULE_DEPENDENCY_MAP.md`](../architecture/MODULE_DEPENDENCY_MAP.md) — règle d'interdiction L4 → modèles Eshop360.
- [`docs/superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md`](../superpowers/specs/2026-05-08-pre-menuiserie360-sprint-design.md) — design du sprint dont ce ADR est le lot 3.
- [`docs/PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md`](../PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P2.1 — origine de la demande.
- [`docs/Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md`](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) v1.0 — spec source des consumers anticipés.
- `Modules/Core/Services/HookRegistry.php` — mécanisme d'extension complémentaire.
- `Modules/Eshop360/Providers/Eshop360HooksProvider.php` — exemple de pattern HookRegistry.

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la décision évolue, créer une nouvelle ADR qui remplace celle-ci, et marquer celle-ci `Remplacé par ADR-XXX`.

L'élargissement du périmètre des contrats (ajout d'une interface ou d'une méthode) ne modifie PAS cet ADR — il s'inscrit dans l'évolution naturelle prévue. Documenter dans `RECENT_DECISIONS.md` à chaque ajout.
