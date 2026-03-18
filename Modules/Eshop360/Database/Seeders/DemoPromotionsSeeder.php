<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Support\Str;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Discount;

final class DemoPromotionsSeeder
{
    public function run(int $instanceId): void
    {
        $this->seedCoupons($instanceId);
        $this->seedDiscounts($instanceId);
    }

    public function reset(int $instanceId): void
    {
        Coupon::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('code', 'like', 'DEMO%')->delete();
        Discount::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('name', 'like', '[DEMO]%')->delete();
    }

    private function seedCoupons(int $instanceId): void
    {
        $coupons = [
            ['name' => 'Bienvenue 10%', 'code' => 'DEMO-WELCOME10', 'type' => 'percentage', 'value' => 10, 'usage_limit' => 100, 'description' => 'Remise de bienvenue pour nouveaux clients'],
            ['name' => 'Remise 5000 XOF', 'code' => 'DEMO-5000OFF', 'type' => 'fixed', 'value' => 5000, 'usage_limit' => 50, 'description' => 'Remise fixe 5000 XOF sur toute commande'],
            ['name' => 'Promo Mars 15%', 'code' => 'DEMO-MARS15', 'type' => 'percentage', 'value' => 15, 'usage_limit' => 30, 'description' => 'Promotion speciale mois de mars'],
            ['name' => 'Grossiste VIP 20%', 'code' => 'DEMO-VIP20', 'type' => 'percentage', 'value' => 20, 'usage_limit' => 10, 'description' => 'Remise exclusive grossistes VIP'],
            ['name' => 'Soldes fin de lot', 'code' => 'DEMO-SOLDES25', 'type' => 'percentage', 'value' => 25, 'usage_limit' => 20, 'description' => 'Liquidation fin de lot'],
        ];

        foreach ($coupons as $c) {
            Coupon::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $c['code']],
                array_merge($c, [
                    'instance_id' => $instanceId,
                    'used_count' => 0,
                    'valid_from' => now()->startOfMonth(),
                    'valid_until' => now()->endOfMonth()->addMonths(2),
                    'is_active' => true,
                ])
            );
        }
    }

    private function seedDiscounts(int $instanceId): void
    {
        $discounts = [
            ['name' => '[DEMO] Remise grossiste 10%', 'type' => 'percentage', 'value' => 10, 'plan_type' => 'standard', 'applies_to' => 'all'],
            ['name' => '[DEMO] Remise lot antibiotiques', 'type' => 'percentage', 'value' => 8, 'plan_type' => 'standard', 'applies_to' => 'specific'],
            ['name' => '[DEMO] Promo paracetamol 500 XOF', 'type' => 'fixed', 'value' => 500, 'plan_type' => 'standard', 'applies_to' => 'specific'],
        ];

        foreach ($discounts as $d) {
            Discount::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'name' => $d['name']],
                array_merge($d, [
                    'instance_id' => $instanceId,
                    'valid_from' => now()->startOfMonth(),
                    'valid_until' => now()->endOfMonth()->addMonths(3),
                    'is_active' => true,
                ])
            );
        }
    }
}
