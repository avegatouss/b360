<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Purchasing\Models\ImportCost;
use Modules\Eshop360\Models\ImportOrder;
use Modules\Eshop360\Models\ImportOrderItem;

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
        $simulation = $this->simulateAllocation($order, $order->cost_allocation_method);

        foreach ($order->items as $item) {
            $sim = collect($simulation)->firstWhere('item_id', $item->id);
            if ($sim) {
                $item->update([
                    'allocated_cost' => $sim['allocated_cost'],
                    'cost_price_real' => $sim['cost_price_real'],
                ]);
            }
        }
    }

    /**
     * Simulate cost allocation WITHOUT saving (for comparison).
     * Returns an array of items with simulated values.
     */
    public function simulateAllocation(ImportOrder $order, string $method): array
    {
        $items = $order->items;
        $totalCosts = (float) $order->costs()->sum('amount');

        if ($items->isEmpty() || $totalCosts <= 0) {
            return $items->map(fn ($item) => [
                'item_id' => $item->id,
                'product_name' => $item->product?->name ?? '—',
                'sku' => $item->product?->sku ?? '',
                'quantity' => $item->quantity,
                'unit_price_factory' => (float) $item->unit_price_factory,
                'total_factory' => (float) $item->total_factory,
                'allocated_cost' => 0,
                'cost_per_unit' => 0,
                'cost_price_real' => (float) $item->unit_price_factory,
                'total_landed' => (float) $item->total_factory,
            ])->toArray();
        }

        $totalFactory = (float) $items->sum('total_factory');
        $totalQuantity = (int) $items->sum('quantity');

        return $items->map(function ($item) use ($method, $totalCosts, $totalFactory, $totalQuantity) {
            if ($method === 'value' && $totalFactory > 0) {
                $coefficient = (float) $item->total_factory / $totalFactory;
            } elseif ($method === 'hybrid' && $totalFactory > 0 && $totalQuantity > 0) {
                $partValue = (float) $item->total_factory / $totalFactory;
                $partQty = (float) $item->quantity / $totalQuantity;
                $coefficient = ($partValue + $partQty) / 2;
            } elseif ($totalQuantity > 0) {
                $coefficient = (float) $item->quantity / $totalQuantity;
            } else {
                $coefficient = 0;
            }

            $allocatedCost = round($totalCosts * $coefficient, 2);
            $costPerUnit = $item->quantity > 0 ? round($allocatedCost / $item->quantity, 4) : 0;
            $costPriceReal = round((float) $item->unit_price_factory + $costPerUnit, 4);

            return [
                'item_id' => $item->id,
                'product_name' => $item->product?->name ?? '—',
                'sku' => $item->product?->sku ?? '',
                'quantity' => (int) $item->quantity,
                'unit_price_factory' => (float) $item->unit_price_factory,
                'total_factory' => (float) $item->total_factory,
                'allocated_cost' => $allocatedCost,
                'cost_per_unit' => $costPerUnit,
                'cost_price_real' => $costPriceReal,
                'total_landed' => round($costPriceReal * $item->quantity, 2),
            ];
        })->toArray();
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
