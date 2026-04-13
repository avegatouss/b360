# Eshop360 — Initialisation menu hierarchique & parametres

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Allow Eshop360 to initialize hub + channels + settings via a wizard when hierarchical menu is activated, add an "Administration" tile for instance-level menus, and add channel-scoped settings in the hierarchical menu.

**Architecture:** A new `EshopInitializer` service encapsulates all creation logic. A 3-step Blade wizard (hub, channels, settings) stores data in session and calls the service at the end. The hierarchical menu gets an "Administration" tile (level 1) and a "Parametres" module group (level 2). Three entry points trigger the wizard: ModuleController toggle, EshopSettings toggle, and hierarchical menu fallback.

**Tech Stack:** Laravel 12, Blade, Alpine.js (for dynamic channel list), nwidart/laravel-modules v12, PHPUnit

**Spec:** `docs/superpowers/specs/2026-04-13-eshop360-hierarchical-menu-init-design.md`

---

## File Map

### New Files

| File | Responsibility |
| --- | --- |
| `Modules/Eshop360/Database/Migrations/2026_04_13_100000_add_is_hub_to_eshop_distribution_channels.php` | Add `is_hub` boolean column |
| `Modules/Eshop360/Services/EshopInitializer.php` | Core initialization logic (hub + channels + settings) |
| `Modules/Eshop360/Http/Controllers/Setup/SetupWizardController.php` | 3-step wizard controller |
| `Modules/Eshop360/Http/Controllers/Settings/ChannelSettingsController.php` | Channel-scoped branding & features settings |
| `Modules/Eshop360/Resources/views/setup/layout.blade.php` | Wizard layout with progress bar |
| `Modules/Eshop360/Resources/views/setup/hub.blade.php` | Step 1: Hub configuration |
| `Modules/Eshop360/Resources/views/setup/channels.blade.php` | Step 2: Additional channels (Alpine.js) |
| `Modules/Eshop360/Resources/views/setup/settings.blade.php` | Step 3: Base settings |
| `Modules/Eshop360/Resources/views/hierarchical-menu/admin.blade.php` | Administration tile level 2 |
| `Modules/Eshop360/Resources/views/hierarchical-menu/admin-section.blade.php` | Administration level 3 |
| `Modules/Eshop360/Resources/views/channel-settings/branding.blade.php` | Channel branding form |
| `Modules/Eshop360/Resources/views/channel-settings/features.blade.php` | Channel features toggles |
| `Modules/Eshop360/Tests/Unit/EshopInitializerTest.php` | Unit tests for EshopInitializer |
| `Modules/Eshop360/Tests/Feature/SetupWizardTest.php` | Feature tests for wizard flow |
| `Modules/Eshop360/Tests/Feature/HierarchicalMenuAdminTest.php` | Feature tests for admin tile |

### Modified Files

| File | Change |
| --- | --- |
| `Modules/Eshop360/Models/DistributionChannel.php:20-36` | Add `is_hub` to fillable |
| `Modules/Eshop360/Models/DistributionChannel.php:38-48` | Add `is_hub` to casts |
| `Modules/Eshop360/Services/HierarchicalMenuService.php` | Add `parametres` group, replace `GLOBAL_CHANNEL_SLUG` with `isHubChannel()`, update `getChannels()` ordering |
| `Modules/Eshop360/Providers/Eshop360HooksProvider.php` | Add `eshop360.channel_settings` menu items |
| `Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php` | Add `admin()`, `adminSection()`, modify `home()` for admin tile + wizard fallback |
| `Modules/Eshop360/Resources/views/hierarchical-menu/home.blade.php` | Add Administration tile |
| `Modules/Eshop360/Routes/web.php` | Add setup, admin, and channel-settings routes |
| `Modules/Eshop360/Http/Controllers/Settings/EshopSettingsController.php:25-37` | Redirect to wizard if not initialized |
| `Modules/ModuleManager/Http/Controllers/ModuleController.php:100-115` | Redirect to wizard after Eshop360 enable |

---

## Task 1: Migration — Add `is_hub` to distribution channels

**Files:**
- Create: `Modules/Eshop360/Database/Migrations/2026_04_13_100000_add_is_hub_to_eshop_distribution_channels.php`
- Modify: `Modules/Eshop360/Models/DistributionChannel.php`

- [ ] **Step 1: Create the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eshop_distribution_channels', function (Blueprint $table) {
            $table->boolean('is_hub')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('eshop_distribution_channels', function (Blueprint $table) {
            $table->dropColumn('is_hub');
        });
    }
};
```

- [ ] **Step 2: Add `is_hub` to the model fillable and casts**

In `Modules/Eshop360/Models/DistributionChannel.php`, add `'is_hub'` to the `$fillable` array (after `'is_active'`) and add `'is_hub' => 'boolean'` to the `$casts` array.

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Migration completes, `is_hub` column exists on `eshop_distribution_channels`.

- [ ] **Step 4: Commit**

```bash
git add Modules/Eshop360/Database/Migrations/2026_04_13_100000_add_is_hub_to_eshop_distribution_channels.php Modules/Eshop360/Models/DistributionChannel.php
git commit -m "feat(eshop360): add is_hub column to distribution_channels"
```

---

## Task 2: Update `HierarchicalMenuService` — Replace slug-based hub detection

**Files:**
- Modify: `Modules/Eshop360/Services/HierarchicalMenuService.php`
- Test: `Modules/Eshop360/Tests/Unit/EshopInitializerTest.php` (will cover this in Task 3)

- [ ] **Step 1: Replace `GLOBAL_CHANNEL_SLUG` constant with `isHubChannel()` method**

In `Modules/Eshop360/Services/HierarchicalMenuService.php`, replace:

```php
/**
 * Slug of the global channel that gives access to all menu items.
 */
public const GLOBAL_CHANNEL_SLUG = 'saphir-plus';
```

With:

```php
/**
 * Check if a channel is the hub (central) channel.
 */
public function isHubChannel(DistributionChannel $channel): bool
{
    return (bool) $channel->is_hub;
}
```

- [ ] **Step 2: Update `isGlobalChannel()` to use `is_hub`**

Replace the `isGlobalChannel` method:

```php
/**
 * Check if a slug corresponds to the hub channel.
 */
public function isGlobalChannel(string $slug): bool
{
    $instance = CurrentInstance::get();
    if (!$instance) {
        return false;
    }

    return DistributionChannel::withoutGlobalScopes()
        ->where('instance_id', $instance->id)
        ->where('slug', $slug)
        ->where('is_hub', true)
        ->exists();
}
```

- [ ] **Step 3: Update `getModuleGroups()` hub detection**

In the `getModuleGroups` method, replace:

```php
if ($channel && !$this->isGlobalChannel($channel->slug) && !empty($def['features'])) {
```

With:

```php
if ($channel && !$this->isHubChannel($channel) && !empty($def['features'])) {
```

- [ ] **Step 4: Update `getChannels()` ordering to use `is_hub`**

Replace the ordering in `getChannels()`:

```php
->orderByRaw("CASE WHEN slug = ? THEN 0 ELSE 1 END", [self::GLOBAL_CHANNEL_SLUG])
```

With:

```php
->orderByDesc('is_hub')
```

- [ ] **Step 5: Add `parametres` group to MODULE_GROUPS**

Add to the `MODULE_GROUPS` constant, after the `'charges'` entry:

```php
'parametres' => [
    'label' => 'Paramètres',
    'icon' => 'ti ti-settings',
    'color' => '#64748b',
    'sections' => ['eshop360.channel_settings'],
    'features' => ['settings'],
],
```

- [ ] **Step 6: Commit**

```bash
git add Modules/Eshop360/Services/HierarchicalMenuService.php
git commit -m "refactor(eshop360): replace slug-based hub detection with is_hub flag, add parametres group"
```

---

## Task 3: `EshopInitializer` service

**Files:**
- Create: `Modules/Eshop360/Services/EshopInitializer.php`
- Test: `Modules/Eshop360/Tests/Unit/EshopInitializerTest.php`

- [ ] **Step 1: Write failing test for `isInitialized()`**

Create `Modules/Eshop360/Tests/Unit/EshopInitializerTest.php`:

```php
<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Services\EshopInitializer;
use Modules\Eshop360\Tests\TestCase;

final class EshopInitializerTest extends TestCase
{
    public function test_is_initialized_returns_false_when_no_channels(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $initializer = app(EshopInitializer::class);

        $this->assertFalse($initializer->isInitialized($instance->id));
    }

    public function test_is_initialized_returns_true_when_hub_exists(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Hub Test',
            'slug' => 'hub-test',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0,
            'buy_rate' => 0,
            'debt_share' => 0.3333,
            'channel_share' => 0.3333,
            'owner_share' => 0.3334,
        ]);

        $initializer = app(EshopInitializer::class);

        $this->assertTrue($initializer->isInitialized($instance->id));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=EshopInitializerTest`
Expected: FAIL — class `EshopInitializer` not found.

- [ ] **Step 3: Write the `EshopInitializer` service**

Create `Modules/Eshop360/Services/EshopInitializer.php`:

```php
<?php

namespace Modules\Eshop360\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Warehouse;

final class EshopInitializer
{
    public function __construct(
        private readonly EshopSettingsService $settings,
    ) {}

    public function isInitialized(int $instanceId): bool
    {
        return DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->where('is_hub', true)
            ->exists();
    }

    public function initialize(
        int $instanceId,
        array $hubData,
        array $channels,
        array $baseSettings,
        User $admin,
    ): void {
        DB::transaction(function () use ($instanceId, $hubData, $channels, $baseSettings, $admin) {
            $hub = $this->createHub($instanceId, $hubData, $admin);
            $this->createChannels($instanceId, $channels);
            $this->saveBaseSettings($instanceId, $hub, $baseSettings);
            $this->activateHierarchicalMenu($instanceId);
        });
    }

    private function createHub(int $instanceId, array $hubData, User $admin): DistributionChannel
    {
        $hub = DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instanceId,
            'name' => $hubData['name'],
            'slug' => Str::slug($hubData['name']),
            'code' => $hubData['code'],
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0,
            'buy_rate' => 0,
            'debt_share' => 0.3333,
            'channel_share' => 0.3333,
            'owner_share' => 0.3334,
            'portal_enabled' => false,
            'portal_settings' => ['theme_color' => $hubData['theme_color'] ?? '#4f46e5'],
        ]);

        // Create warehouse for hub
        $warehouse = Warehouse::withoutGlobalScopes()->create([
            'instance_id' => $instanceId,
            'name' => "Entrepot {$hubData['name']}",
            'code' => 'WH-' . strtoupper(Str::slug($hubData['code'], '-')),
            'is_active' => true,
        ]);
        $hub->update(['warehouse_id' => $warehouse->id]);

        // Assign admin
        ChannelUser::create([
            'channel_id' => $hub->id,
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);

        // Set hub features (all enabled by default)
        $features = $hubData['features'] ?? $this->settings->defaults('features');
        // Force all features to true for hub
        $allTrue = array_map(fn () => true, $features);
        $this->settings->setChannelFeatures($allTrue, $hub->id, $instanceId);

        return $hub;
    }

    private function createChannels(int $instanceId, array $channels): void
    {
        foreach ($channels as $channelData) {
            $channel = DistributionChannel::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'name' => $channelData['name'],
                'slug' => Str::slug($channelData['name']),
                'code' => $channelData['code'] ?? strtoupper(Str::substr(Str::slug($channelData['name']), 0, 6)),
                'is_active' => true,
                'is_hub' => false,
                'margin_rate' => $channelData['margin_rate'] ?? 0.13,
                'buy_rate' => $channelData['buy_rate'] ?? 0.20,
                'debt_share' => 0.3333,
                'channel_share' => 0.3333,
                'owner_share' => 0.3334,
                'portal_enabled' => true,
                'portal_settings' => ['theme_color' => $channelData['theme_color'] ?? '#2c3e50'],
            ]);

            // Create warehouse
            $warehouse = Warehouse::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'name' => "Depot {$channelData['name']}",
                'code' => 'WH-' . strtoupper(Str::slug($channelData['code'] ?? $channelData['name'], '-')),
                'is_active' => true,
            ]);
            $channel->update(['warehouse_id' => $warehouse->id]);

            // Set channel features
            if (!empty($channelData['features'])) {
                $this->settings->setChannelFeatures($channelData['features'], $channel->id, $instanceId);
            }
        }
    }

    private function saveBaseSettings(int $instanceId, DistributionChannel $hub, array $baseSettings): void
    {
        // Invoice settings
        $invoiceData = array_intersect_key($baseSettings, array_flip([
            'company_name', 'company_address', 'company_phone', 'company_email',
            'tax_number', 'currency_symbol',
        ]));
        if (!empty($invoiceData)) {
            $invoiceDefaults = $this->settings->defaults('invoice');
            $this->settings->set('invoice', array_merge($invoiceDefaults, $invoiceData), $instanceId);
        }

        // POS settings
        $posData = [];
        if (isset($baseSettings['pos_layout'])) {
            $posData['default_layout'] = $baseSettings['pos_layout'];
        }
        if (isset($baseSettings['payment_methods'])) {
            $posData['payment_methods'] = $baseSettings['payment_methods'];
        }
        if (!empty($posData)) {
            $posDefaults = $this->settings->defaults('pos');
            $this->settings->set('pos', array_merge($posDefaults, $posData), $instanceId);
        }

        // Hub branding (same company info)
        $brandingData = array_intersect_key($baseSettings, array_flip([
            'company_name', 'company_address', 'company_phone', 'company_email',
            'tax_number', 'currency_symbol',
        ]));
        if (!empty($brandingData)) {
            $this->settings->setForChannel('channel_branding', $brandingData, $hub->id, $instanceId);
        }
    }

    private function activateHierarchicalMenu(int $instanceId): void
    {
        $general = $this->settings->get('general', $instanceId);
        $general['hierarchical_menu'] = true;
        $this->settings->set('general', $general, $instanceId);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=EshopInitializerTest`
Expected: 2 tests pass.

- [ ] **Step 5: Write test for `initialize()`**

Add to `EshopInitializerTest.php`:

```php
public function test_initialize_creates_hub_channels_and_settings(): void
{
    [$instance, $admin] = $this->setUpInstanceWithAdmin();

    $initializer = app(EshopInitializer::class);

    $initializer->initialize(
        instanceId: $instance->id,
        hubData: [
            'name' => 'Mon Hub',
            'code' => 'HUB',
            'theme_color' => '#4f46e5',
            'features' => ['sales' => true, 'stock' => true],
        ],
        channels: [
            [
                'name' => 'Canal Test',
                'code' => 'CT',
                'theme_color' => '#2c3e50',
                'margin_rate' => 0.10,
                'features' => ['sales' => true, 'stock' => false],
            ],
        ],
        baseSettings: [
            'company_name' => 'Test Corp',
            'currency_symbol' => 'FCFA',
            'pos_layout' => 'layout2',
            'payment_methods' => ['cash'],
        ],
        admin: $admin,
    );

    // Hub created
    $hub = DistributionChannel::withoutGlobalScopes()
        ->where('instance_id', $instance->id)
        ->where('is_hub', true)
        ->first();
    $this->assertNotNull($hub);
    $this->assertSame('Mon Hub', $hub->name);
    $this->assertSame('mon-hub', $hub->slug);

    // Hub has warehouse
    $this->assertNotNull($hub->warehouse_id);

    // Admin assigned to hub
    $this->assertTrue($hub->isUserMember($admin->id));

    // Channel created
    $channel = DistributionChannel::withoutGlobalScopes()
        ->where('instance_id', $instance->id)
        ->where('is_hub', false)
        ->first();
    $this->assertNotNull($channel);
    $this->assertSame('Canal Test', $channel->name);

    // Hierarchical menu activated
    $settings = app(EshopSettingsService::class);
    $general = $settings->get('general', $instance->id);
    $this->assertTrue($general['hierarchical_menu']);

    // Base settings saved
    $invoice = $settings->get('invoice', $instance->id);
    $this->assertSame('Test Corp', $invoice['company_name']);

    $pos = $settings->get('pos', $instance->id);
    $this->assertSame('layout2', $pos['default_layout']);

    // isInitialized returns true
    $this->assertTrue($initializer->isInitialized($instance->id));
}

public function test_initialize_works_with_zero_channels(): void
{
    [$instance, $admin] = $this->setUpInstanceWithAdmin();

    $initializer = app(EshopInitializer::class);

    $initializer->initialize(
        instanceId: $instance->id,
        hubData: [
            'name' => 'Hub Solo',
            'code' => 'SOLO',
            'theme_color' => '#4f46e5',
            'features' => [],
        ],
        channels: [],
        baseSettings: [
            'company_name' => 'Solo Corp',
            'currency_symbol' => 'FCFA',
        ],
        admin: $admin,
    );

    $count = DistributionChannel::withoutGlobalScopes()
        ->where('instance_id', $instance->id)
        ->count();
    $this->assertSame(1, $count);

    $this->assertTrue($initializer->isInitialized($instance->id));
}
```

- [ ] **Step 6: Run all initializer tests**

Run: `php artisan test --filter=EshopInitializerTest`
Expected: 4 tests pass.

- [ ] **Step 7: Commit**

```bash
git add Modules/Eshop360/Services/EshopInitializer.php Modules/Eshop360/Tests/Unit/EshopInitializerTest.php
git commit -m "feat(eshop360): add EshopInitializer service with tests"
```

---

## Task 4: Setup Wizard — Routes and Controller

**Files:**
- Create: `Modules/Eshop360/Http/Controllers/Setup/SetupWizardController.php`
- Modify: `Modules/Eshop360/Routes/web.php`
- Test: `Modules/Eshop360/Tests/Feature/SetupWizardTest.php`

- [ ] **Step 1: Write failing feature test for wizard step 1**

Create `Modules/Eshop360/Tests/Feature/SetupWizardTest.php`:

```php
<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Tests\TestCase;

final class SetupWizardTest extends TestCase
{
    public function test_hub_step_shows_form_when_not_initialized(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        $response = $this->get("/i/{$instance->slug}/setup/hub");

        $response->assertOk();
        $response->assertViewIs('eshop360::setup.hub');
    }

    public function test_hub_step_redirects_when_already_initialized(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'name' => 'Existing Hub',
            'slug' => 'existing-hub',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.3333, 'channel_share' => 0.3333, 'owner_share' => 0.3334,
        ]);

        $response = $this->get("/i/{$instance->slug}/setup/hub");

        $response->assertRedirect();
    }

    public function test_full_wizard_creates_hub_and_redirects(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        // Step 1: Store hub data
        $response = $this->post("/i/{$instance->slug}/setup/hub", [
            'name' => 'Mon Hub',
            'code' => 'HUB',
            'theme_color' => '#4f46e5',
            'features' => [
                'sales' => '1', 'stock' => '1', 'customers' => '1',
                'orders' => '1', 'pos' => '1',
            ],
        ]);
        $response->assertRedirect("/i/{$instance->slug}/setup/channels");

        // Step 2: Skip channels
        $response = $this->post("/i/{$instance->slug}/setup/channels", [
            'channels' => [],
        ]);
        $response->assertRedirect("/i/{$instance->slug}/setup/settings");

        // Step 3: Store settings and finalize
        $response = $this->post("/i/{$instance->slug}/setup/settings", [
            'company_name' => 'Test SARL',
            'company_address' => '123 Rue Test',
            'company_phone' => '+225 00 00 00 00',
            'company_email' => 'test@test.ci',
            'currency_symbol' => 'FCFA',
            'pos_layout' => 'layout1',
            'payment_methods' => ['cash', 'card'],
        ]);
        $response->assertRedirect("/i/{$instance->slug}/nav");

        // Verify hub was created
        $hub = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('is_hub', true)
            ->first();
        $this->assertNotNull($hub);
        $this->assertSame('Mon Hub', $hub->name);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SetupWizardTest`
Expected: FAIL — route not defined.

- [ ] **Step 3: Add wizard routes to `web.php`**

In `Modules/Eshop360/Routes/web.php`, add the import at the top (after line 77):

```php
use Modules\Eshop360\Http\Controllers\Setup\SetupWizardController;
```

Add the route group after the Hierarchical Menu Navigation block (after line 108):

```php
    // ─── Setup Wizard ─────────────────────────────
    Route::prefix('setup')->name('eshop360.setup.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/hub', [SetupWizardController::class, 'hub'])->name('hub');
        Route::post('/hub', [SetupWizardController::class, 'storeHub'])->name('hub.store');
        Route::get('/channels', [SetupWizardController::class, 'channels'])->name('channels');
        Route::post('/channels', [SetupWizardController::class, 'storeChannels'])->name('channels.store');
        Route::get('/settings', [SetupWizardController::class, 'settings'])->name('settings');
        Route::post('/settings', [SetupWizardController::class, 'storeSettings'])->name('settings.store');
    });
```

- [ ] **Step 4: Create `SetupWizardController`**

Create `Modules/Eshop360/Http/Controllers/Setup/SetupWizardController.php`:

```php
<?php

namespace Modules\Eshop360\Http\Controllers\Setup;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Services\EshopInitializer;
use Modules\Eshop360\Services\EshopSettingsService;

final class SetupWizardController extends Controller
{
    public function __construct(
        private readonly EshopInitializer $initializer,
        private readonly EshopSettingsService $settings,
    ) {}

    // ─── Step 1: Hub ────────────────────────────

    public function hub(): View|RedirectResponse
    {
        $instance = CurrentInstance::get();

        if ($this->initializer->isInitialized($instance->id)) {
            return redirect()->route('eshop360.nav.home', $instance->slug);
        }

        $defaults = [
            'name' => 'Saphir Plus',
            'code' => 'SAPHIR',
            'theme_color' => '#4f46e5',
        ];

        $sessionData = session('eshop360_setup.hub', []);
        $hub = array_merge($defaults, $sessionData);
        $featureDefaults = $this->settings->defaults('features');

        return view('eshop360::setup.hub', compact('hub', 'featureDefaults', 'instance'));
    }

    public function storeHub(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'theme_color' => 'required|string|max:7',
            'features' => 'nullable|array',
            'features.*' => 'in:0,1',
        ]);

        session(['eshop360_setup.hub' => $validated]);

        return redirect()->route('eshop360.setup.channels', $instance->slug);
    }

    // ─── Step 2: Channels ───────────────────────

    public function channels(): View|RedirectResponse
    {
        $instance = CurrentInstance::get();

        if ($this->initializer->isInitialized($instance->id)) {
            return redirect()->route('eshop360.nav.home', $instance->slug);
        }

        if (!session()->has('eshop360_setup.hub')) {
            return redirect()->route('eshop360.setup.hub', $instance->slug);
        }

        $channels = session('eshop360_setup.channels', []);
        $featureDefaults = $this->settings->defaults('features');

        return view('eshop360::setup.channels', compact('channels', 'featureDefaults', 'instance'));
    }

    public function storeChannels(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'channels' => 'nullable|array',
            'channels.*.name' => 'required|string|max:255',
            'channels.*.code' => 'required|string|max:20',
            'channels.*.theme_color' => 'required|string|max:7',
            'channels.*.margin_rate' => 'required|numeric|min:0|max:1',
            'channels.*.features' => 'nullable|array',
            'channels.*.features.*' => 'in:0,1',
        ]);

        session(['eshop360_setup.channels' => $validated['channels'] ?? []]);

        return redirect()->route('eshop360.setup.settings', $instance->slug);
    }

    // ─── Step 3: Settings ───────────────────────

    public function settings(): View|RedirectResponse
    {
        $instance = CurrentInstance::get();

        if ($this->initializer->isInitialized($instance->id)) {
            return redirect()->route('eshop360.nav.home', $instance->slug);
        }

        if (!session()->has('eshop360_setup.hub')) {
            return redirect()->route('eshop360.setup.hub', $instance->slug);
        }

        $sessionData = session('eshop360_setup.settings', []);
        $defaults = [
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'tax_number' => '',
            'currency_symbol' => 'FCFA',
            'pos_layout' => 'layout1',
            'payment_methods' => ['cash', 'card'],
        ];
        $settings = array_merge($defaults, $sessionData);

        return view('eshop360::setup.settings', compact('settings', 'instance'));
    }

    public function storeSettings(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $user = $request->user();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'currency_symbol' => 'nullable|string|max:10',
            'pos_layout' => 'required|in:layout1,layout2,layout3,layout4,layout5',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'string|in:cash,card,cheque,bank_transfer',
        ]);

        $hubData = session('eshop360_setup.hub');
        $channelsData = session('eshop360_setup.channels', []);

        // Normalize features from string '1'/'0' to boolean
        $hubData['features'] = collect($hubData['features'] ?? [])
            ->map(fn ($v) => (bool) $v)->all();

        foreach ($channelsData as &$ch) {
            $ch['features'] = collect($ch['features'] ?? [])
                ->map(fn ($v) => (bool) $v)->all();
            $ch['margin_rate'] = (float) $ch['margin_rate'];
        }
        unset($ch);

        $this->initializer->initialize(
            instanceId: $instance->id,
            hubData: $hubData,
            channels: $channelsData,
            baseSettings: $validated,
            admin: $user,
        );

        // Cleanup session
        session()->forget('eshop360_setup');

        return redirect()->route('eshop360.nav.home', $instance->slug)
            ->with('success', __('Votre espace eShop est pret !'));
    }
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter=SetupWizardTest`
Expected: 3 tests pass (views not yet created, so may need to create stubs first — see Task 5).

- [ ] **Step 6: Commit**

```bash
git add Modules/Eshop360/Http/Controllers/Setup/SetupWizardController.php Modules/Eshop360/Routes/web.php Modules/Eshop360/Tests/Feature/SetupWizardTest.php
git commit -m "feat(eshop360): add setup wizard controller and routes with tests"
```

---

## Task 5: Wizard Views (3 steps + layout)

**Files:**
- Create: `Modules/Eshop360/Resources/views/setup/layout.blade.php`
- Create: `Modules/Eshop360/Resources/views/setup/hub.blade.php`
- Create: `Modules/Eshop360/Resources/views/setup/channels.blade.php`
- Create: `Modules/Eshop360/Resources/views/setup/settings.blade.php`

- [ ] **Step 1: Create wizard layout**

Create `Modules/Eshop360/Resources/views/setup/layout.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Configuration eShop') — {{ $instance->name ?? 'B360' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --wizard-accent: #4f46e5; }
        body { background: #f1f5f9; font-family: 'Inter', system-ui, sans-serif; min-height: 100vh; }
        .wizard-container { max-width: 800px; margin: 0 auto; padding: 2rem 1rem; }
        .wizard-header { text-align: center; margin-bottom: 2rem; }
        .wizard-header img { height: 40px; margin-bottom: 1rem; }
        .wizard-steps { display: flex; justify-content: center; gap: 0; margin-bottom: 2rem; }
        .wizard-step { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; color: #94a3b8; }
        .wizard-step.active { color: var(--wizard-accent); font-weight: 600; }
        .wizard-step.done { color: #10b981; }
        .wizard-step-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; border: 2px solid currentColor; }
        .wizard-step.active .wizard-step-num { background: var(--wizard-accent); color: #fff; border-color: var(--wizard-accent); }
        .wizard-step.done .wizard-step-num { background: #10b981; color: #fff; border-color: #10b981; }
        .wizard-sep { width: 40px; height: 2px; background: #e2e8f0; align-self: center; }
        .wizard-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; }
        .wizard-footer { display: flex; justify-content: space-between; margin-top: 1.5rem; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <img src="{{ URL::asset('build/img/logo.svg') }}" alt="B360">
            <h4 class="fw-bold">Configuration de votre espace eShop</h4>
        </div>

        <div class="wizard-steps">
            <div class="wizard-step @if($step >= 1) {{ $step == 1 ? 'active' : 'done' }} @endif">
                <span class="wizard-step-num">@if($step > 1)<i class="ti ti-check"></i>@else 1 @endif</span>
                <span>Hub central</span>
            </div>
            <div class="wizard-sep"></div>
            <div class="wizard-step @if($step >= 2) {{ $step == 2 ? 'active' : 'done' }} @endif">
                <span class="wizard-step-num">@if($step > 2)<i class="ti ti-check"></i>@else 2 @endif</span>
                <span>Canaux</span>
            </div>
            <div class="wizard-sep"></div>
            <div class="wizard-step @if($step >= 3) {{ $step == 3 ? 'active' : 'done' }} @endif">
                <span class="wizard-step-num">3</span>
                <span>Paramètres</span>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="wizard-card">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
```

- [ ] **Step 2: Create hub view (Step 1)**

Create `Modules/Eshop360/Resources/views/setup/hub.blade.php`:

```blade
@extends('eshop360::setup.layout', ['step' => 1])

@section('content')
    @php $slug = $instance->slug ?? ''; @endphp

    <h5 class="fw-bold mb-3"><i class="ti ti-building-store me-2"></i>Hub central</h5>
    <p class="text-muted mb-4">Le hub central est votre point d'accès principal. Il donne accès à tous les modules.</p>

    <form method="POST" action="{{ route('eshop360.setup.hub.store', $slug) }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom du hub <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $hub['name']) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $hub['code']) }}" maxlength="20" required>
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Couleur thème</label>
                <input type="color" name="theme_color" class="form-control form-control-color w-100"
                       value="{{ old('theme_color', $hub['theme_color']) }}">
            </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3"><i class="ti ti-toggles me-2"></i>Modules activés</h6>
        <p class="text-muted small mb-3">Tous les modules sont activés par défaut pour le hub central.</p>

        <div class="row g-2">
            @foreach($featureDefaults as $feature => $default)
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="features[{{ $feature }}]" value="0">
                        <input class="form-check-input" type="checkbox" name="features[{{ $feature }}]" value="1"
                               id="feat-{{ $feature }}" checked>
                        <label class="form-check-label" for="feat-{{ $feature }}">{{ ucfirst(str_replace('_', ' ', $feature)) }}</label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="wizard-footer">
            <div></div>
            <button type="submit" class="btn btn-primary">
                Suivant <i class="ti ti-arrow-right ms-1"></i>
            </button>
        </div>
    </form>
@endsection
```

- [ ] **Step 3: Create channels view (Step 2)**

Create `Modules/Eshop360/Resources/views/setup/channels.blade.php`:

```blade
@extends('eshop360::setup.layout', ['step' => 2])

@section('content')
    @php $slug = $instance->slug ?? ''; @endphp

    <h5 class="fw-bold mb-3"><i class="ti ti-truck-delivery me-2"></i>Canaux de distribution</h5>
    <p class="text-muted mb-4">Ajoutez vos canaux de distribution (optionnel). Vous pourrez en ajouter d'autres plus tard.</p>

    <form method="POST" action="{{ route('eshop360.setup.channels.store', $slug) }}" x-data="channelManager()">
        @csrf

        <div id="channels-list">
            <template x-for="(channel, index) in channels" :key="index">
                <div class="border rounded-3 p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2"
                            @click="removeChannel(index)">
                        <i class="ti ti-trash"></i>
                    </button>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" :name="'channels['+index+'][name]'" class="form-control"
                                   x-model="channel.name" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" :name="'channels['+index+'][code]'" class="form-control"
                                   x-model="channel.code" maxlength="20" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Couleur</label>
                            <input type="color" :name="'channels['+index+'][theme_color]'"
                                   class="form-control form-control-color w-100" x-model="channel.theme_color">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Taux de marge (%)</label>
                            <input type="number" :name="'channels['+index+'][margin_rate]'" class="form-control"
                                   x-model="channel.margin_rate" step="0.01" min="0" max="1" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Modules activés</label>
                        <div class="row g-1">
                            @foreach($featureDefaults as $feature => $default)
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input type="hidden" :name="'channels['+index+'][features][{{ $feature }}]'" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               :name="'channels['+index+'][features][{{ $feature }}]'" value="1"
                                               :id="'ch-'+index+'-{{ $feature }}'"
                                               {{ $default ? 'checked' : '' }}>
                                        <label class="form-check-label" :for="'ch-'+index+'-{{ $feature }}'">{{ ucfirst(str_replace('_', ' ', $feature)) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" class="btn btn-outline-primary mb-4" @click="addChannel()">
            <i class="ti ti-plus me-1"></i> Ajouter un canal
        </button>

        <div class="wizard-footer">
            <a href="{{ route('eshop360.setup.hub', $slug) }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-outline-secondary">
                    Passer cette étape <i class="ti ti-arrow-right ms-1"></i>
                </button>
                <button type="submit" class="btn btn-primary" x-show="channels.length > 0">
                    Suivant <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
<script>
    function channelManager() {
        return {
            channels: @json($channels ?: []),
            addChannel() {
                this.channels.push({
                    name: '', code: '', theme_color: '#2c3e50', margin_rate: 0.13, features: {}
                });
            },
            removeChannel(index) {
                this.channels.splice(index, 1);
            }
        };
    }
</script>
@endpush
```

- [ ] **Step 4: Create settings view (Step 3)**

Create `Modules/Eshop360/Resources/views/setup/settings.blade.php`:

```blade
@extends('eshop360::setup.layout', ['step' => 3])

@section('content')
    @php $slug = $instance->slug ?? ''; @endphp

    <h5 class="fw-bold mb-3"><i class="ti ti-settings me-2"></i>Paramètres de base</h5>
    <p class="text-muted mb-4">Informations de votre entreprise et configuration du point de vente.</p>

    <form method="POST" action="{{ route('eshop360.setup.settings.store', $slug) }}">
        @csrf

        <h6 class="fw-bold mb-3">Informations société</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                       value="{{ old('company_name', $settings['company_name']) }}" required>
                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="company_email" class="form-control"
                       value="{{ old('company_email', $settings['company_email']) }}">
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Adresse</label>
                <input type="text" name="company_address" class="form-control"
                       value="{{ old('company_address', $settings['company_address']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Téléphone</label>
                <input type="text" name="company_phone" class="form-control"
                       value="{{ old('company_phone', $settings['company_phone']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">N° fiscal</label>
                <input type="text" name="tax_number" class="form-control"
                       value="{{ old('tax_number', $settings['tax_number']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Devise</label>
                <input type="text" name="currency_symbol" class="form-control"
                       value="{{ old('currency_symbol', $settings['currency_symbol']) }}">
            </div>
        </div>

        <hr>

        <h6 class="fw-bold mb-3">Point de vente</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Layout POS</label>
                <select name="pos_layout" class="form-select">
                    @foreach(['layout1' => 'Layout 1 (Standard)', 'layout2' => 'Layout 2', 'layout3' => 'Layout 3', 'layout4' => 'Layout 4', 'layout5' => 'Layout 5'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('pos_layout', $settings['pos_layout']) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Modes de paiement</label>
                @foreach(['cash' => 'Espèces', 'card' => 'Carte', 'cheque' => 'Chèque', 'bank_transfer' => 'Virement'] as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="payment_methods[]" value="{{ $val }}"
                               id="pm-{{ $val }}" @checked(in_array($val, old('payment_methods', $settings['payment_methods'])))>
                        <label class="form-check-label" for="pm-{{ $val }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="wizard-footer">
            <a href="{{ route('eshop360.setup.channels', $slug) }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
            <button type="submit" class="btn btn-success">
                <i class="ti ti-check me-1"></i> Terminer et activer
            </button>
        </div>
    </form>
@endsection
```

- [ ] **Step 5: Run wizard feature tests**

Run: `php artisan test --filter=SetupWizardTest`
Expected: 3 tests pass.

- [ ] **Step 6: Commit**

```bash
git add Modules/Eshop360/Resources/views/setup/
git commit -m "feat(eshop360): add setup wizard views (layout, hub, channels, settings)"
```

---

## Task 6: Entry points — Redirect to wizard

**Files:**
- Modify: `Modules/Eshop360/Http/Controllers/Settings/EshopSettingsController.php`
- Modify: `Modules/ModuleManager/Http/Controllers/ModuleController.php`
- Modify: `Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php`

- [ ] **Step 1: Modify `EshopSettingsController::updateGeneral()`**

In `Modules/Eshop360/Http/Controllers/Settings/EshopSettingsController.php`, replace the `updateGeneral` method (lines 25–37):

```php
public function updateGeneral(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'hierarchical_menu' => 'boolean',
    ]);

    $validated['hierarchical_menu'] = $request->boolean('hierarchical_menu');

    // If enabling hierarchical menu and not yet initialized, redirect to wizard
    if ($validated['hierarchical_menu']) {
        $instance = \Modules\Core\Support\CurrentInstance::get();
        $initializer = app(\Modules\Eshop360\Services\EshopInitializer::class);

        if (!$initializer->isInitialized($instance->id)) {
            return redirect()->route('eshop360.setup.hub', $instance->slug)
                ->with('info', __('Configurez votre espace eShop avant d\'activer le menu hierarchique.'));
        }
    }

    $this->settings->set('general', $validated);

    return redirect()->route('eshop360.settings.general')
        ->with('success', __('Parametres generaux mis a jour.'));
}
```

- [ ] **Step 2: Modify `ModuleController::toggle()`**

In `Modules/ModuleManager/Http/Controllers/ModuleController.php`, replace lines 108–115 (the else branch's end + redirect):

```php
        $message = "Module « {$name} » activé.";

        // If Eshop360 just enabled and not initialized, redirect to wizard
        if ($name === 'Eshop360') {
            $initializer = app(\Modules\Eshop360\Services\EshopInitializer::class);
            if (!$initializer->isInitialized($instance->id)) {
                app(ModuleManager::class)->clearCache();
                return redirect()
                    ->route('eshop360.setup.hub', $instance->slug)
                    ->with('status', "Module « {$name} » activé. Configurez votre espace de vente.");
            }
        }
    }

    app(ModuleManager::class)->clearCache();

    return redirect()
        ->route('modules.index', $instance->slug)
        ->with('status', $message);
```

- [ ] **Step 3: Add wizard fallback in `HierarchicalMenuController::home()`**

In `Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php`, add at the beginning of the `home()` method (after `$channels` is retrieved, before the filter):

```php
public function home()
{
    $user = auth()->user();
    $channels = $this->menuService->getChannels();

    // Fallback: if no channels exist and hierarchical menu is active, redirect to wizard
    if ($channels->isEmpty()) {
        $instance = CurrentInstance::get();
        return redirect()->route('eshop360.setup.hub', $instance->slug);
    }

    // Filter channels by user access (hub admins see all)
    // ... rest of existing code
```

- [ ] **Step 4: Commit**

```bash
git add Modules/Eshop360/Http/Controllers/Settings/EshopSettingsController.php Modules/ModuleManager/Http/Controllers/ModuleController.php Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php
git commit -m "feat(eshop360): add wizard redirect from module toggle, settings, and nav fallback"
```

---

## Task 7: Administration tile in hierarchical menu

**Files:**
- Modify: `Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php`
- Modify: `Modules/Eshop360/Resources/views/hierarchical-menu/home.blade.php`
- Create: `Modules/Eshop360/Resources/views/hierarchical-menu/admin.blade.php`
- Create: `Modules/Eshop360/Resources/views/hierarchical-menu/admin-section.blade.php`
- Modify: `Modules/Eshop360/Routes/web.php`
- Test: `Modules/Eshop360/Tests/Feature/HierarchicalMenuAdminTest.php`

- [ ] **Step 1: Write failing test**

Create `Modules/Eshop360/Tests/Feature/HierarchicalMenuAdminTest.php`:

```php
<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Tests\TestCase;

final class HierarchicalMenuAdminTest extends TestCase
{
    public function test_admin_tile_visible_for_super_admin(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'name' => 'Hub',
            'slug' => 'hub',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.3333, 'channel_share' => 0.3333, 'owner_share' => 0.3334,
        ]);

        $response = $this->get("/i/{$instance->slug}/nav");

        $response->assertOk();
        $response->assertSee('Administration');
    }

    public function test_admin_page_shows_menu_sections(): void
    {
        [$instance, $admin] = $this->setUpInstanceWithAdmin();

        DistributionChannel::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'name' => 'Hub',
            'slug' => 'hub',
            'is_active' => true,
            'is_hub' => true,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.3333, 'channel_share' => 0.3333, 'owner_share' => 0.3334,
        ]);

        $response = $this->get("/i/{$instance->slug}/nav/admin");

        $response->assertOk();
        $response->assertViewIs('eshop360::hierarchical-menu.admin');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HierarchicalMenuAdminTest`
Expected: FAIL.

- [ ] **Step 3: Add admin routes**

In `Modules/Eshop360/Routes/web.php`, inside the nav route group (after line 107), add:

```php
        Route::get('/admin', [HierarchicalMenuController::class, 'admin'])->name('admin');
        Route::get('/admin/{section}', [HierarchicalMenuController::class, 'adminSection'])->name('admin.section');
```

- [ ] **Step 4: Add `admin()` and `adminSection()` methods to controller**

In `Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php`, add these methods and a new import at the top:

```php
use Modules\Core\Hooks\HookManager;
```

```php
/**
 * Administration page — shows instance-level menu sections as tiles.
 * Visible only to super-admin and instance-admin.
 */
public function admin()
{
    $user = auth()->user();
    abort_unless($this->channelAccess->isHubAdmin($user), 403);

    $instance = CurrentInstance::get();
    $registry = app(HookManager::class)->registry();

    // Collect admin group menus + eshop settings menus
    $allMenus = $registry->menu();

    $adminSections = [];

    // Admin group items (Users, Roles, Modules, Settings, Billing)
    $adminItems = $allMenus->filter(fn ($item) => $item->group === 'admin' && !$item->parentId);
    foreach ($adminItems as $item) {
        $children = $allMenus->filter(fn ($child) => $child->parentId === $item->id);
        $adminSections[] = [
            'key' => $item->id,
            'label' => $item->label,
            'icon' => $item->icon ?? 'ti ti-settings',
            'color' => '#475569',
            'children' => $children->values()->all(),
            'count' => $children->count(),
            'route' => $item->route,
        ];
    }

    // Eshop settings group
    $eshopSettings = $allMenus->first(fn ($item) => $item->id === 'eshop360.eshop_settings');
    if ($eshopSettings) {
        $eshopChildren = $allMenus->filter(fn ($child) => $child->parentId === 'eshop360.eshop_settings');
        $adminSections[] = [
            'key' => 'eshop360.eshop_settings',
            'label' => $eshopSettings->label,
            'icon' => $eshopSettings->icon ?? 'ti ti-adjustments-horizontal',
            'color' => '#4f46e5',
            'children' => $eshopChildren->values()->all(),
            'count' => $eshopChildren->count(),
            'route' => null,
        ];
    }

    return view('eshop360::hierarchical-menu.admin', compact('adminSections', 'instance'));
}

/**
 * Administration sub-section — shows action tiles for a specific admin section.
 */
public function adminSection(Request $request, string $slug, string $section)
{
    $user = auth()->user();
    abort_unless($this->channelAccess->isHubAdmin($user), 403);

    $instance = CurrentInstance::get();
    $registry = app(HookManager::class)->registry();
    $allMenus = $registry->menu();

    // Find the parent item
    $parent = $allMenus->first(fn ($item) => $item->id === $section);
    if (!$parent) {
        abort(404, 'Section introuvable');
    }

    $children = $allMenus->filter(fn ($child) => $child->parentId === $section)->values()->all();

    $sectionData = [
        'key' => $parent->id,
        'label' => $parent->label,
        'icon' => $parent->icon ?? 'ti ti-settings',
        'color' => '#475569',
        'children' => $children,
        'count' => count($children),
    ];

    return view('eshop360::hierarchical-menu.admin-section', compact('sectionData', 'instance'));
}
```

- [ ] **Step 5: Update `home.blade.php` to show admin tile**

In `Modules/Eshop360/Resources/views/hierarchical-menu/home.blade.php`, replace the content section:

```blade
@extends('eshop360::hierarchical-menu.layout')

@section('breadcrumb')
    <span class="hm-bc-current">Accueil</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <div class="hm-logo">
            <img src="{{ URL::asset('build/img/logo.svg') }}" alt="B360">
        </div>
        <h1>Bienvenue sur B360</h1>
        <p>Sélectionnez un canal pour commencer</p>
    </div>

    @if($channels->isNotEmpty())
        <div class="hm-grid hm-grid-lg">
            @foreach($channels as $channel)
                <a href="{{ route('eshop360.nav.modules', [$instance?->slug ?? '', $channel->slug]) }}"
                   class="hm-card hm-card-channel"
                   style="--card-accent: {{ $channel->portal_settings['theme_color'] ?? '#4f46e5' }}">
                    <div class="hm-card-icon"
                         style="background: {{ $channel->portal_settings['theme_color'] ?? '#4f46e5' }}">
                        <i class="ti ti-building-store"></i>
                    </div>
                    <div class="hm-card-title">{{ $channel->name }}</div>
                    @if($channel->code)
                        <div class="hm-card-subtitle">{{ $channel->code }}</div>
                    @endif
                </a>
            @endforeach

            {{-- Administration tile — visible for hub admins only --}}
            @if(app(\Modules\Eshop360\Services\ChannelAccessService::class)->isHubAdmin(auth()->user()))
                <a href="{{ route('eshop360.nav.admin', [$instance?->slug ?? '']) }}"
                   class="hm-card hm-card-channel"
                   style="--card-accent: #475569">
                    <div class="hm-card-icon" style="background: #475569">
                        <i class="ti ti-settings-2"></i>
                    </div>
                    <div class="hm-card-title">Administration</div>
                    <div class="hm-card-subtitle">Paramètres & gestion</div>
                </a>
            @endif
        </div>
    @else
        <div class="hm-empty">
            <i class="ti ti-building-store"></i>
            <p>Aucun canal de distribution actif.<br>Configurez vos canaux dans les paramètres.</p>
        </div>
    @endif
@endsection
```

- [ ] **Step 6: Create admin view**

Create `Modules/Eshop360/Resources/views/hierarchical-menu/admin.blade.php`:

```blade
@extends('eshop360::hierarchical-menu.layout', [
    'backUrl' => route('eshop360.nav.home', $instance?->slug ?? ''),
])

@section('breadcrumb')
    <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}">Accueil</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <span class="hm-bc-current">Administration</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <h1>Administration</h1>
        <p>Gestion de l'instance et paramètres</p>
    </div>

    @if(!empty($adminSections))
        <div class="hm-grid hm-grid-lg">
            @foreach($adminSections as $section)
                @if($section['count'] > 0)
                    <a href="{{ route('eshop360.nav.admin.section', [$instance?->slug ?? '', $section['key']]) }}"
                       class="hm-card"
                       style="--card-accent: {{ $section['color'] }}">
                        <div class="hm-card-icon" style="background: {{ $section['color'] }}">
                            <i class="{{ $section['icon'] }}"></i>
                        </div>
                        <div class="hm-card-title">{{ $section['label'] }}</div>
                        <div class="hm-card-subtitle">{{ $section['count'] }} {{ $section['count'] > 1 ? 'éléments' : 'élément' }}</div>
                    </a>
                @elseif($section['route'])
                    <a href="{{ route($section['route'], $instance?->slug ?? '') }}"
                       class="hm-card"
                       style="--card-accent: {{ $section['color'] }}">
                        <div class="hm-card-icon" style="background: {{ $section['color'] }}">
                            <i class="{{ $section['icon'] }}"></i>
                        </div>
                        <div class="hm-card-title">{{ $section['label'] }}</div>
                    </a>
                @endif
            @endforeach
        </div>
    @else
        <div class="hm-empty">
            <i class="ti ti-settings-off"></i>
            <p>Aucune section d'administration disponible.</p>
        </div>
    @endif
@endsection
```

- [ ] **Step 7: Create admin-section view**

Create `Modules/Eshop360/Resources/views/hierarchical-menu/admin-section.blade.php`:

```blade
@extends('eshop360::hierarchical-menu.layout', [
    'backUrl' => route('eshop360.nav.admin', $instance?->slug ?? ''),
])

@section('breadcrumb')
    <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}">Accueil</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <a href="{{ route('eshop360.nav.admin', $instance?->slug ?? '') }}">Administration</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <span class="hm-bc-current">{{ $sectionData['label'] }}</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <h1>{{ $sectionData['label'] }}</h1>
    </div>

    @if(!empty($sectionData['children']))
        <div class="hm-grid">
            @foreach($sectionData['children'] as $item)
                @if($item->route)
                    <a href="{{ route($item->route, $instance?->slug ?? '') }}"
                       class="hm-card hm-card-action"
                       style="--card-accent: {{ $sectionData['color'] }}">
                        <div class="hm-card-icon" style="background: {{ $sectionData['color'] }}">
                            <i class="{{ $item->icon ?? 'ti ti-arrow-right' }}"></i>
                        </div>
                        <div class="hm-card-title">{{ $item->label }}</div>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
@endsection
```

- [ ] **Step 8: Run tests**

Run: `php artisan test --filter=HierarchicalMenuAdminTest`
Expected: 2 tests pass.

- [ ] **Step 9: Commit**

```bash
git add Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php Modules/Eshop360/Resources/views/hierarchical-menu/ Modules/Eshop360/Routes/web.php Modules/Eshop360/Tests/Feature/HierarchicalMenuAdminTest.php
git commit -m "feat(eshop360): add Administration tile in hierarchical menu"
```

---

## Task 8: Channel-scoped settings (Parametres group)

**Files:**
- Modify: `Modules/Eshop360/Providers/Eshop360HooksProvider.php`
- Create: `Modules/Eshop360/Http/Controllers/Settings/ChannelSettingsController.php`
- Create: `Modules/Eshop360/Resources/views/channel-settings/branding.blade.php`
- Create: `Modules/Eshop360/Resources/views/channel-settings/features.blade.php`
- Modify: `Modules/Eshop360/Routes/web.php`

- [ ] **Step 1: Add channel_settings menu items to HooksProvider**

In `Modules/Eshop360/Providers/Eshop360HooksProvider.php`, in the `registerMenuItems` method, add before the Communication section (before line 945):

```php
        // =====================================================================
        // Channel Settings (for hierarchical menu "Parametres" group)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.channel_settings',
            label: 'Paramètres du canal',
            icon: 'ti ti-settings',
            priority: 670,
            requiredPermission: 'eshop.settings.manage',
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.channel_settings.branding',
            label: 'Branding du canal',
            route: 'eshop360.channel-settings.branding',
            priority: 900,
            requiredPermission: 'eshop.settings.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.channel-settings.branding*',
            parentId: 'eshop360.channel_settings',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.channel_settings.features',
            label: 'Modules du canal',
            route: 'eshop360.channel-settings.features',
            priority: 890,
            requiredPermission: 'eshop.settings.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.channel-settings.features*',
            parentId: 'eshop360.channel_settings',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.channel_settings.members',
            label: 'Membres du canal',
            route: 'eshop360.channels.index',
            priority: 880,
            requiredPermission: 'eshop.channels.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.channels.*',
            parentId: 'eshop360.channel_settings',
        ));
```

- [ ] **Step 2: Add channel-settings routes**

In `Modules/Eshop360/Routes/web.php`, add the import at the top:

```php
use Modules\Eshop360\Http\Controllers\Settings\ChannelSettingsController;
```

Add the route group (after the setup wizard routes):

```php
    // ─── Channel Settings ─────────────────────────
    Route::prefix('channel-settings')->name('eshop360.channel-settings.')->middleware('can:eshop.settings.manage')->group(function () {
        Route::get('/branding', [ChannelSettingsController::class, 'branding'])->name('branding');
        Route::put('/branding', [ChannelSettingsController::class, 'updateBranding'])->name('branding.update');
        Route::get('/features', [ChannelSettingsController::class, 'features'])->name('features');
        Route::put('/features', [ChannelSettingsController::class, 'updateFeatures'])->name('features.update');
    });
```

- [ ] **Step 3: Create `ChannelSettingsController`**

Create `Modules/Eshop360/Http/Controllers/Settings/ChannelSettingsController.php`:

```php
<?php

namespace Modules\Eshop360\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Support\CurrentChannel;

final class ChannelSettingsController extends Controller
{
    public function __construct(
        private readonly EshopSettingsService $settings,
    ) {}

    public function branding()
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();

        if (!$channel) {
            return redirect()->route('eshop360.nav.home', $instance->slug)
                ->with('error', __('Selectionnez un canal d\'abord.'));
        }

        $branding = $this->settings->getForChannel('channel_branding', $channel->id);

        return view('eshop360::channel-settings.branding', compact('branding', 'channel', 'instance'));
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();
        abort_unless($channel, 404);

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'invoice_header' => 'nullable|string|max:500',
            'invoice_footer' => 'nullable|string|max:500',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
            'currency_symbol' => 'nullable|string|max:10',
        ]);

        $this->settings->setForChannel('channel_branding', $validated, $channel->id);

        return redirect()->route('eshop360.channel-settings.branding', $instance->slug)
            ->with('success', __('Branding du canal mis a jour.'));
    }

    public function features()
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();

        if (!$channel) {
            return redirect()->route('eshop360.nav.home', $instance->slug)
                ->with('error', __('Selectionnez un canal d\'abord.'));
        }

        $features = $this->settings->getForChannel('features', $channel->id);
        $featureDefaults = $this->settings->defaults('features');

        return view('eshop360::channel-settings.features', compact('features', 'featureDefaults', 'channel', 'instance'));
    }

    public function updateFeatures(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();
        abort_unless($channel, 404);

        $validated = $request->validate([
            'features' => 'required|array',
            'features.*' => 'in:0,1',
        ]);

        $features = collect($validated['features'])->map(fn ($v) => (bool) $v)->all();
        $this->settings->setChannelFeatures($features, $channel->id);

        return redirect()->route('eshop360.channel-settings.features', $instance->slug)
            ->with('success', __('Modules du canal mis a jour.'));
    }
}
```

- [ ] **Step 4: Create branding view**

Create `Modules/Eshop360/Resources/views/channel-settings/branding.blade.php`:

```blade
<x-dashboard::layouts.master
    :title="__('Branding') . ' — ' . $channel->name"
    :instance="$instance"
    :pageTitle="__('Branding du canal') . ' : ' . $channel->name">

@php $slug = $instance->slug ?? ''; @endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('eshop360.channel-settings.branding.update', $slug) }}">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Nom de l'entreprise</label>
            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $branding['company_name'] ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $branding['company_email'] ?? '') }}">
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">Adresse</label>
            <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $branding['company_address'] ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Téléphone</label>
            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $branding['company_phone'] ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">N° fiscal</label>
            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $branding['tax_number'] ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Devise</label>
            <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $branding['currency_symbol'] ?? 'FCFA') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">En-tête facture</label>
            <textarea name="invoice_header" class="form-control" rows="2">{{ old('invoice_header', $branding['invoice_header'] ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Pied de facture</label>
            <textarea name="invoice_footer" class="form-control" rows="2">{{ old('invoice_footer', $branding['invoice_footer'] ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">En-tête ticket</label>
            <textarea name="receipt_header" class="form-control" rows="2">{{ old('receipt_header', $branding['receipt_header'] ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Pied de ticket</label>
            <textarea name="receipt_footer" class="form-control" rows="2">{{ old('receipt_footer', $branding['receipt_footer'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer') }}</button>
    </div>
</form>

</x-dashboard::layouts.master>
```

- [ ] **Step 5: Create features view**

Create `Modules/Eshop360/Resources/views/channel-settings/features.blade.php`:

```blade
<x-dashboard::layouts.master
    :title="__('Modules') . ' — ' . $channel->name"
    :instance="$instance"
    :pageTitle="__('Modules du canal') . ' : ' . $channel->name">

@php $slug = $instance->slug ?? ''; @endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('eshop360.channel-settings.features.update', $slug) }}">
    @csrf @method('PUT')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h6 class="fw-bold mb-0"><i class="ti ti-toggles me-2"></i>{{ __('Modules actifs pour') }} {{ $channel->name }}</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($featureDefaults as $feature => $default)
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input type="hidden" name="features[{{ $feature }}]" value="0">
                            <input class="form-check-input" type="checkbox" name="features[{{ $feature }}]" value="1"
                                   id="feat-{{ $feature }}" @checked(!empty($features[$feature]))>
                            <label class="form-check-label fw-semibold" for="feat-{{ $feature }}">
                                {{ ucfirst(str_replace('_', ' ', $feature)) }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer') }}</button>
    </div>
</form>

</x-dashboard::layouts.master>
```

- [ ] **Step 6: Commit**

```bash
git add Modules/Eshop360/Providers/Eshop360HooksProvider.php Modules/Eshop360/Http/Controllers/Settings/ChannelSettingsController.php Modules/Eshop360/Resources/views/channel-settings/ Modules/Eshop360/Routes/web.php
git commit -m "feat(eshop360): add channel-scoped settings (branding, features) with parametres group"
```

---

## Task 9: Update `DemoChannelsSeeder` to set `is_hub`

**Files:**
- Modify: `Modules/Eshop360/Database/Seeders/DemoChannelsSeeder.php`

- [ ] **Step 1: Add `is_hub` to the hub channel data**

In `Modules/Eshop360/Database/Seeders/DemoChannelsSeeder.php`, in the `seedChannels` method, add `'is_hub' => true` to the Saphir Plus channel data (after `'is_active' => true,` on line 65) and `'is_hub' => false` to the other channels.

For the first channel array entry (Saphir Plus):
```php
'is_active' => true,
'is_hub' => true,
```

For the other two (CODIFARM, PHARMAPLUS):
```php
'is_active' => true,
'is_hub' => false,
```

- [ ] **Step 2: Run existing tests to ensure no regressions**

Run: `php artisan test --filter=DistributionChannelTest`
Expected: All existing tests pass.

- [ ] **Step 3: Commit**

```bash
git add Modules/Eshop360/Database/Seeders/DemoChannelsSeeder.php
git commit -m "fix(eshop360): set is_hub flag in DemoChannelsSeeder"
```

---

## Task 10: Final integration test and cleanup

**Files:**
- Test: `Modules/Eshop360/Tests/Feature/SetupWizardTest.php` (add more tests)

- [ ] **Step 1: Add test for wizard with channels**

Add to `SetupWizardTest.php`:

```php
public function test_wizard_with_channels_creates_hub_and_channels(): void
{
    [$instance, $admin] = $this->setUpInstanceWithAdmin();

    // Step 1
    $this->post("/i/{$instance->slug}/setup/hub", [
        'name' => 'Hub Test',
        'code' => 'HUB',
        'theme_color' => '#4f46e5',
        'features' => ['sales' => '1', 'stock' => '1'],
    ]);

    // Step 2 with channels
    $this->post("/i/{$instance->slug}/setup/channels", [
        'channels' => [
            [
                'name' => 'Canal A',
                'code' => 'CA',
                'theme_color' => '#2c3e50',
                'margin_rate' => '0.13',
                'features' => ['sales' => '1', 'stock' => '0'],
            ],
        ],
    ]);

    // Step 3
    $this->post("/i/{$instance->slug}/setup/settings", [
        'company_name' => 'Multi Corp',
        'currency_symbol' => 'FCFA',
        'pos_layout' => 'layout1',
        'payment_methods' => ['cash'],
    ]);

    // Verify hub + 1 channel = 2 total
    $count = DistributionChannel::withoutGlobalScopes()
        ->where('instance_id', $instance->id)
        ->count();
    $this->assertSame(2, $count);

    // Channel is not hub
    $channel = DistributionChannel::withoutGlobalScopes()
        ->where('instance_id', $instance->id)
        ->where('is_hub', false)
        ->first();
    $this->assertSame('Canal A', $channel->name);
}

public function test_settings_redirect_to_wizard_when_enabling_hierarchical_menu(): void
{
    [$instance, $admin] = $this->setUpInstanceWithAdmin();

    $response = $this->put("/i/{$instance->slug}/eshop-settings/general", [
        'hierarchical_menu' => 1,
    ]);

    $response->assertRedirect("/i/{$instance->slug}/setup/hub");
}
```

- [ ] **Step 2: Run all Eshop360 tests**

Run: `php artisan test --filter=EshopInitializerTest && php artisan test --filter=SetupWizardTest && php artisan test --filter=HierarchicalMenuAdminTest`
Expected: All tests pass.

- [ ] **Step 3: Run the full test suite to check for regressions**

Run: `php artisan test`
Expected: No regressions.

- [ ] **Step 4: Commit**

```bash
git add Modules/Eshop360/Tests/
git commit -m "test(eshop360): add integration tests for wizard and settings redirect"
```
