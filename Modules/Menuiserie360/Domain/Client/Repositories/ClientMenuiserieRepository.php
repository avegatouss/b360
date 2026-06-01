<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Repositories;

use Modules\Menuiserie360\Domain\Client\Contracts\ClientMenuiserieDto;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Enums\StatutClientMenuiserie;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;

/**
 * Repository client menuiserie autonome (ADR-023).
 */
final class ClientMenuiserieRepository implements ClientRepositoryContract
{
    public function find(int $instanceId, int $clientId): ?ClientMenuiserieDto
    {
        $client = ClientMenuiserie::query()
            ->where('instance_id', $instanceId)
            ->whereKey($clientId)
            ->first();

        return $client !== null ? $this->toDto($client) : null;
    }

    public function withMenuiserieHistory(int $instanceId): iterable
    {
        $clients = ClientMenuiserie::query()
            ->where('instance_id', $instanceId)
            ->where('total_chantiers_count', '>', 0)
            ->orderByDesc('total_chantiers_count')
            ->get();

        foreach ($clients as $client) {
            yield $this->toDto($client);
        }
    }

    public function canReference(int $instanceId, int $clientId): bool
    {
        return ClientMenuiserie::query()
            ->where('instance_id', $instanceId)
            ->whereKey($clientId)
            ->where('is_active', true)
            ->exists();
    }

    public function toDto(ClientMenuiserie $client): ClientMenuiserieDto
    {
        $statut = $client->getAttribute('statut');

        return new ClientMenuiserieDto(
            id: (int) $client->getKey(),
            instanceId: (int) $client->getAttribute('instance_id'),
            code: (string) $client->getAttribute('code'),
            name: $client->name,
            type: (string) $client->getAttribute('type'),
            nom: $this->nullableString($client->getAttribute('nom')),
            prenom: $this->nullableString($client->getAttribute('prenom')),
            raisonSociale: $this->nullableString($client->getAttribute('raison_sociale')),
            email: $this->nullableString($client->getAttribute('email')),
            phone: $this->nullableString($client->getAttribute('telephone_principal')),
            secondaryPhone: $this->nullableString($client->getAttribute('telephone_secondaire')),
            address: $this->nullableString($client->getAttribute('adresse')),
            city: $this->nullableString($client->getAttribute('ville')),
            country: (string) ($client->getAttribute('pays') ?? 'CI'),
            rccm: $this->nullableString($client->getAttribute('rccm')),
            nif: $this->nullableString($client->getAttribute('nif')),
            statut: $statut instanceof StatutClientMenuiserie ? $statut->value : (string) $statut,
            isActive: (bool) $client->getAttribute('is_active'),
            legacyEshopCustomerId: $client->getAttribute('legacy_eshop_customer_id') !== null
                ? (int) $client->getAttribute('legacy_eshop_customer_id')
                : null,
            preferredContactMethod: $this->nullableString($client->getAttribute('preferred_contact_method')),
            totalChantiersCount: (int) $client->getAttribute('total_chantiers_count'),
            totalRevenueXof: (float) $client->getAttribute('total_revenue_xof'),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
