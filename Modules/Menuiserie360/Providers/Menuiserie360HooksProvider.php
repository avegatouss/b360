<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\DemoDataProvider;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Menuiserie360\Database\Seeders\MenuiserieDemoSeeder;

/**
 * Hooks Menuiserie360 — registre Core (HookRegistry + permissions Spatie).
 *
 * Conforme spec v1.3 §5.6 (HookRegistry usage + permissions validées) et
 * ADR-021 §3 (HookRegistry inchangé pour menus/permissions/features).
 *
 * Menus activés depuis P3 (V1 livrée 2026-05-11) : chaque BC pointe sur sa
 * route principale et est filtré par sa permission Spatie. Le module est
 * autonome — il ne dépend d'aucune route d'Eshop360 pour s'afficher.
 */
final class Menuiserie360HooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Menuiserie360';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $this->registerMenuItems($registry);
        $this->registerPermissionGroups($registry);
        $this->registerDemoProviders($registry);
    }

    private function registerDemoProviders(HookRegistry $registry): void
    {
        $registry->addDemoProvider(new DemoDataProvider(
            id: 'menuiserie360.demo',
            label: 'Démo Menuiserie360',
            module: 'Menuiserie360',
            seederClass: MenuiserieDemoSeeder::class,
            priority: 80,
            description: '5 matières + stocks, 3 clients, 1 devis brouillon, 1 devis accepté → BC + acompte + OF + chantier (4 étapes).',
            category: 'metier',
        ));
    }

    private function registerMenuItems(HookRegistry $registry): void
    {
        // Racine du module — visible dès que Menuiserie360 est actif, plus
        // au moins un enfant l'est aussi (filtre HookFilter cache un parent
        // sans route ni enfants visibles).
        $registry->addMenu(new MenuItem(
            id: 'menuiserie360.root',
            label: 'Menuiserie',
            icon: 'ti ti-tools',
            priority: 500,
            requiredModule: 'Menuiserie360',
            group: 'main',
        ));

        // BC → route principale → permission Spatie déclarée plus bas.
        $bcMenus = [
            ['id' => 'menuiserie360.commercial', 'label' => 'Devis',             'icon' => 'ti ti-file-invoice', 'priority' => 490, 'route' => 'menuiserie.devis.index',      'permission' => 'menuiserie.devis.view'],
            ['id' => 'menuiserie360.clients',   'label' => 'Clients',           'icon' => 'ti ti-users',        'priority' => 480, 'route' => 'menuiserie.clients.index',    'permission' => 'menuiserie.client.view'],
            ['id' => 'menuiserie360.chantiers', 'label' => 'Chantiers',         'icon' => 'ti ti-building',     'priority' => 470, 'route' => 'menuiserie.chantiers.index',  'permission' => 'menuiserie.chantier.view'],
            ['id' => 'menuiserie360.production', 'label' => 'Production',        'icon' => 'ti ti-tool',         'priority' => 460, 'route' => 'menuiserie.production.index', 'permission' => 'menuiserie.of.create'],
            ['id' => 'menuiserie360.stocks',    'label' => 'Stocks matières',   'icon' => 'ti ti-package',      'priority' => 450, 'route' => 'menuiserie.stocks.index',     'permission' => 'menuiserie.stock.adjust'],
            ['id' => 'menuiserie360.finance',   'label' => 'Finance',           'icon' => 'ti ti-cash',         'priority' => 440, 'route' => 'menuiserie.factures.index',   'permission' => 'menuiserie.invoice.create'],
            ['id' => 'menuiserie360.reporting', 'label' => 'Tableaux de bord',  'icon' => 'ti ti-chart-bar',    'priority' => 430, 'route' => 'menuiserie.reporting.index',  'permission' => 'menuiserie.report.view'],
        ];

        foreach ($bcMenus as $bc) {
            $registry->addMenu(new MenuItem(
                id: $bc['id'],
                label: $bc['label'],
                route: $bc['route'],
                icon: $bc['icon'],
                priority: $bc['priority'],
                requiredPermission: $bc['permission'],
                requiredModule: 'Menuiserie360',
                group: 'main',
                parentId: 'menuiserie360.root',
            ));
        }
    }

    // ─── P0-5 : Permissions (10 permissions validées v1.3 §5.6) ─

    private function registerPermissionGroups(HookRegistry $registry): void
    {
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'menuiserie.commercial',
            label: 'Commercial Menuiserie',
            permissions: [
                'menuiserie.devis.view' => 'Lire les devis menuiserie',
                'menuiserie.devis.create' => 'Créer un devis menuiserie',
                'menuiserie.bc.validate' => 'Valider un bon de commande',
            ],
            priority: 600,
            module: 'Menuiserie360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'menuiserie.clients',
            label: 'Clients Menuiserie',
            permissions: [
                'menuiserie.client.view' => 'Lire la fiche client menuiserie',
            ],
            priority: 590,
            module: 'Menuiserie360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'menuiserie.chantiers',
            label: 'Chantiers',
            permissions: [
                'menuiserie.chantier.view' => 'Voir les chantiers',
                'menuiserie.chantier.update' => 'Mettre à jour avancement chantier',
            ],
            priority: 580,
            module: 'Menuiserie360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'menuiserie.production',
            label: 'Production Atelier',
            permissions: [
                'menuiserie.of.create' => 'Créer un ordre de fabrication',
            ],
            priority: 570,
            module: 'Menuiserie360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'menuiserie.stocks',
            label: 'Stocks Matières Premières',
            permissions: [
                'menuiserie.stock.adjust' => 'Ajuster stock matières (entrées/sorties)',
                'menuiserie.stock.matiere.manage' => 'Créer / éditer / supprimer une matière première',
            ],
            priority: 560,
            module: 'Menuiserie360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'menuiserie.finance',
            label: 'Finance Menuiserie',
            permissions: [
                'menuiserie.invoice.create' => 'Créer une facture menuiserie',
            ],
            priority: 550,
            module: 'Menuiserie360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'menuiserie.reporting',
            label: 'Reporting Menuiserie',
            permissions: [
                'menuiserie.report.view' => 'Voir les tableaux de bord menuiserie',
            ],
            priority: 540,
            module: 'Menuiserie360',
        ));
    }
}
