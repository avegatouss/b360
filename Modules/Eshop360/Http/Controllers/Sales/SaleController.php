<?php

namespace Modules\Eshop360\Http\Controllers\Sales;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\StockService;

class SaleController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function dashboard(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $salesQuery = Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        $totalSales = (clone $salesQuery)->where('status', 'completed')->sum('total');
        $totalOrders = (clone $salesQuery)->count();
        $completedOrders = (clone $salesQuery)->where('status', 'completed')->count();
        $pendingOrders = (clone $salesQuery)->where('status', 'pending')->count();
        $cancelledOrders = (clone $salesQuery)->where('status', 'cancelled')->count();
        $totalTax = (clone $salesQuery)->where('status', 'completed')->sum('tax_amount');
        $totalDiscount = (clone $salesQuery)->where('status', 'completed')->sum('discount_amount');
        $totalDue = (clone $salesQuery)->where('payment_status', '!=', 'paid')->sum('due_amount');

        // Daily sales for chart
        $dailySales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top selling products
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_revenue'))
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->with('product:id,name,sku,image')
            ->get();

        // Recent sales
        $recentSales = Order::with('customer')
            ->latest()
            ->limit(10)
            ->get();

        return view('eshop360::sales.dashboard', compact(
            'totalSales', 'totalOrders', 'completedOrders', 'pendingOrders',
            'cancelledOrders', 'totalTax', 'totalDiscount', 'totalDue',
            'dailySales', 'topProducts', 'recentSales', 'dateFrom', 'dateTo'
        ));
    }

    public function index(Request $request)
    {
        $sales = Order::with(['customer', 'items'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->source, fn ($q, $s) => $q->where('source', $s))
            ->when($request->search, fn ($q, $s) => $q->where('order_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%")))
            ->when($request->customer_id, fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::sales.index', compact('sales'));
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'       => 'nullable|exists:eshop_customers,id',
            'channel_id'        => 'nullable|exists:eshop_distribution_channels,id',
            'payment_method'    => 'required|string|in:cash,card,cheque,paypal,bank_transfer,points,deposit,gift_card,external',
            'paid_amount'       => 'required|numeric|min:0',
            'discount_amount'   => 'nullable|numeric|min:0',
            'shipping_amount'   => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:1000',
            'coupon_code'       => 'nullable|string|max:50',
            'source'            => 'nullable|in:pos,online,manual',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.original_price' => 'nullable|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
        ]);

        $instance = CurrentInstance::get();
        $orderData = [
            'instance_id' => $instance?->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'status' => 'completed',
            'payment_method' => $validated['payment_method'],
            'paid_amount' => (float) $validated['paid_amount'],
            'discount_amount' => (float) ($validated['discount_amount'] ?? 0),
            'shipping_amount' => (float) ($validated['shipping_amount'] ?? 0),
            'coupon_code' => $validated['coupon_code'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => $validated['source'] ?? 'manual',
            'biller_id' => auth()->id(),
            'number_prefix' => 'SAL',
            'channel_id' => $validated['channel_id'] ?? null,
        ];

        if (($validated['source'] ?? 'manual') === 'pos') {
            $orderData = array_merge($orderData, $this->resolvePosOperationalData());
        }

        $order = $this->orderService->createFromItems($validated['items'], $orderData);

        if (! empty($validated['coupon_code'])) {
            Coupon::query()
                ->where('instance_id', $instance?->id)
                ->where('code', $validated['coupon_code'])
                ->increment('used_count');
        }

        if (($validated['source'] ?? null) === 'pos') {
            $this->clearCart();
        }

        return redirect()->route('eshop360.sales.show', [
            'slug' => $slug,
            'order' => $order,
        ])
            ->with('success', __('Sale :number created successfully.', ['number' => $order->order_number]));
    }

    public function show(string $slug, Order $order)
    {
        $order->load(['customer', 'items.product', 'payments', 'cashRegister.store', 'store', 'holding']);
        $sale = $order;

        return view('eshop360::sales.show', compact('sale'));
    }

    public function update(Request $request, string $slug, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status'         => 'nullable|in:pending,processing,completed,cancelled,refunded',
            'payment_status' => 'nullable|in:unpaid,partial,paid,overdue',
            'paid_amount'    => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $updateData = array_filter($validated, fn ($v) => $v !== null);

        if (isset($updateData['paid_amount'])) {
            $this->orderService->syncPaidAmount(
                $order,
                (float) $updateData['paid_amount'],
                $order->payment_method ?? 'cash',
                'SAL-UPD',
                'Manual sale payment update'
            );

            unset($updateData['paid_amount'], $updateData['payment_status']);
        }

        $order->update($updateData);

        return redirect()->route('eshop360.sales.show', [
            'slug' => $slug,
            'order' => $order,
        ])
            ->with('success', __('Sale updated successfully.'));
    }

    public function destroy(string $slug, Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('eshop360.sales.index', ['slug' => $slug])
            ->with('success', __('Sale deleted successfully.'));
    }

    public function returns(Request $request, string $slug)
    {
        $returns = Order::with(['customer', 'items'])
            ->where('status', 'refunded')
            ->when($request->search, fn ($q, $s) => $q->where('order_number', 'like', "%{$s}%"))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::sales.returns', compact('returns'));
    }

    public function storeReturn(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'order_id'           => 'required|exists:eshop_orders,id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.reason'     => 'nullable|string|max:500',
            'refund_amount'      => 'required|numeric|min:0',
            'refund_method'      => 'nullable|in:cash,wallet,original',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $instance = CurrentInstance::get();

        DB::transaction(function () use ($validated, $instance) {
            $originalOrder = Order::with('items')->findOrFail($validated['order_id']);
            $originalOrder->update(['status' => 'refunded']);

            $stockService = app(StockService::class);

            // Record refund as negative order
            $returnOrder = Order::create([
                'instance_id'     => $instance?->id,
                'customer_id'     => $originalOrder->customer_id,
                'order_number'    => 'RET-' . now()->format('Ymd') . '-' . str_pad(Order::where('order_number', 'like', 'RET-%')->count() + 1, 4, '0', STR_PAD_LEFT),
                'status'          => 'refunded',
                'payment_status'  => 'paid',
                'payment_method'  => $originalOrder->payment_method,
                'subtotal'        => -$validated['refund_amount'],
                'total'           => -$validated['refund_amount'],
                'paid_amount'     => -$validated['refund_amount'],
                'due_amount'      => 0,
                'notes'           => $validated['notes'] ?? "Return for order {$originalOrder->order_number}",
                'source'          => $originalOrder->source,
                'biller_id'       => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $orderItem = $originalOrder->items->firstWhere('product_id', $item['product_id']);

                if (! $orderItem) {
                    throw ValidationException::withMessages([
                        'items' => ['Un produit du retour n\'appartient pas a la vente selectionnee.'],
                    ]);
                }

                $returnQuantity = (int) $item['quantity'];
                $maxQuantity = max(1, (int) $orderItem->quantity);

                if ($returnQuantity > $maxQuantity) {
                    throw ValidationException::withMessages([
                        'items' => ['La quantite retournee depasse la quantite vendue.'],
                    ]);
                }

                $lineDiscount = round(((float) ($orderItem->discount ?? 0) / $maxQuantity) * $returnQuantity, 2);
                $lineTax = round(((float) ($orderItem->tax ?? 0) / $maxQuantity) * $returnQuantity, 2);
                $lineTotal = round(((float) $orderItem->total / $maxQuantity) * $returnQuantity, 2);

                $returnOrder->items()->create([
                    'product_id'   => $item['product_id'],
                    'product_name' => $product->name,
                    'sku'          => $product->sku,
                    'quantity'     => -$returnQuantity,
                    'unit_price'   => $orderItem->unit_price,
                    'discount'     => -$lineDiscount,
                    'tax'          => -$lineTax,
                    'total'        => -$lineTotal,
                ]);

                $stockService->adjustStock(
                    $product,
                    null,
                    $returnQuantity,
                    'return',
                    "Sales return #{$returnOrder->order_number}",
                    auth()->id(),
                    Order::class,
                    $returnOrder->id,
                );
            }

            $refundMethod = $validated['refund_method'] ?? 'cash';

            // If refund to wallet, credit customer balance
            if ($refundMethod === 'wallet' && $originalOrder->customer_id) {
                Customer::where('id', $originalOrder->customer_id)
                    ->increment('wallet_balance', (float) $validated['refund_amount']);
            }

            $returnOrder->payments()->create([
                'instance_id' => $returnOrder->instance_id,
                'amount' => -((float) $validated['refund_amount']),
                'method' => $refundMethod,
                'reference' => 'SALE-REFUND-' . $returnOrder->id,
                'status' => 'refunded',
                'notes' => $validated['notes'] ?? 'Sales refund',
                'received_by' => auth()->id(),
            ]);
        });

        return redirect()->route('eshop360.sales.returns', ['slug' => $slug])
            ->with('success', __('Return processed successfully.'));
    }

    public function taxReport(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $taxByDay = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(tax_amount) as tax_amount'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $taxByProduct = OrderItem::select(
                'product_id',
                DB::raw('SUM(tax) as total_tax'),
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_tax')
            ->with('product:id,name,sku,tax_rate')
            ->paginate(30)
            ->withQueryString();

        $totalTax = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->sum('tax_amount');

        return view('eshop360::sales.tax-report', compact('taxByDay', 'taxByProduct', 'totalTax', 'dateFrom', 'dateTo'));
    }

    private function clearCart(): void
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;

        session()->forget([
            'eshop_cart',
            'eshop_cart_coupon',
            'eshop_cart_instance_' . $instanceId,
            'eshop_cart_coupon_instance_' . $instanceId,
        ]);
    }

    /**
     * @return array<string, int|null>
     */
    private function resolvePosOperationalData(): array
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $settings = Cache::get("eshop_pos_settings_{$instanceId}", []);
        $register = app(\Modules\Eshop360\Services\CashRegisterService::class)->getCurrentRegister();
        $warehouseId = $settings['default_warehouse_id'] ?? $register?->store?->warehouse_id ?? null;

        return array_filter([
            'store_id' => $register?->store_id,
            'warehouse_id' => $warehouseId,
            'cash_register_id' => $register?->id,
        ], static fn ($value) => $value !== null);
    }
}
