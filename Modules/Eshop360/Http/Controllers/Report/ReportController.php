<?php

namespace Modules\Eshop360\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockMovement;
use Modules\Eshop360\Services\ChannelAccessService;

class ReportController extends Controller
{
    public function __construct(
        private readonly ChannelAccessService $channelAccess,
    ) {}

    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();
        $user = auth()->user();
        $channelFilter = $request->integer('channel_id') ?: null;
        $channels = $this->channelAccess->availableChannelsForFilter($user);

        $baseQuery = fn () => $this->channelAccess->scopeWithChannelFilter(
            Order::where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']),
            $user, $channelFilter
        );

        $salesByDay = $baseQuery()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total'),
                DB::raw('SUM(tax_amount) as tax'),
                DB::raw('SUM(discount_amount) as discount'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $salesBySource = $baseQuery()
            ->select('source', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->get();

        $salesByPayment = $baseQuery()
            ->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $totals = [
            'revenue'  => $baseQuery()->sum('total'),
            'orders'   => $baseQuery()->count(),
            'tax'      => $baseQuery()->sum('tax_amount'),
            'discount' => $baseQuery()->sum('discount_amount'),
            'due'      => $this->channelAccess->scopeWithChannelFilter(
                Order::where('payment_status', '!=', 'paid')->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']),
                $user, $channelFilter
            )->sum('due_amount'),
        ];

        return view('eshop360::sales.report', compact('salesByDay', 'salesBySource', 'salesByPayment', 'totals', 'dateFrom', 'dateTo', 'channels', 'channelFilter'));
    }

    public function inventory(Request $request)
    {
        $stocks = Stock::with(['product.category', 'product.brand', 'warehouse'])
            ->when($request->warehouse_id, fn ($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->search, fn ($q, $s) => $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$s}%")))
            ->paginate(30)
            ->withQueryString();

        $summary = [
            'total_products'    => Product::count(),
            'active_products'   => Product::where('is_active', true)->count(),
            'total_stock_value' => DB::table('eshop_stocks')
                ->join('eshop_products', 'eshop_stocks.product_id', '=', 'eshop_products.id')
                ->sum(DB::raw('eshop_stocks.quantity * eshop_products.cost_price')),
            'low_stock_count'   => Product::lowStock()->count(),
            'expired_count'     => Product::expired()->count(),
        ];

        return view('eshop360::reports.inventory', compact('stocks', 'summary'));
    }

    public function products(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $products = Product::select('eshop_products.*')
            ->leftJoin('eshop_order_items', 'eshop_products.id', '=', 'eshop_order_items.product_id')
            ->leftJoin('eshop_orders', function ($join) use ($dateFrom, $dateTo) {
                $join->on('eshop_order_items.order_id', '=', 'eshop_orders.id')
                    ->where('eshop_orders.status', 'completed')
                    ->whereBetween('eshop_orders.created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->selectRaw('COALESCE(SUM(eshop_order_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(eshop_order_items.total), 0) as total_revenue')
            ->groupBy('eshop_products.id')
            ->when($request->search, fn ($q, $s) => $q->where('eshop_products.name', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $c) => $q->where('eshop_products.category_id', $c))
            ->when($request->brand_id, fn ($q, $b) => $q->where('eshop_products.brand_id', $b))
            ->when($request->sort === 'revenue', fn ($q) => $q->orderByDesc('total_revenue'), fn ($q) => $q->orderByDesc('total_sold'))
            ->paginate(30)
            ->withQueryString();

        return view('eshop360::reports.products', compact('products', 'dateFrom', 'dateTo'));
    }

    public function bestSellers(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();
        $limit = $request->integer('limit', 20);

        $bestSellers = OrderItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_qty')
            ->selectRaw('SUM(total) as total_revenue')
            ->selectRaw('COUNT(DISTINCT order_id) as order_count')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->with('product:id,name,sku,image,price,category_id,brand_id', 'product.category:id,name', 'product.brand:id,name')
            ->get();

        return view('eshop360::reports.best-sellers', compact('bestSellers', 'dateFrom', 'dateTo', 'limit'));
    }

    public function stockHistory(Request $request)
    {
        $movements = StockMovement::with(['product', 'warehouse', 'store', 'performer'])
            ->when($request->product_id, fn ($q, $p) => $q->where('product_id', $p))
            ->when($request->warehouse_id, fn ($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->search, fn ($q, $s) => $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$s}%")))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('eshop360::inventory.stocks.history', compact('movements'));
    }

    public function soldStock(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $soldItems = OrderItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->selectRaw('SUM(total) as total_revenue')
            ->selectRaw('AVG(unit_price) as avg_price')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->groupBy('product_id')
            ->when($request->search, fn ($q, $s) => $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$s}%")))
            ->orderByDesc('total_sold')
            ->with('product:id,name,sku,cost_price,price')
            ->paginate(30)
            ->withQueryString();

        $totalSold = OrderItem::whereHas('order', function ($q) use ($dateFrom, $dateTo) {
            $q->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        })->sum('quantity');

        $totalRevenue = OrderItem::whereHas('order', function ($q) use ($dateFrom, $dateTo) {
            $q->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        })->sum('total');

        return view('eshop360::inventory.stocks.sold', compact('soldItems', 'totalSold', 'totalRevenue', 'dateFrom', 'dateTo'));
    }

    public function customerReport(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfYear()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $customers = Customer::select('eshop_customers.*')
            ->leftJoin('eshop_orders', function ($join) use ($dateFrom, $dateTo) {
                $join->on('eshop_customers.id', '=', 'eshop_orders.customer_id')
                    ->where('eshop_orders.status', 'completed')
                    ->whereBetween('eshop_orders.created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->selectRaw('COALESCE(SUM(eshop_orders.total), 0) as total_spent')
            ->selectRaw('COALESCE(COUNT(eshop_orders.id), 0) as order_count')
            ->selectRaw('COALESCE(AVG(eshop_orders.total), 0) as avg_order_value')
            ->groupBy('eshop_customers.id')
            ->when($request->search, fn ($q, $s) => $q->where('eshop_customers.name', 'like', "%{$s}%"))
            ->when($request->sort === 'orders', fn ($q) => $q->orderByDesc('order_count'), fn ($q) => $q->orderByDesc('total_spent'))
            ->paginate(30)
            ->withQueryString();

        $summary = [
            'total_customers'  => Customer::count(),
            'active_customers' => Customer::whereHas('orders', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })->count(),
            'new_customers'    => Customer::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
        ];

        return view('eshop360::customers.report', compact('customers', 'summary', 'dateFrom', 'dateTo'));
    }

    public function purchaseReport(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $purchasesBySupplier = PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('supplier_name')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total) as total')
            ->selectRaw('SUM(paid_amount) as paid')
            ->selectRaw('SUM(due_amount) as due')
            ->groupBy('supplier_name')
            ->orderByDesc('total')
            ->get();

        $purchasesByMonth = PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
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

        $summary = [
            'total_purchased' => PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('total'),
            'total_paid'      => PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('paid_amount'),
            'total_due'       => PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('due_amount'),
            'order_count'     => PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
        ];

        return view('eshop360::purchases.report', compact('purchasesBySupplier', 'purchasesByMonth', 'summary', 'dateFrom', 'dateTo'));
    }

    public function invoiceReport(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $invoicesByStatus = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get();

        $invoicesByMonth = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total'),
                DB::raw('SUM(paid_amount) as paid'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $summary = [
            'total_invoiced' => Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('total'),
            'total_paid'     => Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('paid_amount'),
            'total_due'      => Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('due_amount'),
            'overdue_count'  => Invoice::where('status', '!=', 'paid')
                ->where('due_date', '<', now())
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
        ];

        return view('eshop360::invoices.report', compact('invoicesByStatus', 'invoicesByMonth', 'summary', 'dateFrom', 'dateTo'));
    }
}
