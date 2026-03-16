<?php

namespace Modules\Eshop360\Http\Controllers\Purchase;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\PurchaseReturn;
use Modules\Eshop360\Services\StockService;
use Modules\Core\Support\CurrentInstance;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = PurchaseReturn::with(['purchaseOrder', 'items.product', 'warehouse', 'creator'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($query) use ($s) {
                $query->where('reference', 'like', "%{$s}%")
                    ->orWhere('supplier_name', 'like', "%{$s}%");
            }))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::purchases.returns', compact('returns'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_order_id'   => 'required|exists:eshop_purchase_orders,id',
            'warehouse_id'        => 'nullable|exists:eshop_warehouses,id',
            'status'              => 'nullable|in:pending,received,cancelled',
            'paid_amount'         => 'nullable|numeric|min:0',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:eshop_products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_cost'   => 'required|numeric|min:0',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $instance = CurrentInstance::get();

        $return = DB::transaction(function () use ($validated, $instance) {
            $originalPurchase = PurchaseOrder::findOrFail($validated['purchase_order_id']);

            $total = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_cost'];
                $total += $itemTotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_cost'  => $item['unit_cost'],
                    'total'      => $itemTotal,
                ];
            }

            $paidAmount = (float) ($validated['paid_amount'] ?? 0);
            $dueAmount = max(0, $total - $paidAmount);

            $return = PurchaseReturn::create([
                'instance_id'    => $instance?->id,
                'purchase_order_id' => $originalPurchase->id,
                'supplier_name'  => $originalPurchase->supplier_name,
                'reference'      => 'PR-' . now()->format('Ymd') . '-' . str_pad(PurchaseReturn::count() + 1, 4, '0', STR_PAD_LEFT),
                'warehouse_id'   => $validated['warehouse_id'] ?? $originalPurchase->warehouse_id,
                'status'         => $validated['status'] ?? 'pending',
                'total'          => $total,
                'paid_amount'    => $paidAmount,
                'due_amount'     => $dueAmount,
                'payment_status' => $dueAmount <= 0 ? 'paid' : 'unpaid',
                'notes'          => ($validated['notes'] ?? '') . " [Return for {$originalPurchase->reference}]",
                'created_by'     => auth()->id(),
                'processed_at'   => null,
            ]);

            foreach ($itemsData as $itemData) {
                $return->items()->create($itemData);
            }

            return $return;
        });

        if ($return->status === 'received') {
            $this->processReturnStock($return, app(StockService::class));
        }

        return redirect()->route('eshop360.purchase-returns.index')
            ->with('success', __('Purchase return :ref recorded.', ['ref' => $return->reference]));
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $validated = $request->validate([
            'notes'       => 'nullable|string|max:1000',
            'paid_amount' => 'nullable|numeric|min:0',
            'status'      => 'nullable|in:pending,received,cancelled',
            'warehouse_id' => 'nullable|exists:eshop_warehouses,id',
        ]);

        $updateData = [];

        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        if (isset($validated['paid_amount'])) {
            $updateData['paid_amount'] = $validated['paid_amount'];
            $updateData['due_amount'] = max(0, $purchaseReturn->total - $validated['paid_amount']);
            $updateData['payment_status'] = $validated['paid_amount'] >= $purchaseReturn->total ? 'paid' : 'unpaid';
        }

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        if (array_key_exists('warehouse_id', $validated)) {
            $updateData['warehouse_id'] = $validated['warehouse_id'];
        }

        $purchaseReturn->update($updateData);

        if (($validated['status'] ?? $purchaseReturn->status) === 'received') {
            $this->processReturnStock($purchaseReturn->fresh(['items.product']), app(StockService::class));
        }

        return redirect()->route('eshop360.purchase-returns.index')
            ->with('success', __('Purchase return updated successfully.'));
    }

    public function destroy(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        if ($purchaseReturn->processed_at !== null) {
            return redirect()->route('eshop360.purchase-returns.index')
                ->with('error', __('Processed purchase returns cannot be deleted.'));
        }

        $purchaseReturn->items()->delete();
        $purchaseReturn->delete();

        return redirect()->route('eshop360.purchase-returns.index')
            ->with('success', __('Purchase return deleted successfully.'));
    }

    private function processReturnStock(PurchaseReturn $purchaseReturn, StockService $stockService): void
    {
        if ($purchaseReturn->processed_at !== null) {
            return;
        }

        $purchaseReturn->loadMissing('items.product');

        DB::transaction(function () use ($purchaseReturn, $stockService) {
            foreach ($purchaseReturn->items as $item) {
                $stockService->adjustStock(
                    $item->product,
                    $purchaseReturn->warehouse_id,
                    -$item->quantity,
                    'return',
                    "Purchase return #{$purchaseReturn->reference}",
                    auth()->id(),
                    PurchaseReturn::class,
                    $purchaseReturn->id,
                );
            }

            $purchaseReturn->update([
                'status' => 'received',
                'processed_at' => now(),
            ]);
        });
    }
}
