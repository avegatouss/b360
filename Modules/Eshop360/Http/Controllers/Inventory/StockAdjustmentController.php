<?php

namespace Modules\Eshop360\Http\Controllers\Inventory;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\StockMovement;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;

class StockAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:eshop.inventory.view')->only('index');
        $this->middleware('can:eshop.inventory.manage')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $adjustments = StockMovement::with(['product', 'warehouse', 'store', 'performer'])
            ->where('type', 'adjustment')
            ->when($request->warehouse_id, fn ($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->search, fn ($q, $s) => $q->whereHas('product', function ($pq) use ($s) {
                $pq->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%");
            }))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::inventory.stocks.adjustments', compact('adjustments', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'warehouse_id' => 'required|exists:eshop_warehouses,id',
            'store_id' => 'nullable|exists:eshop_stores,id',
            'quantity' => 'required|integer|not_in:0',
            'reason' => 'required|string|in:damaged,lost,correction,recount,return,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        $instance = CurrentInstance::get();

        DB::transaction(function () use ($validated, $instance) {
            $stock = Stock::firstOrCreate(
                [
                    'instance_id' => $instance->id,
                    'product_id' => $validated['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'store_id' => $validated['store_id'] ?? null,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]
            );

            $newQuantity = $stock->quantity + $validated['quantity'];
            if ($newQuantity < 0) {
                throw new \RuntimeException(
                    __('Ajustement impossible : le stock resultant serait negatif (:new).', ['new' => $newQuantity])
                );
            }

            $stock->increment('quantity', $validated['quantity']);

            StockMovement::create([
                'instance_id' => $instance->id,
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'store_id' => $validated['store_id'] ?? null,
                'type' => 'adjustment',
                'quantity' => $validated['quantity'],
                'notes' => "[{$validated['reason']}] ".($validated['notes'] ?? ''),
                'performed_by' => auth()->id(),
            ]);
        });

        return redirect()->route('eshop360.stock-adjustments.index', CurrentInstance::get()->slug)
            ->with('success', __('Stock adjustment recorded successfully.'));
    }

    public function update(Request $request, string $slug, StockMovement $movement): RedirectResponse
    {
        if ($movement->type !== 'adjustment') {
            return redirect()->route('eshop360.stock-adjustments.index', $slug)
                ->with('error', __('Only adjustment movements can be modified.'));
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $movement->update(['notes' => $validated['notes']]);

        return redirect()->route('eshop360.stock-adjustments.index', $slug)
            ->with('success', __('Adjustment notes updated successfully.'));
    }

    public function destroy(Request $request, string $slug, StockMovement $movement): RedirectResponse
    {
        if ($movement->type !== 'adjustment') {
            return redirect()->route('eshop360.stock-adjustments.index', $slug)
                ->with('error', __('Only adjustment movements can be deleted.'));
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|in:erreur_saisie,doublon,correction,annulation_commande,retour_fournisseur,autre',
        ]);

        $reason = $validated['cancellation_reason'] ?? 'correction';

        DB::transaction(function () use ($movement, $reason) {
            $stock = Stock::where('instance_id', $movement->instance_id)
                ->where('product_id', $movement->product_id)
                ->where('warehouse_id', $movement->warehouse_id)
                ->when(
                    $movement->store_id === null,
                    fn ($query) => $query->whereNull('store_id'),
                    fn ($query) => $query->where('store_id', $movement->store_id)
                )
                ->first();

            if ($stock) {
                $newQuantity = $stock->quantity - $movement->quantity;
                if ($newQuantity < 0) {
                    throw new \RuntimeException(
                        __('Impossible d\'annuler : le stock deviendrait negatif.')
                    );
                }
                $stock->decrement('quantity', $movement->quantity);
            }

            // Log the reversal as a new movement before deleting
            StockMovement::create([
                'instance_id' => $movement->instance_id,
                'product_id' => $movement->product_id,
                'warehouse_id' => $movement->warehouse_id,
                'store_id' => $movement->store_id,
                'type' => 'adjustment',
                'quantity' => -$movement->quantity,
                'notes' => "[annulation:{$reason}] Annulation ajustement #{$movement->id} — ".($movement->notes ?? ''),
                'performed_by' => auth()->id(),
            ]);

            $movement->delete();
        });

        return redirect()->route('eshop360.stock-adjustments.index', $slug)
            ->with('success', __('Ajustement annule avec succes.'));
    }
}
