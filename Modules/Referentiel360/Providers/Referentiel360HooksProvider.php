<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;

/**
 * Hooks Referentiel360 (ADR-030 / ADR-031).
 *
 * Permissions : consultation + fusion manuelle (réservée) + finance consolidée.
 * Menu : entrée « Finance consolidée » vers le reporting 360° (lecture seule),
 * filtrée par `referentiel.finance.view`.
 */
final class Referentiel360HooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Referentiel360';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $this->registerMenuItems($registry);
        $this->registerPermissionGroups($registry);
    }

    private function registerMenuItems(HookRegistry $registry): void
    {
        $registry->addMenu(new MenuItem(
            id: 'referentiel360.finance',
            label: 'Finance consolidée',
            route: 'referentiel.finance.reporting',
            icon: 'ti ti-chart-pie',
            priority: 300,
            requiredPermission: 'referentiel.finance.view',
            requiredModule: 'Referentiel360',
            group: 'main',
        ));
    }

    private function registerPermissionGroups(HookRegistry $registry): void
    {
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'referentiel',
            label: 'Référentiel (tiers + articles + finance)',
            permissions: [
                'referentiel.parties.view' => 'Consulter le référentiel des tiers et les doublons détectés',
                'referentiel.parties.merge' => 'Fusionner / scinder manuellement des tiers',
                'referentiel.articles.view' => 'Consulter le référentiel des articles (catalogue unifié)',
                'referentiel.articles.merge' => 'Rapprocher / scinder manuellement des articles (réservé)',
                'referentiel.finance.view' => 'Consulter le registre financier consolidé (CA / encaissement / reste-dû)',
                'referentiel.finance.export' => 'Exporter le registre financier consolidé (réservé)',
            ],
            priority: 500,
            module: 'Referentiel360',
        ));
    }
}
