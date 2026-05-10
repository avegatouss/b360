<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature;

use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P0 bootstrap — vérifie que le module Menuiserie360 charge correctement
 * dans l'application : ServiceProvider OK, HookContributor enregistré,
 * permissions et menus présents dans la registry centrale.
 *
 * Ces tests sont la « preuve de vie » du squelette. Aucun test fonctionnel
 * métier ici — ces derniers arrivent en P1+ avec les implémentations.
 */
final class Menuiserie360BootstrapTest extends TestCase
{
    public function test_module_service_provider_is_registered(): void
    {
        $loaded = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(
            \Modules\Menuiserie360\Providers\Menuiserie360ServiceProvider::class,
            $loaded,
            'Menuiserie360ServiceProvider should be registered (modules_statuses.json + module.json).'
        );
    }

    public function test_hooks_provider_is_registered_via_core_config(): void
    {
        // Menuiserie360HooksProvider implémente RegistersHooks (pas Laravel
        // ServiceProvider) — il est enregistré via Modules/Core/Config/hooks.php
        // puis instancié par HookManager. Pour vérifier l'enregistrement, on
        // contrôle la présence dans la config (le test des permissions ci-dessous
        // valide implicitement l'instanciation).
        $configured = (array) config('hooks.providers', []);

        $this->assertContains(
            \Modules\Menuiserie360\Providers\Menuiserie360HooksProvider::class,
            $configured,
            'Menuiserie360HooksProvider should be in Modules/Core/Config/hooks.php providers list.'
        );
    }

    public function test_all_10_validated_permissions_are_registered(): void
    {
        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        $allPermissions = [];
        foreach ($registry->permissions() as $group) {
            // PermissionGroup DTO : public array $permissions (key => label)
            foreach (array_keys($group->permissions) as $perm) {
                $allPermissions[] = $perm;
            }
        }

        $expected = [
            'menuiserie.devis.view',
            'menuiserie.devis.create',
            'menuiserie.bc.validate',
            'menuiserie.client.view',
            'menuiserie.chantier.view',
            'menuiserie.chantier.update',
            'menuiserie.of.create',
            'menuiserie.stock.adjust',
            'menuiserie.invoice.create',
            'menuiserie.report.view',
        ];

        foreach ($expected as $perm) {
            $this->assertContains(
                $perm,
                $allPermissions,
                "Permission `{$perm}` should be registered via HookContributor (spec v1.3 §5.6 validated list)."
            );
        }
    }

    public function test_root_menu_entry_is_registered(): void
    {
        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        // menu() returns hierarchical Collection of root MenuItems with children embedded.
        $menuIds = $registry->menu()->pluck('id')->all();

        $this->assertContains(
            'menuiserie360.root',
            $menuIds,
            'Menuiserie360 root menu item should be registered.'
        );
    }

    public function test_mnu_settings_migration_is_loaded(): void
    {
        $migrator = $this->app['migrator'];
        $paths = $migrator->paths();

        $found = false;
        foreach ($paths as $path) {
            if (str_contains((string) $path, 'Modules\\Menuiserie360\\Database\\Migrations')
                || str_contains((string) $path, 'Modules/Menuiserie360/Database/Migrations')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue(
            $found,
            'Migrations path Modules/Menuiserie360/Database/Migrations should be loaded by ServiceProvider::boot().'
        );
    }

    public function test_morph_map_does_not_contain_menuiserie_entries_yet(): void
    {
        // Cas A v1.3 §1.4ter — Menuiserie360 a son propre morph map propre,
        // séparé du morph map central Eshop360. En P0 le morph map propre est
        // vide (placeholder) car aucun modèle morphique n'existe encore — il
        // sera peuplé en P2-P3 (MenuiserieInvoice, MenuiseriePayment, Devis).
        $morphMap = \Illuminate\Database\Eloquent\Relations\Relation::morphMap();

        foreach ($morphMap as $key => $class) {
            $this->assertStringStartsNotWith(
                'mnu.',
                (string) $key,
                "P0: aucune entrée mnu.* ne doit être dans le morph map encore. Trouvé : {$key} → {$class}"
            );
        }
    }
}
