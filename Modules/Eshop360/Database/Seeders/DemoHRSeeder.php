<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Models\Employee;

final class DemoHRSeeder
{
    public function run(int $instanceId): void
    {
        $employees = [
            ['name' => 'Kouame Yao', 'email' => 'kouame@saphir.ci', 'phone' => '+225 07 50 01 01', 'position' => 'Directeur entrepot', 'department' => 'Logistique', 'salary' => 800000, 'commission_rate' => 0],
            ['name' => 'Toure Mariam', 'email' => 'mariam@saphir.ci', 'phone' => '+225 07 50 02 02', 'position' => 'Responsable ventes', 'department' => 'Commercial', 'salary' => 600000, 'commission_rate' => 2.5],
            ['name' => 'Diallo Mamadou', 'email' => 'diallo@saphir.ci', 'phone' => '+225 07 50 03 03', 'position' => 'Gerant depot Bouake', 'department' => 'Logistique', 'salary' => 500000, 'commission_rate' => 1.0],
            ['name' => 'Kone Aissata', 'email' => 'kone@saphir.ci', 'phone' => '+225 07 50 04 04', 'position' => 'Comptable', 'department' => 'Finance', 'salary' => 550000, 'commission_rate' => 0],
            ['name' => 'Bamba Seydou', 'email' => 'bamba@saphir.ci', 'phone' => '+225 07 50 05 05', 'position' => 'Agent commercial', 'department' => 'Commercial', 'salary' => 350000, 'commission_rate' => 3.0],
            ['name' => 'Ouattara Fatou', 'email' => 'ouattara@saphir.ci', 'phone' => '+225 07 50 06 06', 'position' => 'Caissiere POS', 'department' => 'Ventes', 'salary' => 250000, 'commission_rate' => 1.5],
            ['name' => 'Coulibaly Ibrahim', 'email' => 'coulibaly@saphir.ci', 'phone' => '+225 07 50 07 07', 'position' => 'Preparateur commandes', 'department' => 'Logistique', 'salary' => 220000, 'commission_rate' => 0],
            ['name' => 'Traore Aminata', 'email' => 'traore@saphir.ci', 'phone' => '+225 07 50 08 08', 'position' => 'Assistante administrative', 'department' => 'Administration', 'salary' => 280000, 'commission_rate' => 0],
            ['name' => 'Gnamba Koffi', 'email' => 'gnamba@saphir.ci', 'phone' => '+225 07 50 09 09', 'position' => 'Chauffeur livreur', 'department' => 'Logistique', 'salary' => 200000, 'commission_rate' => 0],
            ['name' => 'Aka Brigitte', 'email' => 'aka@saphir.ci', 'phone' => '+225 07 50 10 10', 'position' => 'Agent de saisie', 'department' => 'Administration', 'salary' => 180000, 'commission_rate' => 0],
        ];

        foreach ($employees as $e) {
            Employee::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'email' => $e['email']],
                array_merge($e, [
                    'instance_id' => $instanceId,
                    'status' => 'active',
                    'joined_at' => now()->subMonths(rand(3, 24)),
                ])
            );
        }
    }

    public function reset(int $instanceId): void
    {
        Employee::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
    }
}
