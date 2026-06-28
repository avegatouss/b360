<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;

/**
 * Hooks Referentiel360 (ADR-030). En Lot 1 : permissions seulement
 * (consultation + fusion manuelle réservée — UI reportée). Aucun menu / widget.
 */
final class Referentiel360HooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Referentiel360';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'referentiel',
            label: 'Référentiel (tiers)',
            permissions: [
                'referentiel.parties.view' => 'Consulter le référentiel des tiers et les doublons détectés',
                'referentiel.parties.merge' => 'Fusionner / scinder manuellement des tiers',
            ],
            priority: 500,
            module: 'Referentiel360',
        ));
    }
}
