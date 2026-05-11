<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Concerns;

use Modules\Eshop360\Contracts\Customer\CustomerDto;
use Modules\Eshop360\Contracts\Customer\CustomerReader;

/**
 * M-UI-5 — Helper de préchargement Customer Eshop360 pour les listes
 * Menuiserie360 (Devis, BC, Chantier, Facture).
 *
 * Évite le N+1 sur la table externe `eshop_customers` : un seul appel
 * `CustomerReader::findCustomersByIds()` par page, suivi d'une lookup
 * synchrone côté vue via la map renvoyée.
 *
 * Si Eshop360 est désactivé (R-403 — binding CustomerReader absent),
 * renvoie un tableau vide. La vue affiche alors le fallback `#<id>`.
 */
trait PreloadsCustomers
{
    /**
     * @param  iterable<object>  $rows  rows ayant un attribut `client_id`
     * @return array<int, CustomerDto> indexé par customer id
     */
    protected function preloadCustomers(iterable $rows, int $instanceId): array
    {
        if (! app()->bound(CustomerReader::class)) {
            return [];
        }

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

        return app(CustomerReader::class)->findCustomersByIds($instanceId, $ids);
    }
}
