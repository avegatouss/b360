<?php

namespace Modules\Eshop360\Http\Controllers\Sales;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Http\Controllers\Traits\ResolvesPosContext;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\StockService;

class SaleController extends Controller
{
    use ResolvesPosContext;
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartService $cartService,
    ) {
    }

    public function dashboard(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();
        $channelId = $request->channel_id;

        $baseQuery = Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->when($channelId, fn ($q) => $q->where('channel_id', $channelId));

        $completedQuery = (clone $baseQuery)->where('status', 'completed');

        $totalSales = round((float) (clone $completedQuery)->sum('total'), 2);
        $totalOrders = (clone $baseQuery)->count();
        $completedOrders = (clone $completedQuery)->count();
        $pendingOrders = (clone $baseQuery)->where('status', 'pending')->count();
        $cancelledOrders = (clone $baseQuery)->where('status', 'cancelled')->count();
        $totalTax = round((float) (clone $completedQuery)->sum('tax_amount'), 2);
        $totalDiscount = round((float) (clone $completedQuery)->sum('discount_amount'), 2);
        $totalDue = round((float) (clone $baseQuery)->where('payment_status', '!=', 'paid')->sum('due_amount'), 2);
        $totalPaid = round((float) (clone $completedQuery)->sum('paid_amount'), 2);
        $avgOrderValue = $completedOrders > 0 ? round($totalSales / $completedOrders, 2) : 0;

        // Today's sales
        $todaySales = round((float) Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->when($channelId, fn ($q) => $q->where('channel_id', $channelId))
            ->sum('total'), 2);

        // Daily sales for chart
        $dailySales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->when($channelId, fn ($q) => $q->where('channel_id', $channelId))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Sales by source (pos, online, manual, channel_portal)
        $salesBySource = (clone $baseQuery)->where('status', '!=', 'cancelled')
            ->select('source', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('source')
            ->get()
            ->keyBy('source');

        // Sales by payment method
        $salesByPayment = (clone $completedQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Top selling products
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_revenue'))
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo, $channelId) {
                $q->where('status', 'completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                    ->when($channelId, fn ($q2) => $q2->where('channel_id', $channelId));
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->with('product:id,name,sku,image')
            ->get();

        // Recent sales
        $recentSales = Order::with('customer', 'channel')
            ->when($channelId, fn ($q) => $q->where('channel_id', $channelId))
            ->latest()
            ->limit(10)
            ->get();

        // Channels for filter
        $channels = \Modules\Eshop360\Models\DistributionChannel::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('eshop360::sales.dashboard', compact(
            'totalSales', 'totalOrders', 'completedOrders', 'pendingOrders',
            'cancelledOrders', 'totalTax', 'totalDiscount', 'totalDue',
            'totalPaid', 'avgOrderValue', 'todaySales',
            'dailySales', 'salesBySource', 'salesByPayment',
            'topProducts', 'recentSales', 'dateFrom', 'dateTo',
            'channels', 'channelId'
        ));
    }

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = Order::with(['customer', 'channel'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->source, fn ($q, $s) => $q->where('source', $s))
            ->when($request->channel_id, fn ($q, $c) => $q->where('channel_id', $c))
            ->when($request->payment_method, fn ($q, $m) => $q->where('payment_method', $m))
            ->when($request->search, function ($q, $s) {
                $q->where(function ($qq) use ($s) {
                    $qq->where('order_number', 'like', "%{$s}%")
                       ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
                });
            })
            ->when($request->customer_id, fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->min_total, fn ($q, $m) => $q->where('total', '>=', $m))
            ->when($request->max_total, fn ($q, $m) => $q->where('total', '<=', $m));

        // KPI from the same filtered query (before pagination)
        $filteredQuery = clone $query;
        $kpiTotal = round((float) (clone $filteredQuery)->sum('total'), 0);
        $kpiPaid = round((float) (clone $filteredQuery)->sum('paid_amount'), 0);
        $kpiDue = round((float) (clone $filteredQuery)->where('payment_status', '!=', 'paid')->sum('due_amount'), 0);
        $kpiCount = (clone $filteredQuery)->count();
        $kpiCompleted = (clone $filteredQuery)->where('status', 'completed')->count();
        $kpiPending = (clone $filteredQuery)->where('status', 'pending')->count();
        $kpiCancelled = (clone $filteredQuery)->whereIn('status', ['cancelled', 'refunded'])->count();
        $kpiAvg = $kpiCount > 0 ? round($kpiTotal / $kpiCount, 0) : 0;

        $sales = $query->latest()->paginate(25)->withQueryString();

        // Lookups for filters
        $customers = Customer::where('instance_id', $instance->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $channels = \Modules\Eshop360\Models\DistributionChannel::where('instance_id', $instance->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $paymentMethods = Order::where('instance_id', $instance->id)
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method');

        return view('eshop360::sales.index', compact(
            'sales', 'customers', 'channels', 'paymentMethods',
            'kpiTotal', 'kpiPaid', 'kpiDue', 'kpiCount',
            'kpiCompleted', 'kpiPending', 'kpiCancelled', 'kpiAvg'
        ));
    }

    public function create(string $slug)
    {
        $instance = CurrentInstance::get();
        $customers = Customer::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();
        $channels = \Modules\Eshop360\Models\DistributionChannel::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('eshop360::sales.create', compact('customers', 'products', 'channels'));
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
            $this->cartService->clear();
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

    public function stats(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        $instanceId = $instance?->id ?? 0;
        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        // Base query for completed orders
        $baseQuery = Order::where('instance_id', $instanceId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        // Global KPIs
        $globalStats = (clone $baseQuery)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total), 0) as total_revenue,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(due_amount), 0) as total_due,
            COALESCE(SUM(tax_amount), 0) as total_tax,
            COALESCE(SUM(discount_amount), 0) as total_discount,
            COALESCE(AVG(total), 0) as avg_order
        ')->first();

        // Sales by channel
        $byChannel = (clone $baseQuery)
            ->leftJoin('eshop_distribution_channels', 'eshop_orders.channel_id', '=', 'eshop_distribution_channels.id')
            ->selectRaw('COALESCE(eshop_distribution_channels.name, "Direct") as channel_name, COUNT(*) as orders, SUM(eshop_orders.total) as revenue')
            ->groupBy('eshop_orders.channel_id', 'eshop_distribution_channels.name')
            ->orderByDesc('revenue')
            ->get();

        // Sales by customer (top 20)
        $byCustomer = (clone $baseQuery)
            ->join('eshop_customers', 'eshop_orders.customer_id', '=', 'eshop_customers.id')
            ->selectRaw('eshop_customers.name as customer_name, eshop_customers.id as customer_id, COUNT(*) as orders, SUM(eshop_orders.total) as revenue, SUM(eshop_orders.due_amount) as due')
            ->groupBy('eshop_orders.customer_id', 'eshop_customers.name', 'eshop_customers.id')
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();

        // Sales by store
        $byStore = (clone $baseQuery)
            ->leftJoin('eshop_stores', 'eshop_orders.store_id', '=', 'eshop_stores.id')
            ->selectRaw('COALESCE(eshop_stores.name, "N/A") as store_name, COUNT(*) as orders, SUM(eshop_orders.total) as revenue')
            ->groupBy('eshop_orders.store_id', 'eshop_stores.name')
            ->orderByDesc('revenue')
            ->get();

        // Sales by warehouse
        $byWarehouse = (clone $baseQuery)
            ->leftJoin('eshop_warehouses', 'eshop_orders.warehouse_id', '=', 'eshop_warehouses.id')
            ->selectRaw('COALESCE(eshop_warehouses.name, "N/A") as warehouse_name, COUNT(*) as orders, SUM(eshop_orders.total) as revenue')
            ->groupBy('eshop_orders.warehouse_id', 'eshop_warehouses.name')
            ->orderByDesc('revenue')
            ->get();

        // Sales by source
        $bySource = (clone $baseQuery)
            ->selectRaw('source, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('source')
            ->orderByDesc('revenue')
            ->get();

        // Sales by payment method
        $byPaymentMethod = (clone $baseQuery)
            ->selectRaw('payment_method, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        // Top products
        $topProducts = DB::table('eshop_order_items')
            ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
            ->leftJoin('eshop_products', 'eshop_order_items.product_id', '=', 'eshop_products.id')
            ->where('eshop_orders.instance_id', $instanceId)
            ->where('eshop_orders.status', 'completed')
            ->whereBetween('eshop_orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('
                eshop_order_items.product_id,
                COALESCE(eshop_products.name, eshop_order_items.product_name) as product_name,
                eshop_products.sku,
                eshop_products.price as current_price,
                eshop_products.cost_price,
                eshop_products.purchase_price_factory,
                SUM(eshop_order_items.quantity) as qty_sold,
                SUM(eshop_order_items.total) as revenue,
                SUM(eshop_order_items.tax) as tax_collected,
                SUM(eshop_order_items.discount) as discount_given
            ')
            ->groupBy('eshop_order_items.product_id', 'eshop_products.name', 'eshop_order_items.product_name', 'eshop_products.sku', 'eshop_products.price', 'eshop_products.cost_price', 'eshop_products.purchase_price_factory')
            ->orderByDesc('revenue')
            ->limit(50)
            ->get();

        // Margin analysis
        $marginData = $topProducts->map(function ($p) {
            $costPrice = (float) ($p->cost_price ?? 0);
            $revenue = (float) $p->revenue;
            $qtySold = (int) $p->qty_sold;
            $totalCost = $costPrice * $qtySold;
            $grossMargin = $revenue - $totalCost;
            $marginPct = $revenue > 0 ? round($grossMargin / $revenue * 100, 1) : 0;
            return (object) array_merge((array) $p, [
                'total_cost' => $totalCost,
                'gross_margin' => $grossMargin,
                'margin_pct' => $marginPct,
            ]);
        });

        // Monthly trend (last 12 months)
        $monthlyTrend = Order::where('instance_id', $instanceId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as orders, SUM(total) as revenue, SUM(paid_amount) as paid')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        // Charges for Saphir section
        $chargesService = app(\Modules\Eshop360\Services\ChargesService::class);
        $monthlyCharges = $chargesService->getTotalCostPerSecond($instanceId) * \Modules\Eshop360\Services\ChargesService::SECONDS_PER_MONTH;

        // Channels and stores for filters
        $channels = \Modules\Eshop360\Models\DistributionChannel::where('instance_id', $instanceId)->where('is_active', true)->orderBy('name')->get();
        $stores = \Modules\Eshop360\Models\Store::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $warehouses = \Modules\Eshop360\Models\Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('eshop360::sales.stats', compact(
            'globalStats', 'byChannel', 'byCustomer', 'byStore', 'byWarehouse',
            'bySource', 'byPaymentMethod', 'topProducts', 'marginData',
            'monthlyTrend', 'monthlyCharges', 'channels', 'stores', 'warehouses',
            'from', 'to'
        ));
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

}
