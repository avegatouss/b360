<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\CRM\Models\CustomerGroup;

final class DemoCustomersSeeder
{
    public function run(int $instanceId): void
    {
        $groups = $this->seedGroups($instanceId);
        $this->seedCustomers($instanceId, $groups);
    }

    public function reset(int $instanceId): void
    {
        Customer::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('code', 'like', 'DEMO-%')->delete();
        CustomerGroup::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')->delete();
    }

    private function seedGroups(int $instanceId): array
    {
        $data = [
            ['name' => '[DEMO] Grossistes VIP', 'discount_rate' => 15],
            ['name' => '[DEMO] Grossistes Standard', 'discount_rate' => 10],
            ['name' => '[DEMO] Pharmacies', 'discount_rate' => 8],
            ['name' => '[DEMO] Detaillants', 'discount_rate' => 5],
            ['name' => '[DEMO] ONG / Institutions', 'discount_rate' => 12],
        ];

        $result = [];
        foreach ($data as $d) {
            $result[$d['name']] = CustomerGroup::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $d['name']],
                array_merge($d, ['instance_id' => $instanceId])
            );
        }

        return $result;
    }

    private function seedCustomers(int $instanceId, array $groups): void
    {
        $customers = [
            ['code' => 'DEMO-CL-001', 'name' => 'Pharmacie Centrale Abidjan', 'email' => 'contact@pca.ci', 'phone' => '+225 07 01 01 01', 'city' => 'Abidjan', 'country' => 'CI', 'group' => '[DEMO] Grossistes VIP', 'company_name' => 'PCA SARL', 'credit_limit' => 5000000],
            ['code' => 'DEMO-CL-002', 'name' => 'Pharmacie du Plateau', 'email' => 'plateau@pharma.ci', 'phone' => '+225 07 02 02 02', 'city' => 'Abidjan', 'country' => 'CI', 'group' => '[DEMO] Pharmacies', 'company_name' => 'Pharma Plateau', 'credit_limit' => 2000000],
            ['code' => 'DEMO-CL-003', 'name' => 'Grossiste UBUFARM', 'email' => 'info@ubufarm.sn', 'phone' => '+221 77 100 2000', 'city' => 'Dakar', 'country' => 'SN', 'group' => '[DEMO] Grossistes VIP', 'company_name' => 'UBUFARM SA', 'credit_limit' => 10000000],
            ['code' => 'DEMO-CL-004', 'name' => 'PSP Pharmacie', 'email' => 'psp@pharma.ci', 'phone' => '+225 07 04 04 04', 'city' => 'Bouake', 'country' => 'CI', 'group' => '[DEMO] Grossistes Standard', 'company_name' => 'PSP CI', 'credit_limit' => 3000000],
            ['code' => 'DEMO-CL-005', 'name' => 'Revendeur Principal', 'email' => 'revendeur@saphir.ci', 'phone' => '+225 07 05 05 05', 'city' => 'Abidjan', 'country' => 'CI', 'group' => '[DEMO] Grossistes VIP', 'company_name' => 'Revendeur Principal', 'credit_limit' => 15000000],
            ['code' => 'DEMO-CL-006', 'name' => 'Pharmacie de la Gare', 'email' => 'gare@pharma.ci', 'phone' => '+225 07 06 06 06', 'city' => 'Bouake', 'country' => 'CI', 'group' => '[DEMO] Pharmacies', 'credit_limit' => 1000000],
            ['code' => 'DEMO-CL-007', 'name' => 'Pharmacie Adjoua', 'email' => 'adjoua@pharma.ci', 'phone' => '+225 07 07 07 07', 'city' => 'Yamoussoukro', 'country' => 'CI', 'group' => '[DEMO] Pharmacies', 'credit_limit' => 800000],
            ['code' => 'DEMO-CL-008', 'name' => 'ONG Sante Pour Tous', 'email' => 'spt@ong.org', 'phone' => '+225 07 08 08 08', 'city' => 'Abidjan', 'country' => 'CI', 'group' => '[DEMO] ONG / Institutions', 'company_name' => 'SPT ONG', 'credit_limit' => 2000000],
            ['code' => 'DEMO-CL-009', 'name' => 'Depot Pharma San Pedro', 'email' => 'depot@sanpedro.ci', 'phone' => '+225 07 09 09 09', 'city' => 'San Pedro', 'country' => 'CI', 'group' => '[DEMO] Detaillants', 'credit_limit' => 500000],
            ['code' => 'DEMO-CL-010', 'name' => 'Centre Hospitalier Regional', 'email' => 'chr@sante.gouv.ci', 'phone' => '+225 07 10 10 10', 'city' => 'Korhogo', 'country' => 'CI', 'group' => '[DEMO] ONG / Institutions', 'company_name' => 'CHR Korhogo', 'credit_limit' => 3000000],
            ['code' => 'DEMO-CL-011', 'name' => 'Pharmacie du Marche', 'email' => 'marche@pharma.ci', 'phone' => '+225 07 11 11 11', 'city' => 'Man', 'country' => 'CI', 'group' => '[DEMO] Detaillants', 'credit_limit' => 400000],
            ['code' => 'DEMO-CL-012', 'name' => 'Grossiste PharmaPlus Bamako', 'email' => 'info@pharmaplus.ml', 'phone' => '+223 70 20 30 40', 'city' => 'Bamako', 'country' => 'ML', 'group' => '[DEMO] Grossistes Standard', 'company_name' => 'PharmaPlus Mali', 'credit_limit' => 5000000],
            ['code' => 'DEMO-CL-013', 'name' => 'Pharmacie Ndiaye', 'email' => 'ndiaye@pharma.sn', 'phone' => '+221 77 300 4000', 'city' => 'Dakar', 'country' => 'SN', 'group' => '[DEMO] Pharmacies', 'credit_limit' => 1500000],
            ['code' => 'DEMO-CL-014', 'name' => 'Pharmacie de l\'Aeroport', 'email' => 'aeroport@pharma.ci', 'phone' => '+225 07 14 14 14', 'city' => 'Abidjan', 'country' => 'CI', 'group' => '[DEMO] Pharmacies', 'credit_limit' => 1200000],
            ['code' => 'DEMO-CL-015', 'name' => 'Depot Medical Daloa', 'email' => 'daloa@med.ci', 'phone' => '+225 07 15 15 15', 'city' => 'Daloa', 'country' => 'CI', 'group' => '[DEMO] Detaillants', 'credit_limit' => 600000],
        ];

        foreach ($customers as $c) {
            $groupModel = $groups[$c['group']] ?? null;
            unset($c['group']);

            Customer::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $c['code']],
                array_merge($c, [
                    'instance_id' => $instanceId,
                    'group_id' => $groupModel?->id,
                    'wallet_balance' => 0,
                    'is_active' => true,
                ])
            );
        }
    }
}
