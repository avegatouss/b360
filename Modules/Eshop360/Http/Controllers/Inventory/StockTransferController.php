<?php

namespace Modules\Eshop360\Http\Controllers\Inventory;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockMovement;
use Modules\Eshop360\Models\StockTransfer;
use Modules\Eshop360\Models\Warehouse;
use Modules\Core\Support\CurrentInstance;

class StockTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:eshop.inventory.view')->only(['index', 'show']);
        $this->middleware('can:eshop.inventory.manage')->only(['store', 'update', 'complete', 'cancel']);
    }

    public function index(Request $request)
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product', 'transferredBy'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->from_warehouse_id, fn ($q, $w) => $q->where('from_warehouse_id', $w))
            ->when($request->to_warehouse_id, fn ($q, $w) => $q->where('to_warehouse_id', $w))
            ->when($request->search, fn ($q, $s) => $q->where('reference_number', 'like', "%{$s}%"))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::inventory.stocks.transfers', compact('transfers', 'warehouses'));
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:eshop_warehouses,id',
            'to_warehouse_id'   => 'required|exists:eshop_warehouses,id|different:from_warehouse_id',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $instance = CurrentInstance::get();

        $transfer = DB::transaction(function () use ($validated, $instance) {
            $transfer = StockTransfer::create([
                'instance_id'       => $instance?->id,
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id'   => $validated['to_warehouse_id'],
                'reference_number'  => 'TRF-' . strtoupper(Str::random(8)),
                'status'            => 'pending',
                'notes'             => $validated['notes'] ?? null,
                'transferred_by'    => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                // Verify source warehouse has sufficient stock
                $sourceStock = Stock::where('instance_id', $instance?->id)
                    ->where('product_id', $item['product_id'])
                    ->where('warehouse_id', $validated['from_warehouse_id'])
                    ->first();

                if (!$sourceStock || $sourceStock->available_quantity < $item['quantity']) {
                    throw new \RuntimeException(
                        __('Insufficient stock for product ID :id in source warehouse.', ['id' => $item['product_id']])
                    );
                }

                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);

                // Reserve stock in source warehouse
                $sourceStock->increment('reserved_quantity', $item['quantity']);
            }

            return $transfer;
        });

        return redirect()->route('eshop360.stock-transfers.index', $slug)
            ->with('success', __('Stock transfer :ref created.', ['ref' => $transfer->reference_number]));
    }

    public function show(string $slug, StockTransfer $transfer)
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'transferredBy']);
        $stockTransfer = $transfer;

        return view('eshop360::inventory.stocks.transfer-show', compact('stockTransfer'));
    }

    public function update(Request $request, string $slug, StockTransfer $transfer): RedirectResponse
    {
        if (!in_array($transfer->status, ['pending'])) {
            return redirect()->route('eshop360.stock-transfers.show', [$slug, $transfer])
                ->with('error', __('Only pending transfers can be updated.'));
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $transfer->update($validated);

        return redirect()->route('eshop360.stock-transfers.show', [$slug, $transfer])
            ->with('success', __('Transfer updated successfully.'));
    }

    public function complete(string $slug, StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status !== 'pending' && $transfer->status !== 'in_transit') {
            return redirect()->route('eshop360.stock-transfers.show', [$slug, $transfer])
                ->with('error', __('Only pending or in-transit transfers can be completed.'));
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Deduct from source warehouse
                $sourceStock = Stock::where('instance_id', $transfer->instance_id)
                    ->where('product_id', $item->product_id)
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->firstOrFail();

                $sourceStock->decrement('quantity', $item->quantity);
                $sourceStock->decrement('reserved_quantity', $item->quantity);

                StockMovement::create([
                    'instance_id'    => $transfer->instance_id,
                    'product_id'     => $item->product_id,
                    'warehouse_id'   => $transfer->from_warehouse_id,
                    'type'           => 'transfer',
                    'quantity'       => -$item->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id'   => $transfer->id,
                    'notes'          => "Transfer out to {$transfer->toWarehouse->name}",
                    'performed_by'   => auth()->id(),
                ]);

                // Add to destination warehouse
                $destStock = Stock::firstOrCreate(
                    [
                        'instance_id'  => $transfer->instance_id,
                        'product_id'   => $item->product_id,
                        'warehouse_id' => $transfer->to_warehouse_id,
                    ],
                    [
                        'quantity'          => 0,
                        'reserved_quantity' => 0,
                    ]
                );

                $destStock->increment('quantity', $item->quantity);

                StockMovement::create([
                    'instance_id'    => $transfer->instance_id,
                    'product_id'     => $item->product_id,
                    'warehouse_id'   => $transfer->to_warehouse_id,
                    'type'           => 'transfer',
                    'quantity'       => $item->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id'   => $transfer->id,
                    'notes'          => "Transfer in from {$transfer->fromWarehouse->name}",
                    'performed_by'   => auth()->id(),
                ]);
            }

            $transfer->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('eshop360.stock-transfers.show', [$slug, $transfer])
            ->with('success', __('Stock transfer completed successfully.'));
    }

    public function cancel(string $slug, StockTransfer $transfer): RedirectResponse
    {
        if (!in_array($transfer->status, ['pending', 'in_transit'])) {
            return redirect()->route('eshop360.stock-transfers.show', [$slug, $transfer])
                ->with('error', __('Only pending or in-transit transfers can be cancelled.'));
        }

        DB::transaction(function () use ($transfer) {
            // Release reserved stock
            foreach ($transfer->items as $item) {
                $sourceStock = Stock::where('instance_id', $transfer->instance_id)
                    ->where('product_id', $item->product_id)
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->first();

                if ($sourceStock) {
                    $sourceStock->decrement('reserved_quantity', $item->quantity);
                }
            }

            $transfer->update(['status' => 'cancelled']);
        });

        return redirect()->route('eshop360.stock-transfers.show', [$slug, $transfer])
            ->with('success', __('Stock transfer cancelled.'));
    }
}
