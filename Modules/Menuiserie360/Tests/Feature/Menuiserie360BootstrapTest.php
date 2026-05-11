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

    public function test_morph_map_contains_expected_menuiserie_entries(): void
    {
        // Cas A v1.3 §1.4ter — Menuiserie360 a son propre morph map propre.
        // P2-7 : 'mnu.invoice' enregistré pour MenuiserieInvoice (BC-Finance).
        // Les modèles non-morphiques (Devis, BC, OF, Chantier) sont référencés
        // par FK standard, pas via polymorphisme.
        $morphMap = \Illuminate\Database\Eloquent\Relations\Relation::morphMap();

        $this->assertArrayHasKey(
            'mnu.invoice',
            $morphMap,
            'P2-7: mnu.invoice doit être dans le morph map propre (Menuiserie360ServiceProvider::registerMorphMap()).'
        );
        $this->assertSame(
            \Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice::class,
            $morphMap['mnu.invoice'],
            'mnu.invoice doit pointer vers MenuiserieInvoice canonique.'
        );
    }
}
