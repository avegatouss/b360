<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit;

use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\HookFilter;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Modules\ModuleManager;
use Modules\Menuiserie360\Providers\Menuiserie360HooksProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verrouille le fait que Menuiserie360 expose un menu autonome via
 * HookRegistry, indépendamment de l'état d'activation d'Eshop360.
 *
 * Régression historique : les MenuItems étaient déclarés en P0 avec
 * `visibleWhen: fn () => false` (placeholder) — V1 livrée 2026-05-11
 * sans retirer le flag, le module restait silencieux. Ces tests
 * cassent dès qu'on retombe dans ce piège.
 */
final class MenuVisibilityTest extends TestCase
{
    public function test_menu_visible_when_menuiserie360_enabled_and_eshop360_disabled(): void
    {
        $filtered = $this->filterMenu(
            menuiserieEnabled: true,
            eshopEnabled: false,
            grantAllPermissions: true,
        );

        $ids = $filtered->pluck('id')->all();
        $this->assertContains(
            'menuiserie360.root',
            $ids,
            'Le menu Menuiserie doit apparaître même si Eshop360 est désactivé.'
        );

        /** @var MenuItem|null $root */
        $root = $filtered->firstWhere('id', 'menuiserie360.root');
        $this->assertNotNull($root, 'Racine Menuiserie absente du résultat filtré.');
        $childIds = array_map(static fn (MenuItem $c) => $c->id, $root->children);

        foreach ([
            'menuiserie360.commercial',
            'menuiserie360.clients',
            'menuiserie360.chantiers',
            'menuiserie360.production',
            'menuiserie360.stocks',
            'menuiserie360.finance',
            'menuiserie360.reporting',
        ] as $expected) {
            $this->assertContains(
                $expected,
                $childIds,
                "MenuItem `{$expected}` doit être visible sous menuiserie360.root."
            );
        }
    }

    public function test_menu_hidden_when_menuiserie360_module_disabled(): void
    {
        $filtered = $this->filterMenu(
            menuiserieEnabled: false,
            eshopEnabled: false,
            grantAllPermissions: true,
        );

        $this->assertNotContains(
            'menuiserie360.root',
            $filtered->pluck('id')->all(),
            'Le menu Menuiserie doit disparaître si le module est désactivé.'
        );
    }

    public function test_each_child_menu_has_route_and_required_permission(): void
    {
        // Anti-régression du placeholder P0 : si un MenuItem revient sans
        // `route` ou sans `requiredPermission`, ce test casse — on a soit
        // un menu mort (route null → url = '#'), soit une fuite de
        // permission (n'importe qui voit le menu).
        $registry = new HookRegistry;
        (new Menuiserie360HooksProvider)->registerHooks($registry);

        /** @var MenuItem $root */
        $root = $registry->menu()->firstWhere('id', 'menuiserie360.root');
        $this->assertNotNull($root, 'Racine non assemblée — HookRegistry::menu() défaillant ?');

        foreach ($root->children as $child) {
            $this->assertNotNull(
                $child->route,
                "MenuItem `{$child->id}` doit pointer vers une route nommée (régression visibleWhen=false ?)."
            );
            $this->assertNotNull(
                $child->requiredPermission,
                "MenuItem `{$child->id}` doit déclarer une permission Spatie pour son filtre."
            );
        }
    }

    /**
     * Construit le registry, applique le HookFilter avec un ModuleManager
     * stubbé, et renvoie la collection filtrée.
     *
     * @return \Illuminate\Support\Collection<int, MenuItem>
     */
    private function filterMenu(
        bool $menuiserieEnabled,
        bool $eshopEnabled,
        bool $grantAllPermissions,
    ): \Illuminate\Support\Collection {
        $registry = new HookRegistry;
        (new Menuiserie360HooksProvider)->registerHooks($registry);

        $modules = $this->createStub(ModuleManager::class);
        $modules->method('isEnabled')->willReturnCallback(
            static function (string $name) use ($menuiserieEnabled, $eshopEnabled): bool {
                return match (strtoupper($name)) {
                    'MENUISERIE360' => $menuiserieEnabled,
                    'ESHOP360' => $eshopEnabled,
                    default => true,
                };
            }
        );

        $user = new class($grantAllPermissions)
        {
            public function __construct(private readonly bool $grant) {}

            public function can(string $permission): bool
            {
                return $this->grant;
            }
        };

        return (new HookFilter($modules))->filter($registry->menu(), $user, instance: null);
    }
}
