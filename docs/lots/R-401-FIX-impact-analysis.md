# IMPACT ANALYSIS — R-401-FIX (LayoutSlots + PostEnableRedirects via HookRegistry)

> Cadrage architectural du lot qui élimine définitivement le coupling cross-module mitigé par R-401.
> ADR associé : [ADR-022](../adr/ADR-022-layout-slots-and-post-enable-redirects.md) (statut **Proposé**).

**Date** : 2026-05-11
**Auteur** : Claude (cadrage architecte) — implémentation prévue par Codex
**Branche suggérée** : `feat/r401-fix-layout-slots-hookregistry`
**Risque ouvert lié** : [R-401](../memory/OPEN_RISKS.md)

---

## Changement demandé (1-3 lignes)

Éliminer les 7 références cross-module entre modules socles (Dashboard, ModuleManager) et modules métier (Eshop360) en introduisant 2 nouveaux types de hook dans HookRegistry : `layout_slots` (contributions UI nommées) et `post_enable_redirects` (route de wizard post-activation module). Retirer les guards `Route::has()` introduits par R-401, qui deviennent inutiles.

---

## Modules touchés directement

| Module | Fichiers | Type de modif |
|---|---|---|
| Core | `Modules/Core/Hooks/Registry/HookRegistry.php` | additif (2 nouveaux types) |
| Core | `Modules/Core/Hooks/DTO/LayoutSlotContribution.php` | nouveau DTO |
| Core | `Modules/Core/Hooks/DTO/PostEnableRedirect.php` | nouveau DTO |
| Dashboard | `Modules/Dashboard/View/Components/LayoutSlot.php` | nouveau composant Blade |
| Dashboard | `Modules/Dashboard/Resources/views/components/layout-slot.blade.php` | nouvelle vue |
| Dashboard | `Modules/Dashboard/Resources/views/components/layouts/master.blade.php` | refactor (retrait 4 routes guardées) |
| Dashboard | `Modules/Dashboard/Http/Controllers/DashboardController.php` | refactor (`postEnableRedirects` au lieu de `Route::has`) |
| ModuleManager | `Modules/ModuleManager/Http/Controllers/ModuleController.php` | refactor (`postEnableRedirects` au lieu de `Route::has`) |
| Eshop360 | `Modules/Eshop360/Providers/Eshop360HooksProvider.php` | additif (registerLayoutSlots + registerPostEnableRedirects) |
| Eshop360 | `Modules/Eshop360/Resources/views/layouts/notification-bell.blade.php` | nouvelle vue (extraction depuis master.blade.php) |
| Eshop360 | `Modules/Eshop360/Resources/views/layouts/nav-fab.blade.php` | nouvelle vue (extraction depuis master.blade.php) |
| Eshop360 | `Modules/Eshop360/Providers/Eshop360ServiceProvider.php` | retrait View::composer `$hierarchicalMenuEnabled` |

## Modules touchés indirectement

| Module | Comment | Impact |
|---|---|---|
| Menuiserie360 | Aucun changement direct ; bénéficie de la nouvelle API HookRegistry pour exposer ses propres slots futurs (P4+). | additif |
| Tous modules HooksProviders | API HookRegistry étendue ; ils peuvent désormais consommer `addLayoutSlot` et `addPostEnableRedirect`. | additif |

---

## Contrats API impactés

- [x] Aucun endpoint HTTP n'est modifié.
- [x] HookRegistry étendu de façon **strictement additive** (4 nouvelles méthodes publiques, aucune signature existante modifiée).

---

## Permissions impactées

- [x] Aucune.

---

## Événements impactés

- [x] Aucun.

---

## Tables impactées

- [x] Aucune (pas de migration DB dans ce lot).

---

## Jobs / queues impactés

- [x] Aucun.

---

## Logs / audit

- [x] Aucun ajout — le preflight R-403 reste en place côté Menuiserie360.

---

## Tests à exécuter

- [ ] `vendor/bin/phpunit Modules/Core/Tests` (HookRegistry + Filter + Manager)
- [ ] `vendor/bin/phpunit Modules/Dashboard/Tests` (LayoutSlot composant + Sidebar inchangé)
- [ ] `vendor/bin/phpunit Modules/ModuleManager/Tests` (toggle post-enable)
- [ ] `vendor/bin/phpunit Modules/Eshop360/Tests` (HooksProvider extension, **Eshop360 ON requis**)
- [ ] `vendor/bin/phpunit Modules/Core/Tests/Unit/Architecture` (NoUnguardedCrossModuleRoutesTest doit rester vert)
- [ ] Suite complète avec **Eshop360 OFF** (modules_statuses.json) → 0 errors / 0 failures attendus

## Tests à ajouter

1. `Modules/Core/Tests/Feature/HookRegistryLayoutSlotsTest` (~5 tests) : add/override/remove/filter par slot/priority.
2. `Modules/Core/Tests/Feature/HookRegistryPostEnableRedirectsTest` (~3 tests) : add/lookup par moduleName/condition closure.
3. `Modules/Dashboard/Tests/Feature/LayoutSlotComponentTest` (~3 tests) : rendu HTML quand 0/1/N contributions, filtre `requiredModule`.
4. `Modules/Dashboard/Tests/Feature/DashboardLayoutRenderingTest` (**~4 tests, crucial**) :
   - Eshop360 ON + auth + instance → cloche notifications rendue.
   - Eshop360 OFF + auth + instance → cloche absente, **aucune RouteNotFoundException**.
   - hierarchical_menu enabled + Eshop360 ON → FAB rendu, sidebar cachée.
   - hierarchical_menu disabled → FAB absent, sidebar visible.
5. `Modules/ModuleManager/Tests/Feature/PostEnableRedirectTest` (~2 tests) : active Eshop360 non-initialisé → redirect setup.hub ; active Eshop360 initialisé → redirect modules.index.

---

## Risques de régression

| Niveau | Risque | Mitigation |
|---|---|---|
| **MAJEUR** | API HookRegistry modifiée — possible cassure de 30+ tests existants | Strictement additif (jamais `remove`/rename d'API existant). Run `Modules/Core/Tests` avant chaque sous-lot. |
| **MAJEUR** | Cassure du rendu hierarchical menu Eshop360 (View::composer retiré) | Migration en 2 commits : (1) introduire le nouveau hook layout_modes et le consommer en parallèle de l'ancien composer, (2) retirer le composer dans un commit séparé après validation. |
| **MOYEN** | NotificationController reste dans Eshop360 → coupling déplacé du master.blade.php vers les vues Eshop360 (vue contient toujours `route('eshop360.notifications.*')`). | Acté dans ADR-022 §scope-out : Phase 1 = pattern hook. Phase 2 (lot ultérieur, hors R-401-FIX) = déplacement physique notifications vers Core/Dashboard. |
| **MOYEN** | `MenuVisibilityTest` (R-402) casse si `HookFilter::filter()` est touché | Aucune modif à `HookFilter`, uniquement ajout de getters HookRegistry. Test vert avant/après. |
| **FAIBLE** | Test `NoUnguardedCrossModuleRoutesTest` retombe à 0 occurrence — trop permissif | Maintenir le test : il prouve désormais l'absence totale de couplage en dur. Renommer si besoin (`NoBusinessRoutesInPlatformTest`). |

---

## Documentation à mettre à jour

- [x] `docs/adr/ADR-022-layout-slots-and-post-enable-redirects.md` — créé en statut "Proposé", à passer en "Accepté" après validation.
- [x] `docs/memory/OPEN_RISKS.md` — R-401 passe en **FERMÉ** une fois le lot mergé.
- [x] `docs/memory/RECENT_DECISIONS.md` — entrée 2026-05-XX (date d'implémentation effective).
- [x] `docs/architecture/MODULE_DEPENDENCY_MAP.md` — section "Communications inter-modules autorisées" enrichie de `layout_slots` et `post_enable_redirects`.
- [x] `docs/memory/CURRENT_STATE.md` — incrément du module Core (nouveau type de hook).

---

## Zones protégées touchées

- [ ] L1 — CRITIQUE : non.
- [x] **L2 — SENSIBLE** :
  - `Modules/Core/Hooks/Registry/HookRegistry.php` (registre central).
  - `Modules/ModuleManager/Http/Controllers/ModuleController.php` (activation/désactivation modules).

→ Procédure renforcée : tests étendus ciblés, double review (Claude + Codex), ADR-022 obligatoire.

---

## Plan d'implémentation séquencé (7 sous-lots)

Chaque sous-lot est un commit séparé qui doit passer Pint + PHPStan + tests ciblés avant de passer au suivant.

### S1 — Nouveaux DTOs + getters HookRegistry (~80 lignes code + ~80 lignes tests)

**Fichiers à créer** :
- `Modules/Core/Hooks/DTO/LayoutSlotContribution.php`

  ```php
  final class LayoutSlotContribution
  {
      public function __construct(
          public readonly string $id,
          public readonly string $slot,           // ex: "header.notifications"
          public readonly string $view,           // ex: "eshop360::layouts.notification-bell"
          public readonly int $priority = 0,
          public readonly ?string $requiredPermission = null,
          public readonly ?string $requiredModule = null,
          public readonly ?Closure $visibleWhen = null,
          public readonly array $params = [],     // passé à la vue
      ) {}
  }
  ```

- `Modules/Core/Hooks/DTO/PostEnableRedirect.php`

  ```php
  final class PostEnableRedirect
  {
      public function __construct(
          public readonly string $moduleName,     // ex: "Eshop360"
          public readonly string $route,          // ex: "eshop360.setup.hub"
          public readonly ?Closure $condition = null, // fn(Instance): bool — true = redirect actif
          public readonly int $priority = 0,
      ) {}
  }
  ```

**Fichiers à modifier** :
- `Modules/Core/Hooks/Registry/HookRegistry.php` :
  - Ajouter `'layout_slots' => []` et `'post_enable_redirects' => []` dans `$items` et `$removed`.
  - Méthodes : `addLayoutSlot()`, `layoutSlots(?string $slot = null)`, `addPostEnableRedirect()`, `postEnableRedirect(string $moduleName)`.

**Tests à ajouter** : `Modules/Core/Tests/Feature/HookRegistryLayoutSlotsTest` + `HookRegistryPostEnableRedirectsTest` (cf. liste ci-dessus).

**DoD S1** : 8+ nouveaux tests verts, suite Core inchangée.

### S2 — Composant Blade `<x-dashboard::layout-slot>` (~60 lignes)

**Fichiers à créer** :
- `Modules/Dashboard/View/Components/LayoutSlot.php` — résout `HookRegistry::layoutSlots($name)`, applique `HookFilter::filter()`, renvoie une `View` qui itère sur les contributions.
- `Modules/Dashboard/Resources/views/components/layout-slot.blade.php` — `@foreach ($contributions as $c) @include($c->view, $c->params) @endforeach`.

**Tests à ajouter** : `LayoutSlotComponentTest` (rendu vide, rendu 1 contribution, filtre par module/permission).

**DoD S2** : composant utilisable via `<x-dashboard::layout-slot name="…" :instance="…" />`.

### S3 — Eshop360 expose ses contributions (~50 lignes)

**Fichiers à créer** :
- `Modules/Eshop360/Resources/views/layouts/notification-bell.blade.php` — copie du bloc 165-233 de master.blade.php (les routes `eshop360.notifications.*` restent dans cette vue Eshop360-interne).
- `Modules/Eshop360/Resources/views/layouts/nav-fab.blade.php` — copie du bloc 323-331.

**Fichiers à modifier** :
- `Modules/Eshop360/Providers/Eshop360HooksProvider.php` :

  ```php
  private function registerLayoutSlots(HookRegistry $r): void
  {
      $r->addLayoutSlot(new LayoutSlotContribution(
          id: 'eshop360.header.notifications',
          slot: 'header.notifications',
          view: 'eshop360::layouts.notification-bell',
          requiredModule: 'Eshop360',
      ));
      $r->addLayoutSlot(new LayoutSlotContribution(
          id: 'eshop360.hierarchical-nav.fab',
          slot: 'hierarchical-nav.fab',
          view: 'eshop360::layouts.nav-fab',
          requiredModule: 'Eshop360',
          visibleWhen: fn ($user, $instance) => /* hierarchical_menu enabled */,
      ));
  }

  private function registerPostEnableRedirects(HookRegistry $r): void
  {
      $r->addPostEnableRedirect(new PostEnableRedirect(
          moduleName: 'Eshop360',
          route: 'eshop360.setup.hub',
          condition: fn (Instance $instance) => /* !EshopInitializer::isInitialized($instance->id) */,
      ));
  }
  ```

**DoD S3** : tests Eshop360 verts (avec Eshop360 ON), `NoUnguardedCrossModuleRoutesTest` toujours vert (les `route('eshop360.*')` sont désormais dans des vues Eshop360, exclues du scan).

### S4 — master.blade.php consomme les slots (~30 lignes)

**Fichiers à modifier** :
- `Modules/Dashboard/Resources/views/components/layouts/master.blade.php` :
  - Remplacer les lignes 164-234 (bloc notifications) par `<x-dashboard::layout-slot name="header.notifications" :instance="$instance ?? null" />`.
  - Remplacer les lignes 322-330 (FAB) par `<x-dashboard::layout-slot name="hierarchical-nav.fab" :instance="$instance ?? null" />`.
  - Retirer les guards `Route::has(...)` introduits par R-401.

**Tests à ajouter** : `DashboardLayoutRenderingTest` (cf. liste — verrouille les 4 scénarios).

**DoD S4** : 0 `route('eshop360.*')` dans `Modules/Dashboard/`. `php artisan view:clear` puis rendu master via Feature test sans crash quel que soit l'état Eshop360.

### S5 — `$hierarchicalMenuEnabled` → hook (~40 lignes)

**Décision à prendre dans le sous-lot** : soit consommer directement le résultat des `layout_slots` du slot `hierarchical-nav.fab` (présent = mode hiérarchique), soit ajouter un type de hook dédié `layout_modes`.

**Recommandation** : la première option (le slot lui-même est le signal — pas besoin d'un nouveau type).

**Fichiers à modifier** :
- `Modules/Eshop360/Providers/Eshop360ServiceProvider.php` : retirer le `View::composer` lignes 303-313.
- `Modules/Dashboard/Resources/views/components/layouts/master.blade.php` : remplacer `@if(!empty($hierarchicalMenuEnabled))` par un test sur la présence de contributions au slot.

**DoD S5** : aucun `View::composer` cross-module restant. Comportement identique pour utilisateur final (vérifié par `DashboardLayoutRenderingTest`).

### S6 — Controllers consomment `postEnableRedirects` (~40 lignes)

**Fichiers à modifier** :
- `Modules/Dashboard/Http/Controllers/DashboardController.php` :

  ```php
  foreach (app(HookRegistry::class)->postEnableRedirects() as $redirect) {
      if (!$modules->isEnabled($redirect->moduleName)) continue;
      if ($redirect->condition && !($redirect->condition)($instance)) continue;
      return redirect()->route($redirect->route, $instance->slug);
  }
  // sinon: rendu normal du dashboard
  ```

  Retirer les imports `Route` et le guard `Route::has('eshop360.nav.home')`.

- `Modules/ModuleManager/Http/Controllers/ModuleController.php` :
  Remplacer le `if ($name === 'Eshop360' && class_exists(...) && Route::has(...))` par une boucle sur `postEnableRedirect($name)`.

**Tests à ajouter** : `PostEnableRedirectTest` (cf. liste).

**DoD S6** : 0 import `eshop360` dans Dashboard et ModuleManager controllers. Tests Feature verts (avec et sans Eshop360 actif).

### S7 — Cleanup + documentation (~50 lignes doc, < 20 lignes code)

**Code** :
- Retirer le check `Route::has('eshop360.notifications.index')` du `@if` ligne 166 master.blade.php si pas encore fait à S4.
- Vérifier qu'aucun `Route::has('eshop360.*')` ne subsiste dans les modules socles.

**Doc** :
- `docs/memory/OPEN_RISKS.md` : R-401 → **FERMÉ** (déplacer dans section "FERMÉ" avec date 2026-05-XX et résumé du lot R-401-FIX).
- `docs/memory/RECENT_DECISIONS.md` : entrée 2026-05-XX résumant ADR-022.
- `docs/architecture/MODULE_DEPENDENCY_MAP.md` : §"Communications inter-modules autorisées" gagne 2 entrées (`layout_slots`, `post_enable_redirects`).
- `docs/memory/CURRENT_STATE.md` : Core mentionne les 2 nouveaux types de hook.
- `docs/adr/ADR-022-…md` : statut passe de "Proposé" à "Accepté".

**DoD S7** :
- 0 référence cross-module en dur dans les modules socles.
- `NoUnguardedCrossModuleRoutesTest` reste vert (trivialement, aucune occurrence à scanner).
- Suite globale verte avec Eshop360 ON (504+ tests).
- Suite globale avec Eshop360 OFF : 0 errors / 42 skipped (R-403 inchangé).

---

## Critères de succès (DoD global du lot)

- ✅ 0 `route('eshop360.*')` dans `Modules/{Core,Dashboard,Auth,Users,Settings,Billing,Lang,Currency,Instances,ModuleManager,Installer,Demo}`.
- ✅ 0 `View::composer` cross-module dans les ServiceProviders Eshop360.
- ✅ Aucun guard `Route::has(...)` introduit par R-401 ne subsiste (devenus inutiles).
- ✅ 13+ nouveaux tests, tous verts.
- ✅ Suite globale `Modules/*/Tests` verte avec Eshop360 ON.
- ✅ Suite globale 0 errors avec Eshop360 OFF (skipped R-403 préservés).
- ✅ ADR-022 en statut **Accepté**.
- ✅ R-401 marqué **FERMÉ** dans OPEN_RISKS.
- ✅ Pint + PHPStan verts sur chaque sous-lot.

---

## Hand-off Codex (à coller dans son prompt)

Voir bloc ci-dessous à copier-coller dans Codex avec le wrapper `templates/prompts/02-codex-implementation.md`.

```
LOT: R-401-FIX — LayoutSlots + PostEnableRedirects via HookRegistry
ADR référencé: docs/adr/ADR-022-layout-slots-and-post-enable-redirects.md (statut Proposé)
IMPACT_ANALYSIS: docs/lots/R-401-FIX-impact-analysis.md (présent fichier)

## Objectif unique

Éliminer les 7 références cross-module entre modules socles et Eshop360 en introduisant
2 nouveaux types de hook dans HookRegistry (layout_slots + post_enable_redirects).
Retirer les guards Route::has() introduits par R-401, devenus inutiles.

## Fichiers cibles autorisés (liste blanche stricte)

CRÉATIONS :
- Modules/Core/Hooks/DTO/LayoutSlotContribution.php
- Modules/Core/Hooks/DTO/PostEnableRedirect.php
- Modules/Dashboard/View/Components/LayoutSlot.php
- Modules/Dashboard/Resources/views/components/layout-slot.blade.php
- Modules/Eshop360/Resources/views/layouts/notification-bell.blade.php
- Modules/Eshop360/Resources/views/layouts/nav-fab.blade.php
- Modules/Core/Tests/Feature/HookRegistryLayoutSlotsTest.php
- Modules/Core/Tests/Feature/HookRegistryPostEnableRedirectsTest.php
- Modules/Dashboard/Tests/Feature/LayoutSlotComponentTest.php
- Modules/Dashboard/Tests/Feature/DashboardLayoutRenderingTest.php
- Modules/ModuleManager/Tests/Feature/PostEnableRedirectTest.php

MODIFICATIONS :
- Modules/Core/Hooks/Registry/HookRegistry.php (additif uniquement)
- Modules/Dashboard/Resources/views/components/layouts/master.blade.php
- Modules/Dashboard/Http/Controllers/DashboardController.php
- Modules/ModuleManager/Http/Controllers/ModuleController.php
- Modules/Eshop360/Providers/Eshop360HooksProvider.php
- Modules/Eshop360/Providers/Eshop360ServiceProvider.php (retrait View::composer)
- docs/memory/OPEN_RISKS.md, docs/memory/RECENT_DECISIONS.md,
  docs/architecture/MODULE_DEPENDENCY_MAP.md, docs/memory/CURRENT_STATE.md
- docs/adr/ADR-022-layout-slots-and-post-enable-redirects.md (passage Proposé → Accepté en S7)

## Fichiers interdits (liste noire)

- Modules/Core/Hooks/HookFilter.php (R-402 dépend de son comportement actuel)
- Modules/Core/Hooks/HookManager.php (auto-discovery existant)
- Tous les autres HooksProviders (DashboardHooksProvider, etc.) — ils restent inchangés
- Tout fichier hors `Modules/{Core,Dashboard,ModuleManager,Eshop360}` + `docs/`

## Séquence à respecter (7 sous-lots, 7 commits)

S1: DTOs + getters HookRegistry + tests Unit (Modules/Core uniquement)
S2: Composant LayoutSlot + vue + test (Modules/Dashboard uniquement)
S3: Eshop360 expose ses 3 contributions (Modules/Eshop360 uniquement)
S4: master.blade.php consomme les slots (Modules/Dashboard uniquement)
S5: Retrait View::composer + slot signal pour hm enabled
S6: Controllers consomment postEnableRedirects (Dashboard + ModuleManager)
S7: Cleanup R-401 + docs + ADR-022 → Accepté

Chaque sous-lot = 1 commit avec format `feat(governance): R-401-FIX SN <description>` sauf
S7 qui est `docs(governance): R-401-FIX S7 cleanup + ADR-022 accepted`.

## Critères de succès (DoD)

Cf. docs/lots/R-401-FIX-impact-analysis.md §"Critères de succès".

Tests bloquants à passer avant chaque commit :
- vendor/bin/pint --test <fichiers du sous-lot>
- vendor/bin/phpstan analyse <fichiers du sous-lot> --memory-limit=1G
- vendor/bin/phpunit Modules/<module ciblé>/Tests

À la fin du S7 :
- vendor/bin/phpunit (suite complète avec Eshop360 ON) : 0 errors / 0 failures attendus.
- modifier modules_statuses.json (Eshop360 → false) localement, relancer la suite :
  0 errors / 42 skipped attendus. PUIS RESTAURER Eshop360 → true avant commit final.

## Garde-fous d'architecture

1. HookRegistry strictement additif (signatures existantes inchangées).
2. Aucun nouveau use Modules\Eshop360\* dans Modules/{Core,Dashboard,ModuleManager}.
3. Les vues notification-bell.blade.php et nav-fab.blade.php sont scopées
   Eshop360 — elles peuvent référencer route('eshop360.notifications.*')
   et route('eshop360.nav.home') sans guard (elles vivent dans Eshop360).
4. NoUnguardedCrossModuleRoutesTest doit rester vert à chaque sous-lot.
5. MenuVisibilityTest (R-402) doit rester vert.
6. Pas de migration DB, pas de seeder, pas de queue/job, pas de permission.

## Hypothèses retenues

- Le slot "hierarchical-nav.fab" sert lui-même de signal "hierarchical menu activé".
  Pas besoin d'un type de hook layout_modes séparé. La sidebar est cachée
  quand HookRegistry::layoutSlots('hierarchical-nav.fab') filtré renvoie au moins
  une contribution visible.
- Le NotificationController reste dans Eshop360. Les vues notification-bell.blade.php
  référencent encore les routes Eshop360, mais elles vivent dans Eshop360 et ne
  sont chargées que si Eshop360 est actif (via HookRegistry → requiredModule).
- Phase 2 (déplacement notifications vers Core, hors scope ce lot) sera arbitrée plus tard.

## Points méritant l'œil de Claude (review)

- L'API du composant `<x-dashboard::layout-slot>` : passe-t-on `instance` explicitement
  ou via dependency injection / View::shared ?
- Faut-il limiter le nombre de slots (whitelist) ou laisser ouvert (any string) ?
- DashboardLayoutRenderingTest doit-il monter une vraie HTTP request authentifiée
  ou tester le composant directement ?

## Mémoire à mettre à jour (S7)

- docs/memory/OPEN_RISKS.md : R-401 → FERMÉ
- docs/memory/RECENT_DECISIONS.md : entrée 2026-05-XX
- docs/architecture/MODULE_DEPENDENCY_MAP.md : nouveaux hooks listés
- docs/memory/CURRENT_STATE.md : Core gain 2 types de hook

## Commit messages suggérés

S1: `feat(governance): R-401-FIX S1 add layout_slots + post_enable_redirects types`
S2: `feat(governance): R-401-FIX S2 x-dashboard::layout-slot component`
S3: `feat(governance): R-401-FIX S3 Eshop360 layout slots + post-enable redirect`
S4: `feat(governance): R-401-FIX S4 master layout consumes hook slots`
S5: `refactor(governance): R-401-FIX S5 retire View::composer hierarchical menu`
S6: `refactor(governance): R-401-FIX S6 controllers consume postEnableRedirects`
S7: `docs(governance): R-401-FIX S7 close R-401 + accept ADR-022`
```

---

## Plan de rollback

Le lot étant séquencé en 7 commits indépendants, chaque sous-lot peut être revert isolément :

```bash
git revert <sha-S7>
git revert <sha-S6>
# ... etc.
```

Le revert de S1 (DTOs + HookRegistry) ne casse rien si S2-S7 sont déjà revert (les nouvelles méthodes deviennent du code mort).

Aucune migration DB → pas de `migrate:rollback` requis.

---

## Validation finale

- [ ] Pipeline qualité 100 % vert sur la branche complète (`make qa`).
- [ ] Tests étendus passants (suite complète Eshop360 ON + suite avec Eshop360 OFF).
- [ ] Mémoire à jour (OPEN_RISKS / RECENT_DECISIONS / MODULE_DEPENDENCY_MAP / CURRENT_STATE).
- [ ] ADR-022 en "Accepté".
- [ ] PR review effectuée par Claude.
- [ ] Review humaine (zone L2 SENSIBLE).
