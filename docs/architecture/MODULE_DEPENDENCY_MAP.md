# MODULE_DEPENDENCY_MAP — B360

> Source de vérité des dépendances autorisées entre modules.
> Toute violation est bloquée par Deptrac.

---

## Règles fondatrices

1. **Aucun module ne peut importer un modèle Eloquent d'un autre module.** La communication passe par contrats, services applicatifs ou événements.
2. **Aucun accès DB direct** (`DB::table('eshop_*')` hors namespace `Eshop360`) bloqué par règle PHPStan custom.
3. **Pas de dépendance circulaire** vérifiée par Deptrac.
4. **Le Core ne dépend d'aucun module métier**.

---

## Couches autorisées (deptrac.yaml)

| Couche | Modules | Peut dépendre de |
|---|---|---|
| **L0 — Socle** | Core | (aucun) |
| **L1 — Identité** | Auth, Users, Instances | L0 |
| **L1 — Configuration** | Settings | L0 |
| **L2 — Transverse** | Billing | L0, L1 |
| **L2 — Support** | Lang, Currency, Dashboard, ModuleManager, Demo, Installer | L0, L1 |
| **L3 — Métier** | Eshop360 (catalog, pricing, inventory, sales, finance, crm, channel, hr, projects) | L0, L1, L2 |
| **L4 — Modules futurs** | Menuiserie360, etc. | L0, L1, L2, `Modules/Eshop360/Contracts/*` et `Modules/Eshop360/Events/*` (interfaces + DTO + événements). **Interdit** : `Modules/Eshop360/Domain/*/Models/*`, `Modules/Eshop360/Models/*`, `DB::table('eshop_*')`. Voir [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md). |

---

## Dépendances actuelles (constatées)

| Module source | Dépend de | Type |
|---|---|---|
| Auth | Core, app/User, app/Instance | core / fort |
| Users | Core, app/User, Instances | core / fort |
| Instances | Core, app/Instance, Settings | core / fort |
| Settings | Core | core / fort |
| Billing | Core, Settings, app/Instance | core / fort |
| Dashboard | Core | core / fort |
| Lang | Core, Settings | core / moyen |
| Currency | Core, Settings | core / moyen |
| ModuleManager | Core | core / fort |
| Demo | Core, Eshop360 | support / moyen |
| **Eshop360** | Core, Auth, Users, Settings, Billing, app/User, app/Instance | métier / très fort |

---

## Communications inter-modules autorisées

### Via HookRegistry (préféré)
- `menu` (MenuItem)
- `widgets` (DashboardWidget)
- `settings_groups` (SettingsGroup)
- `permissions` (PermissionGroup)
- `features` (BillableFeature)
- `payment_gateways` (PaymentGatewayDefinition)
- `demo_providers` (DemoDataProvider)
- `notification_types`

### Via Events (recommandé pour découpler)
- À documenter dans `docs/index/EVENT_INDEX.md`

### Via Contracts (pour services partagés)
- À documenter dans `docs/index/API_INDEX.md` section "Contracts internes"

### Via Contracts Eshop360 (pour modules L4)

Pour qu'un module L4 (Menuiserie360, futur CCC360, etc.) consomme Eshop360 :
- **Interfaces synchrones** : `Modules/Eshop360/Contracts/<Domain>/<Reader|Resolver>.php` (DI binding par défaut sur `Modules/Eshop360/Adapters/Eloquent*.php`)
- **DTO immutables** : `Modules/Eshop360/Contracts/<Domain>/*Dto.php`
- **Événements asynchrones** : `Modules/Eshop360/Events/*.php`

Voir [ADR-021](../adr/ADR-021-contracts-for-future-business-modules.md) pour le détail du pattern et le périmètre minimum (Catalog + Customer + Pricing).

---

## Interdictions strictes

- ❌ `use Modules\Eshop360\Models\Product` depuis Menuiserie360 → utiliser un contrat
- ❌ `DB::table('eshop_products')` hors namespace `Eshop360` → bloqué PHPStan
- ❌ Dupliquer un trait, modèle ou service déjà présent dans un autre module → factoriser
- ❌ Créer un module qui dépend de la **vue Blade** d'un autre module → utiliser composants UI partagés
- ❌ Référencer une route d'un module métier (`route('eshop360.*')`, `route('menuiserie360.*')`, etc.) depuis un module socle (L0/L1/L2) **sans guard** `Route::has(...)` → bloqué par `Modules/Core/Tests/Unit/Architecture/NoUnguardedCrossModuleRoutesTest`. Voir R-401 dans `docs/memory/OPEN_RISKS.md`.

### Pattern de guard obligatoire

Quand un module socle DOIT pointer vers une route d'un module métier (cas transitoire, en attendant le passage par HookRegistry) :

```php
// PHP — contrôleur, service
use Illuminate\Support\Facades\Route;

if (Route::has('eshop360.nav.home')) {
    return redirect()->route('eshop360.nav.home', $slug);
}
```

```blade
{{-- Blade — vue --}}
@if(Route::has('eshop360.notifications.index'))
    <a href="{{ route('eshop360.notifications.index', $instance->slug) }}">…</a>
@endif
```

Le test structurel `NoUnguardedCrossModuleRoutesTest` exige qu'un `Route::has('<prefix>.…')` correspondant au préfixe métier appelé apparaisse dans **le même fichier** (peu importe la position, mais en pratique le bloc englobant). Couvre `eshop360`, `menuiserie360`, `ccc360`, `treso360` — étendre la constante `BUSINESS_PREFIXES` du test à chaque nouveau vertical.

---

## Pour étendre cette carte

Toute nouvelle dépendance doit :
1. être documentée ici
2. être ajoutée à `deptrac.yaml`
3. faire l'objet d'un ADR si traverse une couche
