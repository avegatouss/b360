<?php

declare(strict_types=1);

namespace Modules\Eshop360\Adapters\Eloquent;

use Modules\Eshop360\Contracts\Customer\CustomerDto;
use Modules\Eshop360\Contracts\Customer\CustomerReader;
use Modules\Eshop360\Domain\CRM\Models\Customer;

/**
 * Implémentation par défaut du {@see CustomerReader} via Eloquent.
 *
 * Bind par défaut dans {@see \Modules\Eshop360\Providers\Eshop360ServiceProvider::register()}.
 *
 * Note ADR-021 §1 : c'est l'unique endroit où le modèle Eloquent
 * `Modules\Eshop360\Domain\CRM\Models\Customer` est importé au-delà
 * du périmètre Eshop360. Tout consommateur externe (L4) passe par le
 * contrat, jamais par le modèle.
 */
final class EloquentCustomerReader implements CustomerReader
{
    public function findCustomer(int $instanceId, int $customerId): ?CustomerDto
    {
        $customer = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('id', $customerId)
            ->first();

        return $customer ? $this->mapToDto($customer) : null;
    }

    public function findCustomerByCode(int $instanceId, string $code): ?CustomerDto
    {
        $customer = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('code', $code)
            ->first();

        return $customer ? $this->mapToDto($customer) : null;
    }

    public function customerExists(int $instanceId, int $customerId): bool
    {
        return Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('id', $customerId)
            ->exists();
    }

    private function mapToDto(Customer $c): CustomerDto
    {
        $channelId = $c->getAttribute('channel_id');
        $userId = $c->getAttribute('user_id');
        $groupId = $c->getAttribute('group_id');
        $email = $c->getAttribute('email');
        $phone = $c->getAttribute('phone');
        $address = $c->getAttribute('address');
        $city = $c->getAttribute('city');
        $country = $c->getAttribute('country');
        $companyName = $c->getAttribute('company_name');
        $taxNumber = $c->getAttribute('tax_number');

        return new CustomerDto(
            id: (int) $c->getAttribute('id'),
            instanceId: (int) $c->getAttribute('instance_id'),
            channelId: $channelId !== null ? (int) $channelId : null,
            userId: $userId !== null ? (int) $userId : null,
            groupId: $groupId !== null ? (int) $groupId : null,
            code: (string) $c->getAttribute('code'),
            name: (string) $c->getAttribute('name'),
            email: $email !== null ? (string) $email : null,
            phone: $phone !== null ? (string) $phone : null,
            address: $address !== null ? (string) $address : null,
            city: $city !== null ? (string) $city : null,
            country: $country !== null ? (string) $country : null,
            companyName: $companyName !== null ? (string) $companyName : null,
            taxNumber: $taxNumber !== null ? (string) $taxNumber : null,
            walletBalance: (float) $c->getAttribute('wallet_balance'),
            creditLimit: (float) $c->getAttribute('credit_limit'),
            isActive: (bool) $c->getAttribute('is_active'),
        );
    }
}
