<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Concerns;

use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;

/**
 * Helper de prechargement des clients natifs Menuiserie360.
 */
trait PreloadsCustomers
{
    /**
     * @param  iterable<object>  $rows  rows ayant un attribut `client_id`
     * @return array<int, ClientMenuiserie> indexe par client id
     */
    protected function preloadCustomers(iterable $rows, int $instanceId): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $clientId = $row->getAttribute('client_id') ?? null;
            if ($clientId !== null) {
                $ids[] = (int) $clientId;
            }
        }

        if ($ids === []) {
            return [];
        }

        return ClientMenuiserie::query()
            ->where('instance_id', $instanceId)
            ->whereIn('id', array_values(array_unique($ids)))
            ->get()
            ->keyBy(fn (ClientMenuiserie $client): int => (int) $client->getKey())
            ->all();
    }
}
