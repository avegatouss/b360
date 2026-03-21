<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\ImportOrder;
use Modules\Eshop360\Models\ImportOrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Models\Warehouse;

class DemoImportOrdersSeeder extends Seeder
{
    public function run(?int $instanceId = null): void
    {
        $instanceId = $instanceId ?? CurrentInstance::get()?->id ?? 1;

        if (ImportOrder::withoutGlobalScopes()->where('instance_id', $instanceId)->count() >= 5) {
            return;
        }

        $suppliers = Supplier::withoutGlobalScopes()->where('instance_id', $instanceId)->get();
        $products = Product::withoutGlobalScopes()->where('instance_id', $instanceId)->where('is_active', true)->get();
        $warehouse = Warehouse::withoutGlobalScopes()->where('instance_id', $instanceId)->where('is_active', true)->first();

        if ($suppliers->isEmpty() || $products->isEmpty() || ! $warehouse) {
            return;
        }

        $shippingTypes = ['sea', 'air', 'land'];
        $statuses = ['draft', 'confirmed', 'shipped', 'customs', 'received'];
        $containers = ['MSKU1234567', 'TCLU9876543', 'HLBU5432109', 'CMAU7654321', 'MRKU3456789', null];

        for ($i = 0; $i < 10; $i++) {
            $supplier = $suppliers->random();
            $shippingType = $shippingTypes[array_rand($shippingTypes)];
            $status = $statuses[array_rand($statuses)];
            $shipDate = now()->subDays(rand(10, 90));
            $eta = $shippingType === 'air' ? $shipDate->copy()->addDays(rand(3, 7)) : ($shippingType === 'sea' ? $shipDate->copy()->addDays(rand(25, 60)) : $shipDate->copy()->addDays(rand(5, 15)));

            $importOrder = ImportOrder::create([
                'instance_id'           => $instanceId,
                'supplier_id'           => $supplier->id,
                'reference'             => 'IMP-' . now()->format('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'container_no'          => $containers[array_rand($containers)],
                'shipping_type'         => $shippingType,
                'ship_date'             => $status !== 'draft' ? $shipDate : null,
                'eta'                   => $status !== 'draft' ? $eta : null,
                'status'                => $status,
                'warehouse_id'          => $warehouse->id,
                'cost_allocation_method' => collect(['weight', 'value', 'equal'])->random(),
                'notes'                 => collect([null, 'Commande urgente', 'Lot saisonnier', 'Reapprovisionnement stock', 'Premiere commande fournisseur'])->random(),
                'created_by'            => 1,
                'created_at'            => $shipDate->copy()->subDays(rand(5, 20)),
            ]);

            $itemCount = rand(3, 8);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            foreach ($selectedProducts as $product) {
                $qty = rand(50, 500);
                $factoryPrice = max(100, (float) ($product->purchase_price_factory ?? $product->cost_price ?? $product->price * 0.4));
                $totalFactory = $qty * $factoryPrice;
                $allocatedCost = round($totalFactory * rand(5, 20) / 100, 2);

                ImportOrderItem::create([
                    'import_order_id'    => $importOrder->id,
                    'product_id'         => $product->id,
                    'quantity'           => $qty,
                    'unit_price_factory' => round($factoryPrice, 4),
                    'total_factory'      => round($totalFactory, 2),
                    'allocated_cost'     => $allocatedCost,
                    'cost_price_real'    => round(($totalFactory + $allocatedCost) / $qty, 4),
                ]);
            }
        }
    }
}
