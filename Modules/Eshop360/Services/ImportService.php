<?php

namespace Modules\Eshop360\Services;

use Modules\Eshop360\Models\ImportOrder;
use Modules\Eshop360\Models\ImportOrderItem;
use Modules\Eshop360\Models\ImportCost;
use Modules\Eshop360\Models\Product;
use Illuminate\Support\Facades\DB;

class ImportService
{
    /**
     * Create a new import order with items
     */
    public function createImportOrder(array $data, array $items): ImportOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $order = ImportOrder::create($data);
            foreach ($items as $item) {
                $item['import_order_id'] = $order->id;
                $item['total_factory'] = $item['quantity'] * $item['unit_price_factory'];
                ImportOrderItem::create($item);
            }
            return $order->load('items', 'supplier');
        });
    }

    /**
     * Add costs to an import order
     */
    public function addCost(ImportOrder $order, array $costData): ImportCost
    {
        return $order->costs()->create($costData);
    }

    /**
     * Allocate import costs to products using the order's configured method
     * Method 'value': proportional to factory value
     * Method 'quantity': proportional to quantities
     */
    public function allocateCosts(ImportOrder $order): void
    {
        $items = $order->items;
        $totalCosts = $order->costs()->sum('amount');

        if ($items->isEmpty() || $totalCosts <= 0) return;

        if ($order->cost_allocation_method === 'value') {
            $totalFactory = $items->sum('total_factory');
            if ($totalFactory <= 0) return;
            foreach ($items as $item) {
                $coefficient = $item->total_factory / $totalFactory;
                $allocatedCost = $totalCosts * $coefficient;
                $costPriceReal = $item->unit_price_factory + ($allocatedCost / $item->quantity);
                $item->update([
                    'allocated_cost' => $allocatedCost,
                    'cost_price_real' => $costPriceReal,
                ]);
            }
        } else { // quantity method
            $totalQuantity = $items->sum('quantity');
            if ($totalQuantity <= 0) return;
            foreach ($items as $item) {
                $coefficient = $item->quantity / $totalQuantity;
                $allocatedCost = $totalCosts * $coefficient;
                $costPriceReal = $item->unit_price_factory + ($allocatedCost / $item->quantity);
                $item->update([
                    'allocated_cost' => $allocatedCost,
                    'cost_price_real' => $costPriceReal,
                ]);
            }
        }
    }

    /**
     * Receive import order - update product cost prices and create stock entries
     */
    public function receiveImport(ImportOrder $order, StockService $stockService): void
    {
        DB::transaction(function () use ($order, $stockService) {
            if ($order->status === 'received') {
                return;
            }

            // Allocate costs first
            $this->allocateCosts($order);

            // Update each product's cost_price_real and create stock entries
            foreach ($order->items as $item) {
                // Update product pricing
                $item->product->update([
                    'cost_price_real' => $item->cost_price_real,
                    'purchase_price_factory' => $item->unit_price_factory,
                ]);

                // Add stock
                $stockService->adjustStock(
                    $item->product,
                    $order->warehouse_id,
                    $item->quantity,
                    'in',
                    "Import #{$order->reference}",
                    $order->created_by,
                    ImportOrder::class,
                    $order->id,
                );
            }

            $order->update(['status' => 'received']);
        });
    }

    /**
     * Generate unique reference for import order
     */
    public function generateReference(int $instanceId): string
    {
        $year = now()->format('Y');
        $count = ImportOrder::where('instance_id', $instanceId)
            ->whereYear('created_at', $year)
            ->count() + 1;
        return sprintf('IMP-%s-%03d', $year, $count);
    }
}
