<?php

namespace Modules\Eshop360\Http\Controllers\Purchase;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\PurchaseItem;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Services\StockService;
use Modules\Core\Support\CurrentInstance;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = PurchaseOrder::with(['supplier', 'warehouse'])
            ->withCount('items')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->supplier_id, fn ($q, $s) => $q->where('supplier_id', $s))
            ->when($request->search, function ($q, $s) {
                $q->where(function ($qq) use ($s) {
                    $qq->where('reference', 'like', "%{$s}%")
                       ->orWhere('supplier_name', 'like', "%{$s}%");
                });
            })
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        // KPIs
        $kpiAll = PurchaseOrder::where('instance_id', $instance->id);
        $kpiTotal = round((float) (clone $kpiAll)->sum('total'), 0);
        $kpiPaid = round((float) (clone $kpiAll)->sum('paid_amount'), 0);
        $kpiDue = round((float) (clone $kpiAll)->where('payment_status', '!=', 'paid')->sum('due_amount'), 0);
        $kpiCount = (clone $kpiAll)->count();
        $kpiPending = (clone $kpiAll)->where('status', 'pending')->count();
        $kpiReceived = (clone $kpiAll)->where('status', 'received')->count();

        $purchases = $query->latest()->paginate(25)->withQueryString();

        $suppliers = Supplier::where('instance_id', $instance->id)->orderBy('name')->get(['id', 'name']);

        return view('eshop360::purchases.index', compact(
            'purchases', 'suppliers',
            'kpiTotal', 'kpiPaid', 'kpiDue', 'kpiCount', 'kpiPending', 'kpiReceived'
        ));
    }

    public function create()
    {
        $instance = CurrentInstance::get();
        $suppliers = Supplier::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();
        $warehouses = \Modules\Eshop360\Models\Warehouse::where('instance_id', $instance->id)->where('is_active', true)->get();
        $products = \Modules\Eshop360\Models\Product::where('instance_id', $instance->id)->where('is_active', true)->get();

        return view('eshop360::purchases.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'         => 'nullable|exists:eshop_suppliers,id',
            'supplier_name'       => 'nullable|string|max:255',
            'supplier_email'      => 'nullable|email|max:255',
            'warehouse_id'        => 'nullable|exists:eshop_warehouses,id',
            'status'              => 'nullable|in:ordered,pending,received,cancelled',
            'paid_amount'         => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:eshop_products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_cost'   => 'required|numeric|min:0',
        ]);

        $instance = CurrentInstance::get();

        $purchase = DB::transaction(function () use ($validated, $instance) {
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

            $paidAmount = $validated['paid_amount'] ?? 0;
            $dueAmount = max(0, $total - $paidAmount);

            $paymentStatus = 'unpaid';
            if ($paidAmount >= $total) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            // Resolve supplier
            $supplierId = $validated['supplier_id'] ?? null;
            $supplierName = $validated['supplier_name'] ?? '';

            if ($supplierId && empty($supplierName)) {
                $supplier = Supplier::find($supplierId);
                $supplierName = $supplier?->name ?? '';
                $validated['supplier_email'] = $validated['supplier_email'] ?? $supplier?->email;
            } elseif (!$supplierId && !empty($supplierName)) {
                $supplierId = Supplier::where('instance_id', $instance?->id)
                    ->where('name', $supplierName)
                    ->value('id');
            }

            $purchase = PurchaseOrder::create([
                'instance_id'    => $instance?->id,
                'supplier_id'    => $supplierId,
                'supplier_name'  => $supplierName,
                'supplier_email' => $validated['supplier_email'] ?? null,
                'reference'      => 'PO-' . now()->format('Ymd') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT),
                'warehouse_id'   => $validated['warehouse_id'] ?? null,
                'status'         => $validated['status'] ?? 'pending',
                'total'          => $total,
                'paid_amount'    => $paidAmount,
                'due_amount'     => $dueAmount,
                'payment_status' => $paymentStatus,
                'notes'          => $validated['notes'] ?? null,
                'created_by'     => auth()->id(),
            ]);

            foreach ($itemsData as $itemData) {
                $purchase->items()->create($itemData);
            }

            if ($paidAmount > 0) {
                $purchase->payments()->create([
                    'instance_id' => $purchase->instance_id,
                    'amount' => $paidAmount,
                    'method' => 'manual',
                    'reference' => 'PO-INIT-' . $purchase->id,
                    'status' => 'completed',
                    'received_by' => auth()->id(),
                ]);
            }

            return $purchase;
        });

        if ($purchase->status === 'received') {
            $this->receivePurchaseStock($purchase, app(StockService::class));
        }

        return redirect()->route('eshop360.purchases.show', ['purchase' => $purchase])
            ->with('success', __('Purchase order :ref created.', ['ref' => $purchase->reference]));
    }

    public function show(PurchaseOrder $purchase)
    {
        $purchase->load(['items.product', 'payments', 'warehouse']);

        return view('eshop360::purchases.show', compact('purchase'));
    }

    public function update(Request $request, string $slug, PurchaseOrder $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name'  => 'required|string|max:255',
            'supplier_email' => 'nullable|email|max:255',
            'warehouse_id'   => 'nullable|exists:eshop_warehouses,id',
            'status'         => 'nullable|in:ordered,pending,received,cancelled',
            'paid_amount'    => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $previousPaidAmount = (float) $purchase->paid_amount;

        if (isset($validated['paid_amount'])) {
            $validated['due_amount'] = max(0, $purchase->total - $validated['paid_amount']);
            if ($validated['paid_amount'] >= $purchase->total) {
                $validated['payment_status'] = 'paid';
            } elseif ($validated['paid_amount'] > 0) {
                $validated['payment_status'] = 'partial';
            } else {
                $validated['payment_status'] = 'unpaid';
            }
        }

        $purchase->update($validated);

        if (isset($validated['paid_amount']) && $validated['paid_amount'] > $previousPaidAmount) {
            $purchase->payments()->create([
                'instance_id' => $purchase->instance_id,
                'amount' => round($validated['paid_amount'] - $previousPaidAmount, 2),
                'method' => 'manual',
                'reference' => 'PO-UPD-' . $purchase->id . '-' . now()->format('YmdHis'),
                'status' => 'completed',
                'received_by' => auth()->id(),
            ]);
        }

        if (($validated['status'] ?? $purchase->status) === 'received') {
            $this->receivePurchaseStock($purchase->fresh(['items.product']), app(StockService::class));
        }

        return redirect()->route('eshop360.purchases.show', ['purchase' => $purchase])
            ->with('success', __('Purchase order updated successfully.'));
    }

    public function destroy(string $slug, PurchaseOrder $purchase): RedirectResponse
    {
        $purchase->delete();

        return redirect()->route('eshop360.purchases.index', ['slug' => $slug])
            ->with('success', __('Purchase order deleted successfully.'));
    }

    public function report(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();
        $instanceId = CurrentInstance::idOrFail();

        $purchasesByStatus = PurchaseOrder::where('instance_id', $instanceId)->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get();

        $purchasesBySupplier = PurchaseOrder::where('instance_id', $instanceId)->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('supplier_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('supplier_name')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $monthlyPurchases = PurchaseOrder::where('instance_id', $instanceId)->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $totalPurchased = PurchaseOrder::where('instance_id', $instanceId)->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('total');
        $totalPaid = PurchaseOrder::where('instance_id', $instanceId)->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('paid_amount');
        $totalDue = PurchaseOrder::where('instance_id', $instanceId)->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('due_amount');

        return view('eshop360::purchases.report', compact(
            'purchasesByStatus', 'purchasesBySupplier', 'monthlyPurchases',
            'totalPurchased', 'totalPaid', 'totalDue', 'dateFrom', 'dateTo'
        ));
    }

    public function transactions(Request $request)
    {
        $instanceId = CurrentInstance::idOrFail();

        $purchases = PurchaseOrder::with('items.product')
            ->where('instance_id', $instanceId)
            ->where('payment_status', '!=', 'paid')
            ->when($request->search, fn ($q, $s) => $q->where(fn ($qq) => $qq->where('reference', 'like', "%{$s}%")
                ->orWhere('supplier_name', 'like', "%{$s}%")))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('due_amount')
            ->paginate(20)
            ->withQueryString();

        $totalDue = PurchaseOrder::where('instance_id', $instanceId)->where('payment_status', '!=', 'paid')->sum('due_amount');

        return view('eshop360::purchases.transactions', compact('purchases', 'totalDue'));
    }

    /**
     * Show per-line receiving form.
     */
    public function receiveForm(string $slug, PurchaseOrder $purchase)
    {
        $purchase->load(['items.product', 'warehouse']);
        $warehouses = \Modules\Eshop360\Models\Warehouse::all();

        return view('eshop360::purchases.receive', compact('purchase', 'warehouses'));
    }

    /**
     * Process per-line receiving and update stock accordingly.
     */
    public function receive(string $slug, PurchaseOrder $purchase, Request $request): RedirectResponse
    {
        abort_if($purchase->received_at !== null, 422, 'This purchase order has already been fully received.');

        $validated = $request->validate([
            'warehouse_id'                   => 'required|exists:eshop_warehouses,id',
            'items'                          => 'required|array',
            'items.*.purchase_item_id'       => 'required|exists:eshop_purchase_items,id',
            'items.*.received_qty'           => 'required|integer|min:0',
        ]);

        $stockService = app(StockService::class);

        DB::transaction(function () use ($purchase, $validated, $stockService) {
            $purchase->update(['warehouse_id' => $validated['warehouse_id']]);

            foreach ($validated['items'] as $itemData) {
                $item = $purchase->items()->findOrFail($itemData['purchase_item_id']);
                $qty = (int) $itemData['received_qty'];

                if ($qty <= 0) {
                    continue;
                }

                // Cap at remaining quantity
                $remaining = max(0, $item->quantity - $item->received_qty);
                $qty = min($qty, $remaining);

                if ($qty <= 0) {
                    continue;
                }

                $item->increment('received_qty', $qty);

                $stockService->adjustStock(
                    $item->product,
                    (int) $validated['warehouse_id'],
                    $qty,
                    'in',
                    "Purchase #{$purchase->reference}",
                    auth()->id(),
                    PurchaseOrder::class,
                    $purchase->id,
                );
            }

            // Update PO status
            $purchase->loadMissing('items');
            $allReceived = $purchase->items->every(fn ($i) => $i->received_qty >= $i->quantity);
            $anyReceived = $purchase->items->some(fn ($i) => $i->received_qty > 0);

            if ($allReceived) {
                $purchase->update(['status' => 'received', 'received_at' => now()]);
            } elseif ($anyReceived) {
                $purchase->update(['status' => 'partial']);
            }
        });

        return redirect()->route('eshop360.purchases.show', ['slug' => $slug, 'purchase' => $purchase])
            ->with('success', __('Reception recorded successfully.'));
    }

    private function receivePurchaseStock(PurchaseOrder $purchase, StockService $stockService): void
    {
        if ($purchase->received_at !== null) {
            return;
        }

        $purchase->loadMissing('items.product');

        DB::transaction(function () use ($purchase, $stockService) {
            foreach ($purchase->items as $item) {
                // 1. Add stock to warehouse
                $stockService->adjustStock(
                    $item->product,
                    $purchase->warehouse_id,
                    $item->quantity,
                    'in',
                    "Purchase #{$purchase->reference}",
                    auth()->id(),
                    PurchaseOrder::class,
                    $purchase->id,
                );

                // 2. Update product cost price (weighted average)
                if ($item->product && $item->unit_cost > 0) {
                    $product = $item->product;
                    $oldCost = (float) ($product->cost_price ?? 0);
                    $oldStock = (int) $product->stocks()->sum('quantity') - $item->quantity; // stock before this receipt
                    $newCost = (float) $item->unit_cost;
                    $newQty = (int) $item->quantity;

                    if ($oldCost > 0 && $oldStock > 0) {
                        // Weighted average: (old_cost * old_stock + new_cost * new_qty) / (old_stock + new_qty)
                        $weightedCost = round(($oldCost * $oldStock + $newCost * $newQty) / ($oldStock + $newQty), 2);
                    } else {
                        $weightedCost = $newCost;
                    }

                    $product->update(['cost_price' => $weightedCost]);
                }
            }

            $purchase->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });
    }
}
