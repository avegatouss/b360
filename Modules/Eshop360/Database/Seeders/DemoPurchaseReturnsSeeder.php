<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Product;

class DemoPurchaseReturnsSeeder extends Seeder
{
    public function run(?int $instanceId = null): void
    {
        $instanceId = $instanceId ?? CurrentInstance::get()?->id ?? 1;

        if (DB::table('eshop_purchase_returns')->where('instance_id', $instanceId)->exists()) {
            return;
        }

        $purchaseOrders = PurchaseOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('status', 'received')
            ->with('items')
            ->get();

        if ($purchaseOrders->isEmpty()) {
            // Fallback: use any PO with items
            $purchaseOrders = PurchaseOrder::withoutGlobalScopes()
                ->where('instance_id', $instanceId)
                ->with('items')
                ->limit(10)
                ->get();
        }

        if ($purchaseOrders->isEmpty()) {
            return;
        }

        $reasons = [
            'Produit defectueux a la reception',
            'Quantite en surplus non commandee',
            'Mauvais produit livre',
            'Produit perime a la reception',
            'Emballage endommage',
            'Non conforme aux specifications',
            'Erreur de commande interne',
        ];

        $statuses = ['pending', 'received', 'received', 'pending', 'cancelled'];
        $warehouse = \Modules\Eshop360\Models\Warehouse::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->first();

        $count = 0;
        foreach ($purchaseOrders->take(8) as $po) {
            if ($po->items->isEmpty()) {
                continue;
            }

            $status = $statuses[$count % count($statuses)];
            $returnItems = $po->items->random(min(rand(1, 3), $po->items->count()));
            $total = 0;
            $itemsData = [];

            foreach ($returnItems as $item) {
                $returnQty = max(1, (int) round($item->quantity * rand(10, 50) / 100));
                $unitCost = (float) ($item->unit_cost ?? $item->unit_price ?? 0);
                $lineTotal = $returnQty * $unitCost;
                $total += $lineTotal;

                $itemsData[] = [
                    'product_id' => $item->product_id,
                    'quantity'   => $returnQty,
                    'unit_cost'  => $unitCost,
                    'total'      => $lineTotal,
                ];
            }

            $isPaid = $status === 'received' && rand(0, 10) > 3;
            $paidAmount = $isPaid ? $total : 0;

            $returnId = DB::table('eshop_purchase_returns')->insertGetId([
                'instance_id'       => $instanceId,
                'purchase_order_id' => $po->id,
                'supplier_name'     => $po->supplier_name ?? $po->supplier?->name ?? 'Fournisseur',
                'reference'         => 'PRET-' . now()->format('Y') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
                'warehouse_id'      => $po->warehouse_id ?? $warehouse?->id,
                'status'            => $status,
                'total'             => $total,
                'paid_amount'       => $paidAmount,
                'due_amount'        => max(0, $total - $paidAmount),
                'payment_status'    => $isPaid ? 'paid' : 'unpaid',
                'notes'             => $reasons[array_rand($reasons)],
                'created_by'        => 1,
                'processed_at'      => $status === 'received' ? now()->subDays(rand(1, 15)) : null,
                'created_at'        => now()->subDays(rand(5, 45)),
                'updated_at'        => now(),
            ]);

            foreach ($itemsData as $itemData) {
                DB::table('eshop_purchase_return_items')->insert([
                    'purchase_return_id' => $returnId,
                    'product_id'         => $itemData['product_id'],
                    'quantity'           => $itemData['quantity'],
                    'unit_cost'          => $itemData['unit_cost'],
                    'total'              => $itemData['total'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            $count++;
        }
    }
}
