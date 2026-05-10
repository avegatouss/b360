<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;

/**
 * P0-5 / P0-6 — Hooks Menuiserie360 (HookRegistry de Core).
 *
 * Conforme spec v1.3 §5.6 (HookRegistry usage + permissions validées) et
 * ADR-021 §3 (HookRegistry inchangé pour menus/permissions/features).
 *
 * Permissions : 10 permissions validées v1.3 réparties sur 7 BC. Aucune
 * route métier n'est encore exposée (P0 = squelette) — les MenuItems pointent
 * vers des routes qui seront créées en P2..P3 (statut visibleWhen=false en
 * P0 pour ne pas afficher de menu cassé).
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
    }

    // ─── P0-6 : Menus ────────────────────────────────────────────

    private function registerMenuItems(HookRegistry $registry): void
    {
        // Entrée principale Menuiserie360 — masquée tant que les routes ne
        // sont pas créées (P0 = squelette uniquement). En P2-P3, retirer le
        // visibleWhen=false et activer les enfants.
        $registry->addMenu(new MenuItem(
            id: 'menuiserie360.root',
            label: 'Menuiserie',
            icon: 'ti ti-tools',
            priority: 500,
            requiredModule: 'Menuiserie360',
            group: 'main',
            visibleWhen: fn ($user, $instance) => false, // P0 — placeholder
        ));

        // Sous-entrées par BC (toutes masquées en P0 — placeholder).
        // Activées en P2-P3 quand les controllers + routes existeront.
        $bcMenus = [
            ['id' => 'menuiserie360.commercial', 'label' => 'Commercial', 'icon' => 'ti ti-file-invoice', 'priority' => 490],
            ['id' => 'menuiserie360.clients', 'label' => 'Clients', 'icon' => 'ti ti-users', 'priority' => 480],
            ['id' => 'menuiserie360.chantiers', 'label' => 'Chantiers', 'icon' => 'ti ti-building', 'priority' => 470],
            ['id' => 'menuiserie360.production', 'label' => 'Production', 'icon' => 'ti ti-tool', 'priority' => 460],
            ['id' => 'menuiserie360.stocks', 'label' => 'Stocks matières', 'icon' => 'ti ti-package', 'priority' => 450],
            ['id' => 'menuiserie360.finance', 'label' => 'Finance', 'icon' => 'ti ti-cash', 'priority' => 440],
            ['id' => 'menuiserie360.reporting', 'label' => 'Tableaux de bord', 'icon' => 'ti ti-chart-bar', 'priority' => 430],
        ];

        foreach ($bcMenus as $bc) {
            $registry->addMenu(new MenuItem(
                id: $bc['id'],
                label: $bc['label'],
                icon: $bc['icon'],
                priority: $bc['priority'],
                requiredModule: 'Menuiserie360',
                group: 'main',
                parentId: 'menuiserie360.root',
                visibleWhen: fn ($user, $instance) => false, // P0 — placeholder
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
