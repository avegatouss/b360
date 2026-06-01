<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mnu_clients_menuiserie') || ! Schema::hasTable('eshop_customers')) {
            return;
        }

        $customerColumns = collect(Schema::getColumnListing('eshop_customers'));

        DB::table('mnu_clients_menuiserie')
            ->whereNotNull('legacy_eshop_customer_id')
            ->orderBy('id')
            ->each(function (object $client) use ($customerColumns): void {
                $customer = DB::table('eshop_customers')
                    ->where('id', $client->legacy_eshop_customer_id)
                    ->where('instance_id', $client->instance_id)
                    ->first();

                if ($customer === null) {
                    return;
                }

                $name = $this->value($customer, $customerColumns, 'name') ?? 'Client '.$client->id;
                $companyName = $this->value($customer, $customerColumns, 'company_name');

                DB::table('mnu_clients_menuiserie')
                    ->where('id', $client->id)
                    ->update([
                        'code' => $this->value($customer, $customerColumns, 'code') ?? sprintf('CLI-%s-%04d', now()->format('Y'), (int) $client->id),
                        'type' => $companyName !== null ? 'entreprise' : 'particulier',
                        'nom' => $companyName === null ? $name : null,
                        'raison_sociale' => $companyName ?? $name,
                        'email' => $this->value($customer, $customerColumns, 'email'),
                        'telephone_principal' => $this->value($customer, $customerColumns, 'phone'),
                        'adresse' => $this->value($customer, $customerColumns, 'address'),
                        'ville' => $this->value($customer, $customerColumns, 'city'),
                        'pays' => $this->value($customer, $customerColumns, 'country') ?? 'CI',
                        'nif' => $this->value($customer, $customerColumns, 'tax_number'),
                        'is_active' => (bool) ($this->value($customer, $customerColumns, 'is_active') ?? true),
                    ]);
            });
    }

    public function down(): void
    {
        // Migration de donnees non reversible sans perte : no-op.
    }

    private function value(object $row, \Illuminate\Support\Collection $columns, string $column): mixed
    {
        if (! $columns->contains($column)) {
            return null;
        }

        return $row->{$column} ?? null;
    }
};
