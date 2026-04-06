<?php

namespace Modules\Eshop360\Services;

use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockMovement;
use Modules\Eshop360\Models\StockTransfer;
use Modules\Eshop360\Models\StockTransferItem;
use Modules\Eshop360\Models\Warehouse;

class StockService
{
    /**
     * Adjust stock for a product in a specific warehouse.
     *
     * @param string $type Canonical values: in, out, adjustment, transfer, return
     * Legacy aliases are normalized internally.
     */
    public function adjustStock(
        Product $product,
        ?int $warehouseId,
        int $quantity,
        string $type,
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): StockMovement {
        $instance = CurrentInstance::get();

        return DB::transaction(function () use ($product, $warehouseId, $quantity, $type, $notes, $userId, $instance, $referenceType, $referenceId) {
            $resolvedWarehouseId = $this->resolveWarehouseId($product, $warehouseId, $instance?->id);
            [$movementType, $delta] = $this->normalizeMovement($type, $quantity);

            // Find or create the stock record with pessimistic locking
            $stock = Stock::lockForUpdate()->firstOrCreate(
                [
                    'instance_id'  => $instance?->id,
                    'product_id'   => $product->id,
                    'warehouse_id' => $resolvedWarehouseId,
                ],
                [
                    'quantity'          => 0,
                    'reserved_quantity' => 0,
                ],
            );

            // Refresh under the lock to get the latest quantity
            $stock->refresh();

            $newQuantity = $stock->quantity + $delta;

            if ($newQuantity < 0) {
                throw new InvalidArgumentException("Insufficient stock for product {$product->id} in warehouse {$resolvedWarehouseId}.");
            }

            $stock->increment('quantity', $delta);

            // Record the movement
            return $this->recordMovement([
                'instance_id'  => $instance?->id,
                'product_id'   => $product->id,
                'warehouse_id' => $resolvedWarehouseId,
                'type'         => $movementType,
                'quantity'     => $delta,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes'        => $notes,
                'performed_by' => $userId ?? auth()->id(),
            ]);
        });
    }

    /**
     * Transfer stock between warehouses.
     *
     * @param array<int, array{product_id: int, quantity: int}> $items
     */
    public function transferStock(
        array $items,
        int $fromWarehouseId,
        int $toWarehouseId,
        ?int $userId = null,
    ): StockTransfer {
        $instance = CurrentInstance::get();

        return DB::transaction(function () use ($items, $fromWarehouseId, $toWarehouseId, $userId, $instance) {
            $transfer = StockTransfer::create([
                'instance_id'       => $instance?->id,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id'   => $toWarehouseId,
                'reference_number'  => 'TRF-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'status'            => 'pending',
                'transferred_by'    => $userId ?? auth()->id(),
            ]);

            foreach ($items as $itemData) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $itemData['product_id'],
                    'quantity'          => $itemData['quantity'],
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Get the available quantity for a product, optionally filtered by warehouse.
     */
    public function getAvailableQuantity(Product $product, ?int $warehouseId = null): int
    {
        $query = Stock::where('product_id', $product->id);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        $stocks = $query->get();

        return $stocks->sum(fn (Stock $stock) => $stock->quantity - $stock->reserved_quantity);
    }

    /**
     * Get products that are at or below their alert quantity threshold.
     */
    public function checkLowStock(?int $warehouseId = null): Collection
    {
        $query = Product::active()
            ->whereHas('stocks', function ($q) use ($warehouseId) {
                if ($warehouseId !== null) {
                    $q->where('warehouse_id', $warehouseId);
                }
                $q->whereColumn('quantity', '<=', 'eshop_products.alert_quantity');
            })
            ->with(['stocks' => function ($q) use ($warehouseId) {
                if ($warehouseId !== null) {
                    $q->where('warehouse_id', $warehouseId);
                }
            }]);

        return $query->get();
    }

    /**
     * Get products that have passed their expiry date.
     */
    public function checkExpiredProducts(): Collection
    {
        return Product::expired()->with('stocks')->get();
    }

    /**
     * Record a stock movement entry.
     */
    public function recordMovement(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function normalizeMovement(string $type, int $quantity): array
    {
        return match ($type) {
            'addition', 'purchase', 'in', 'receive', 'received', 'import' => ['in', abs($quantity)],
            'subtraction', 'sale', 'out', 'consume' => ['out', -abs($quantity)],
            'adjustment' => ['adjustment', $quantity],
            'transfer' => ['transfer', $quantity],
            'return' => ['return', $quantity],
            default => throw new InvalidArgumentException("Unsupported stock movement type [{$type}]."),
        };
    }

    private function resolveWarehouseId(Product $product, ?int $warehouseId, ?int $instanceId): int
    {
        if ($warehouseId !== null) {
            return $warehouseId;
        }

        $existingWarehouseId = Stock::query()
            ->where('product_id', $product->id)
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->orderByDesc('quantity')
            ->value('warehouse_id');

        if ($existingWarehouseId) {
            return (int) $existingWarehouseId;
        }

        $fallbackWarehouseId = Warehouse::query()
            ->when($instanceId !== null, fn ($query) => $query->where('instance_id', $instanceId))
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        if ($fallbackWarehouseId) {
            return (int) $fallbackWarehouseId;
        }

        throw new InvalidArgumentException("No warehouse available for product {$product->id}.");
    }
}
