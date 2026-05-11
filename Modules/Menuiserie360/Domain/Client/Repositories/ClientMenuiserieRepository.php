<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Repositories;

use Modules\Eshop360\Contracts\Customer\CustomerDto;
use Modules\Eshop360\Contracts\Customer\CustomerReader;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientMenuiserieDto;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;

/**
 * P1-5 — Repository client menuiserie (ACL applicatif).
 *
 * Combine :
 *   - identité Eshop360 lue via {@see CustomerReader} (contrat ADR-021)
 *   - attributs propres menuiserie via {@see ClientMenuiserie} (table
 *     `mnu_clients_menuiserie`)
 *
 * Pas de FK SQL vers `eshop_customers` (cf. migration P1-4) — la cohérence
 * est validée applicativement via `CustomerReader::customerExists()`.
 *
 * Bind comme {@see ClientRepositoryContract} dans Menuiserie360ServiceProvider.
 */
final class ClientMenuiserieRepository implements ClientRepositoryContract
{
    public function __construct(
        private readonly CustomerReader $customerReader,
    ) {}

    public function find(int $instanceId, int $customerId): ?ClientMenuiserieDto
    {
        $customer = $this->customerReader->findCustomer($instanceId, $customerId);
        if ($customer === null) {
            return null;
        }

        $extension = ClientMenuiserie::query()
            ->where('instance_id', $instanceId)
            ->where('customer_id', $customerId)
            ->first();

        return $this->fromCustomerDto(
            $customer,
            $extension !== null ? $this->extensionToArray($extension) : null,
        );
    }

    public function withMenuiserieHistory(int $instanceId): iterable
    {
        $extensions = ClientMenuiserie::query()
            ->where('instance_id', $instanceId)
            ->where('total_chantiers_count', '>', 0)
            ->get();

        foreach ($extensions as $extension) {
            $customerId = (int) $extension->getAttribute('customer_id');
            $customer = $this->customerReader->findCustomer($instanceId, $customerId);
            if ($customer === null) {
                // Customer Eshop360 supprimé entretemps — on saute (mais
                // cela révèle un orphelin à nettoyer dans un lot dédié).
                continue;
            }

            yield $this->fromCustomerDto($customer, $this->extensionToArray($extension));
        }
    }

    public function canReference(int $instanceId, int $customerId): bool
    {
        return $this->customerReader->customerExists($instanceId, $customerId);
    }

    public function fromCustomerDto(CustomerDto $customer, ?array $menuiserieAttributes = null): ClientMenuiserieDto
    {
        return new ClientMenuiserieDto(
            id: $customer->id,
            instanceId: $customer->instanceId,
            code: $customer->code,
            name: $customer->name,
            email: $customer->email,
            phone: $customer->phone,
            address: $customer->address,
            city: $customer->city,
            isActive: $customer->isActive,
            preferredContactMethod: isset($menuiserieAttributes['preferred_contact_method'])
                ? (string) $menuiserieAttributes['preferred_contact_method']
                : null,
            totalChantiersCount: isset($menuiserieAttributes['total_chantiers_count'])
                ? (int) $menuiserieAttributes['total_chantiers_count']
                : 0,
            totalRevenueXof: isset($menuiserieAttributes['total_revenue_xof'])
                ? (float) $menuiserieAttributes['total_revenue_xof']
                : 0.0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function extensionToArray(ClientMenuiserie $extension): array
    {
        return [
            'preferred_contact_method' => $extension->getAttribute('preferred_contact_method'),
            'total_chantiers_count' => $extension->getAttribute('total_chantiers_count'),
            'total_revenue_xof' => $extension->getAttribute('total_revenue_xof'),
        ];
    }
}
