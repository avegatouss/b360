<?php

namespace Modules\Eshop360\Database\Seeders;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Purchasing\Models\PurchaseItem;
use Modules\Eshop360\Domain\Purchasing\Models\PurchaseOrder;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;

final class DemoSupplierPurchasesSeeder
{
    public function run(int $instanceId): void
    {
        $suppliers = Supplier::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->get();

        if ($suppliers->isEmpty()) {
            return;
        }

        $products = Product::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->limit(30)
            ->get();

        if ($products->count() < 5) {
            return;
        }

        // Link products to suppliers (distribute evenly)
        $supplierIds = $suppliers->pluck('id')->toArray();
        foreach ($products as $i => $product) {
            $supplierId = $supplierIds[$i % count($supplierIds)];
            if (! $product->supplier_id) {
                $product->update(['supplier_id' => $supplierId]);
            }
        }

        foreach ($suppliers as $supplier) {
            $this->seedPurchaseOrders($instanceId, $supplier, $products);
        }
    }

    public function reset(int $instanceId): void
    {
        $orderIds = PurchaseOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('reference', 'like', 'DEMO-PO-%')
            ->pluck('id');
        PurchaseItem::whereIn('purchase_order_id', $orderIds)->delete();
        PurchaseOrder::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('reference', 'like', 'DEMO-PO-%')
            ->forceDelete();
    }

    private function seedPurchaseOrders(int $instanceId, Supplier $supplier, $products): void
    {
        $supplierProducts = $products->where('supplier_id', $supplier->id);
        if ($supplierProducts->isEmpty()) {
            $supplierProducts = $products->random(min(4, $products->count()));
        }

        // 2-4 orders per supplier over 3 months
        $orderCount = rand(2, 4);
        for ($i = 0; $i < $orderCount; $i++) {
            $date = now()->subDays(rand(1, 90))->setHour(rand(8, 17));
            $statuses = ['received', 'received', 'received', 'ordered', 'pending'];
            $status = $statuses[array_rand($statuses)];

            $itemCount = rand(2, 5);
            $selectedProducts = $supplierProducts->random(min($itemCount, $supplierProducts->count()));

            $orderTotal = 0;
            $itemsData = [];
            foreach ($selectedProducts as $product) {
                $qty = rand(10, 100);
                $costPrice = (float) ($product->cost_price ?: $product->price * 0.6);
                $unitCost = round($costPrice * (1 + rand(-5, 10) / 100), 2);
                $total = round($unitCost * $qty, 2);
                $orderTotal += $total;
                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'received_qty' => $status === 'received' ? $qty : 0,
                    'unit_cost' => $unitCost,
                    'total' => $total,
                ];
            }

            $total = round($orderTotal, 2);
            $isPaid = $status === 'received' && rand(1, 10) > 3;
            $paidAmount = $isPaid ? $total : ($status === 'received' ? round($total * rand(30, 70) / 100, 2) : 0);
            $dueAmount = round($total - $paidAmount, 2);
            $paymentStatus = $dueAmount <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

            $ref = 'DEMO-PO-S'.$supplier->id.'-'.$date->format('ymdHi').'-'.$i;

            $order = PurchaseOrder::withoutGlobalScopes()->create([
                'instance_id' => $instanceId,
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'supplier_email' => $supplier->email,
                'reference' => $ref,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'notes' => '[DEMO] Commande fournisseur '.$supplier->name,
                'created_by' => 1,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($itemsData as $item) {
                PurchaseItem::create(array_merge($item, ['purchase_order_id' => $order->id]));
            }
        }
    }
}
