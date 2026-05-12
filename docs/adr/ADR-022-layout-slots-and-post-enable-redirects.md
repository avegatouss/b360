# ADR-022 — LayoutSlots & PostEnableRedirects via HookRegistry

> Architectural Decision Record. Étend HookRegistry de 2 nouveaux types pour permettre aux modules métier de contribuer du contenu UI nommé dans les layouts socles et de déclarer une route de redirection post-activation, sans créer de coupling code → routes nommées.

## Statut

**Accepté** — 2026-05-12 (implémentation R-401-FIX S1→S7 livrée sur la branche `base`, commits `7cb0187` → S7).

Statut initial **Proposé** — 2026-05-11.

## Contexte

Le lot **R-401** (commit `088bba6`, 2026-05-11) a mitigé un crash `RouteNotFoundException` en ajoutant des guards `Route::has(...)` autour des 6 références dures depuis les modules socles (Dashboard, ModuleManager) vers des routes Eshop360 (notifications, nav home, setup wizard). Cette mitigation est **défensive mais ne supprime pas le couplage** : les modules socles connaissent toujours les noms de routes métier.

Le risque [R-401](../memory/OPEN_RISKS.md) documente explicitement la cible architecturale :

> 1. Notifications → exposer la cloche comme widget via HookRegistry…
> 2. Nav home → exposer via `HookRegistry::menus()` ou un slot de layout…
> 3. Redirect post-enable → contrat `PostEnableRedirector` enregistré via HookRegistry…
> 4. `$hierarchicalMenuEnabled` → slot de layout déclaré côté Core/Dashboard…

L'écosystème B360 utilise déjà HookRegistry pour 8 types (`menu`, `widgets`, `settings_groups`, `permissions`, `notification_types`, `features`, `payment_gateways`, `demo_providers`). Ajouter 2 types pour clore R-401 s'inscrit dans la continuité naturelle du pattern.

Les besoins distincts (un type ne suffit pas) :

1. **Contributions UI nommées dans un layout** : un module veut injecter du HTML à un emplacement précis d'un layout (cloche notifications, FAB navigation, footer custom, etc.). `widgets` est réservé au dashboard et ne couvre pas les layouts.
2. **Route de redirection après activation** : `ModuleController::toggle()` doit savoir si un module nouvellement activé veut rediriger vers son wizard d'initialisation. Aujourd'hui codé en dur (`if ($name === 'Eshop360') redirect(...)`), à transformer en pattern générique.

## Décision

Nous ajoutons **deux nouveaux types** à HookRegistry, strictement additifs (aucune signature existante modifiée).

### 1. `layout_slots` — Contributions UI nommées

DTO : `Modules\Core\Hooks\DTO\LayoutSlotContribution`

```php
final class LayoutSlotContribution
{
    public function __construct(
        public readonly string $id,                    // ex: "eshop360.header.notifications"
        public readonly string $slot,                  // ex: "header.notifications"
        public readonly string $view,                  // ex: "eshop360::layouts.notification-bell"
        public readonly int $priority = 0,
        public readonly ?string $requiredPermission = null,
        public readonly ?string $requiredModule = null,
        public readonly ?Closure $visibleWhen = null,  // fn($user, $instance): bool
        public readonly array $params = [],            // passé à la vue
    ) {}
}
```

API HookRegistry :

```php
$registry->addLayoutSlot(LayoutSlotContribution $c): void
$registry->layoutSlots(?string $slot = null): Collection
$registry->remove('layout_slots', string $id): void   // mécanisme générique existant
```

Consommation côté layout (composant Blade `<x-dashboard::layout-slot>`) :

```blade
<x-dashboard::layout-slot name="header.notifications" :instance="$instance ?? null" />
```

Le composant itère via `HookFilter::filter()` (filtre déjà existant qui applique `requiredModule`, `requiredPermission`, `visibleWhen`), puis fait un `@include($contribution->view, $contribution->params)`.

### 2. `post_enable_redirects` — Route de redirection post-activation

DTO : `Modules\Core\Hooks\DTO\PostEnableRedirect`

```php
final class PostEnableRedirect
{
    public function __construct(
        public readonly string $moduleName,            // ex: "Eshop360"
        public readonly string $route,                 // ex: "eshop360.setup.hub"
        public readonly ?Closure $condition = null,    // fn(Instance): bool — true = redirect actif
        public readonly int $priority = 0,
    ) {}
}
```

API HookRegistry :

```php
$registry->addPostEnableRedirect(PostEnableRedirect $r): void
$registry->postEnableRedirect(string $moduleName): ?PostEnableRedirect
$registry->postEnableRedirects(): Collection
```

Consommation côté contrôleurs :

```php
// ModuleController::toggle() après activation
$redirect = app(HookRegistry::class)->postEnableRedirect($name);
if ($redirect && ($redirect->condition === null || ($redirect->condition)($instance))) {
    return redirect()->route($redirect->route, $instance->slug);
}
```

## Conséquences

### Positives

- **R-401 fermé** : 0 référence `route('<business>.*')` dans les modules socles. Le test structurel `NoUnguardedCrossModuleRoutesTest` devient trivialement vert (0 occurrence à scanner).
- **Symétrie avec les types existants** : mêmes mécanismes `remove`, `override`, ordering par priority. Pas de nouvelle abstraction.
- **Multi-module** : N modules peuvent contribuer au même slot (ex: Eshop360 fournit cloche notifications, futur Menuiserie360 pourrait fournir un widget de relances impayés à `header.alerts`).
- **Découplage View::composer** : retrait du `View::composer(['dashboard::components.layouts.master'])` dans `Eshop360ServiceProvider` — Dashboard ne dépend plus du chargement Eshop360 pour son rendu.

### Négatives / Trade-offs

- **Coupling déplacé, pas supprimé en Phase 1** : les vues `notification-bell.blade.php` et `nav-fab.blade.php` (extraites vers Eshop360) référencent encore `route('eshop360.notifications.*')` et `route('eshop360.nav.home')`. Acceptable car ces vues vivent dans Eshop360 et ne sont chargées que si le module est actif. Phase 2 (lot ultérieur, hors ADR-022) déplacera physiquement le NotificationController vers Core/Dashboard.
- **Nouveau type à apprendre** : 1 type supplémentaire dans HookRegistry. Mitigation : documentation MODULE_DEPENDENCY_MAP, exemples dans Eshop360HooksProvider.
- **Pas de typage fort sur `$slot`** : un module peut écrire `layout_slot('typo.slot.name')` et ne jamais être rendu. Mitigation : convention `<layout>.<position>` (header.*, sidebar.*, footer.*) + tests Feature `DashboardLayoutRenderingTest` qui verrouillent les slots officiels.

### Alternatives rejetées

| Alternative | Pourquoi rejetée |
|---|---|
| Étendre `widgets` pour couvrir aussi les layouts | Mélange dashboard widgets (cards) et layout snippets (bell, FAB) — sémantiques différentes, filtres différents. |
| Module gating dur (interdire l'activation d'un L4 si son L3 est OFF) | Contradit R-403 et empêche le dev en isolation. |
| Null adapters côté Dashboard pour stubber les routes Eshop360 | Anti-pattern : Dashboard implémente une "interface" qui n'existe pas explicitement. Code mort en prod. |
| Garder les `Route::has(...)` guards (mitigation R-401 actuelle) | Coupling toujours présent, fragile à chaque nouveau cas. Le test structurel devient une dette permanente. |

## Contraintes imposées au futur

1. Toute nouvelle contribution UI nommée d'un module dans un layout socle **doit** passer par `addLayoutSlot()`. Aucun nouveau `@include('<module>::…')` en dur ni `route('<business>.*')` en dur dans `Modules/{Core,Dashboard,Auth,Users,Settings,Billing,Lang,Currency,Instances,ModuleManager,Installer,Demo}`.
2. Tout nouveau module métier qui propose un wizard d'initialisation post-activation **doit** déclarer son `PostEnableRedirect` via HookRegistry. Aucun `if ($name === '<Module>')` en dur dans `ModuleController::toggle()`.
3. Les vues extraites des layouts socles vers leurs modules d'origine (`Modules/<Module>/Resources/views/layouts/*`) peuvent référencer librement leurs propres routes nommées — elles sont chargées via le mécanisme `layout_slots` qui garantit l'isolation (requiredModule, requiredPermission).
4. `NoUnguardedCrossModuleRoutesTest` reste actif et continue à scanner les modules socles. Sa promesse devient : "0 occurrence attendue" plutôt que "occurrence gardée par Route::has".

## Mise en œuvre

Plan séquencé en 7 sous-lots : voir [docs/lots/R-401-FIX-impact-analysis.md](../lots/R-401-FIX-impact-analysis.md) §"Plan d'implémentation séquencé".

## Tests structurels associés

- `Modules/Core/Tests/Feature/HookRegistryLayoutSlotsTest` (S1) — 5 tests.
- `Modules/Core/Tests/Feature/HookRegistryPostEnableRedirectsTest` (S1) — 3 tests.
- `Modules/Dashboard/Tests/Feature/LayoutSlotComponentTest` (S2) — 3 tests.
- `Modules/Dashboard/Tests/Feature/DashboardLayoutRenderingTest` (S4) — 4 tests (verrouille l'absence de RouteNotFoundException).
- `Modules/ModuleManager/Tests/Feature/PostEnableRedirectTest` (S6) — 2 tests.
- `Modules/Core/Tests/Unit/Architecture/NoUnguardedCrossModuleRoutesTest` (existant) — reste vert.
- `Modules/Menuiserie360/Tests/Unit/MenuVisibilityTest` (existant) — reste vert (HookFilter inchangé).

## Références

- [R-401](../memory/OPEN_RISKS.md) — risque ouvert que cet ADR ferme.
- [R-402](../memory/OPEN_RISKS.md) — MenuVisibilityTest dépend de HookFilter qui reste inchangé.
- [ADR-021](ADR-021-contracts-for-future-business-modules.md) — pattern HookRegistry pour menus/permissions/features (R-403 §"Contraintes runtime").
- [MODULE_DEPENDENCY_MAP](../architecture/MODULE_DEPENDENCY_MAP.md) — §"Communications inter-modules autorisées" à enrichir au S7.
- [PROTECTED_AREAS](../governance/PROTECTED_AREAS.md) — HookRegistry et ModuleManager sont L2 SENSIBLES.
- [Commit 088bba6](https://github.com/.../commit/088bba6) — mitigation R-401 (origine de ce ADR).
- [docs/lots/R-401-FIX-impact-analysis.md](../lots/R-401-FIX-impact-analysis.md) — IMPACT_ANALYSIS complet + hand-off Codex.

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la décision évolue (par exemple, déplacer physiquement NotificationController dans une Phase 2), créer une nouvelle ADR qui remplace celle-ci ou la complète, sans modifier ce fichier.

L'élargissement du périmètre (un module futur ajoute un nouveau slot ou un nouveau type de redirect) ne modifie pas cet ADR — c'est le pattern qui s'applique. Documenter chaque ajout dans `RECENT_DECISIONS.md`.
