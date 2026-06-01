<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Contracts;

/**
 * Contrat interne BC-Clients Menuiserie360.
 */
interface ClientRepositoryContract
{
    /**
     * Recupere un client menuiserie natif par son ID local.
     */
    public function find(int $instanceId, int $clientId): ?ClientMenuiserieDto;

    /**
     * Liste les clients menuiserie ayant déjà au moins un BC ou chantier
     * dans l'instance.
     *
     * @return iterable<ClientMenuiserieDto>
     */
    public function withMenuiserieHistory(int $instanceId): iterable;

    /**
     * Verifie qu'un client natif actif peut etre reference.
     */
    public function canReference(int $instanceId, int $clientId): bool;
}
