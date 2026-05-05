<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Purchasing\Models\Supplier;

final class DemoSuppliersSeeder
{
    public function run(int $instanceId): void
    {
        $suppliers = [
            ['name' => 'Sanofi Aventis', 'company' => 'Sanofi SA', 'country' => 'France', 'contact_person' => 'Jean Dupont', 'email' => 'export@sanofi.com', 'phone' => '+33 1 53 77 40 00', 'address' => '54 Rue La Boetie, Paris', 'balance' => 0],
            ['name' => 'Cipla Limited', 'company' => 'Cipla Ltd', 'country' => 'Inde', 'contact_person' => 'Rajesh Patel', 'email' => 'exports@cipla.com', 'phone' => '+91 22 2437 6000', 'address' => 'Mumbai, Maharashtra', 'balance' => 0],
            ['name' => 'Denk Pharma GmbH', 'company' => 'Denk Pharma', 'country' => 'Allemagne', 'contact_person' => 'Hans Mueller', 'email' => 'africa@denkpharma.de', 'phone' => '+49 89 829 26 0', 'address' => 'Munich, Germany', 'balance' => 0],
            ['name' => 'CIPHARM', 'company' => 'CIPHARM SA', 'country' => 'Cote d\'Ivoire', 'contact_person' => 'Koffi Assi', 'email' => 'commercial@cipharm.ci', 'phone' => '+225 27 21 75 18 50', 'address' => 'Zone Industrielle Vridi, Abidjan', 'balance' => 0],
            ['name' => 'Pharmivoire Nouvelle', 'company' => 'Pharmivoire SA', 'country' => 'Cote d\'Ivoire', 'contact_person' => 'Traore Mariam', 'email' => 'commande@pharmivoire.ci', 'phone' => '+225 27 21 25 85 85', 'address' => 'Abidjan, Treichville', 'balance' => 0],
            ['name' => 'Sun Pharmaceutical', 'company' => 'Sun Pharma', 'country' => 'Inde', 'contact_person' => 'Vikram Singh', 'email' => 'africa@sunpharma.com', 'phone' => '+91 22 4324 4324', 'address' => 'Mumbai, India', 'balance' => 0],
            ['name' => 'Maphar Casablanca', 'company' => 'Maphar SA', 'country' => 'Maroc', 'contact_person' => 'Ahmed Bennani', 'email' => 'export@maphar.ma', 'phone' => '+212 522 67 43 00', 'address' => 'Casablanca, Maroc', 'balance' => 0],
            ['name' => 'Aurobindo Pharma', 'company' => 'Aurobindo Ltd', 'country' => 'Inde', 'contact_person' => 'Priya Sharma', 'email' => 'intl@aurobindo.com', 'phone' => '+91 40 6672 5000', 'address' => 'Hyderabad, India', 'balance' => 0],
        ];

        foreach ($suppliers as $s) {
            Supplier::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'email' => $s['email']],
                array_merge($s, ['instance_id' => $instanceId, 'is_active' => true])
            );
        }
    }

    public function reset(int $instanceId): void
    {
        Supplier::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
    }
}
