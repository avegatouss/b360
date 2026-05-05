<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\StockMovement;
use Modules\Eshop360\Domain\Inventory\Models\Store;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;

final class DemoInventorySeeder
{
    public function run(int $instanceId): void
    {
        $warehouses = $this->seedWarehouses($instanceId);
        $this->seedStores($instanceId, $warehouses);
        $this->seedInitialStock($instanceId, $warehouses);
    }

    public function reset(int $instanceId): void
    {
        StockMovement::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        Stock::withoutGlobalScopes()->where('instance_id', $instanceId)->delete();
        Store::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->whereHas('warehouse', fn ($q) => $q->where('code', 'like', 'DEMO-%'))->delete();
        Warehouse::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->where('code', 'like', 'DEMO-%')->delete();
    }

    private function seedWarehouses(int $instanceId): array
    {
        $data = [
            ['name' => 'Entrepot Central Abidjan', 'code' => 'DEMO-WH-ABI', 'city' => 'Abidjan', 'address' => 'Zone Industrielle Vridi', 'phone' => '+225 27 21 35 00 00', 'manager_name' => 'Kouame Yao'],
            ['name' => 'Entrepot Bouake', 'code' => 'DEMO-WH-BKE', 'city' => 'Bouake', 'address' => 'Quartier Commerce', 'phone' => '+225 27 31 63 00 00', 'manager_name' => 'Diallo Mamadou'],
            ['name' => 'Depot San Pedro', 'code' => 'DEMO-WH-SPO', 'city' => 'San Pedro', 'address' => 'Zone Portuaire', 'phone' => '+225 27 34 71 00 00', 'manager_name' => 'Toure Ibrahim'],
        ];

        $result = [];
        foreach ($data as $d) {
            $result[$d['code']] = Warehouse::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $d['code']],
                array_merge($d, ['instance_id' => $instanceId, 'is_active' => true])
            );
        }

        return $result;
    }

    private function seedStores(int $instanceId, array $warehouses): void
    {
        $stores = [
            ['warehouse' => 'DEMO-WH-ABI', 'name' => 'Comptoir Vente Abidjan', 'code' => 'DEMO-ST-ABI-1'],
            ['warehouse' => 'DEMO-WH-ABI', 'name' => 'Pharmacie Grossiste Abidjan', 'code' => 'DEMO-ST-ABI-2'],
            ['warehouse' => 'DEMO-WH-BKE', 'name' => 'Comptoir Bouake', 'code' => 'DEMO-ST-BKE-1'],
        ];

        foreach ($stores as $s) {
            $wh = $warehouses[$s['warehouse']] ?? null;
            if (! $wh) {
                continue;
            }

            Store::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $s['code']],
                ['instance_id' => $instanceId, 'warehouse_id' => $wh->id, 'name' => $s['name'], 'is_active' => true]
            );
        }
    }

    private function seedInitialStock(int $instanceId, array $warehouses): void
    {
        $mainWh = $warehouses['DEMO-WH-ABI'] ?? null;
        $secondWh = $warehouses['DEMO-WH-BKE'] ?? null;
        if (! $mainWh) {
            return;
        }

        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('sku', 'like', 'PHARMA-%')
            ->get();

        foreach ($products as $i => $product) {
            $qty = rand(20, 200);

            Stock::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'product_id' => $product->id, 'warehouse_id' => $mainWh->id],
                ['quantity' => $qty, 'reserved_quantity' => 0]
            );

            StockMovement::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'product_id' => $product->id,
                'warehouse_id' => $mainWh->id,
                'type' => 'in',
                'quantity' => $qty,
                'notes' => 'Stock initial demo',
            ]);

            if ($secondWh && $i % 3 === 0) {
                $qty2 = rand(10, 50);
                Stock::withoutGlobalScopes()->updateOrCreate(
                    ['instance_id' => $instanceId, 'product_id' => $product->id, 'warehouse_id' => $secondWh->id],
                    ['quantity' => $qty2, 'reserved_quantity' => 0]
                );

                StockMovement::withoutGlobalScopes()->create([
                    'instance_id' => $instanceId,
                    'product_id' => $product->id,
                    'warehouse_id' => $secondWh->id,
                    'type' => 'in',
                    'quantity' => $qty2,
                    'notes' => 'Stock initial demo Bouake',
                ]);
            }
        }
    }
}
