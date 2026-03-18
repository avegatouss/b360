<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Expense;
use Modules\Eshop360\Models\Income;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Payment;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockMovement;
use Modules\Eshop360\Models\Supplier;

class ReportService
{
    private const TTL_SHORT  = 300;   // 5 min — overview, posOverview
    private const TTL_MEDIUM = 900;   // 15 min — salesByCategory, salesByProduct, cashbook
    private const TTL_LONG   = 3600;  // 1h — stockReport, taxReport, dues, commissions, monthly

    public function overview(int $instanceId, string $from, string $to): array
    {
        return $this->cached("report:overview:{$instanceId}:{$from}:{$to}", self::TTL_SHORT, $instanceId, function () use ($instanceId, $from, $to) {
            [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);
            [$fromDate, $toDate] = $this->normalizeDateRange($from, $to);

            $sales = Order::where('instance_id', $instanceId)
                ->whereBetween('created_at', [$fromDateTime, $toDateTime])
                ->where('status', '!=', 'cancelled');
            $purchases = PurchaseOrder::where('instance_id', $instanceId)
                ->whereBetween('created_at', [$fromDateTime, $toDateTime])
                ->where('status', '!=', 'cancelled');
            $expenses = Expense::where('instance_id', $instanceId)
                ->whereDate('date', '>=', $fromDate)
                ->whereDate('date', '<=', $toDate);
            $incomes = Income::where('instance_id', $instanceId)
                ->whereDate('date', '>=', $fromDate)
                ->whereDate('date', '<=', $toDate);

            $salesTotal = round((float) (clone $sales)->sum('total'), 2);
            $purchaseTotal = round((float) (clone $purchases)->sum('total'), 2);
            $expenseTotal = round((float) (clone $expenses)->sum('amount'), 2);
            $incomeTotal = round((float) (clone $incomes)->sum('amount'), 2);
            $totalExpenses = round($purchaseTotal + $expenseTotal, 2);
            $netProfit = round(($salesTotal + $incomeTotal) - $totalExpenses, 2);

            $topProducts = DB::table('eshop_order_items as oi')
                ->join('eshop_orders as o', 'o.id', '=', 'oi.order_id')
                ->where('o.instance_id', $instanceId)
                ->whereBetween('o.created_at', [$fromDateTime, $toDateTime])
                ->where('o.status', '!=', 'cancelled')
                ->select('oi.product_name', DB::raw('SUM(oi.quantity) as total_qty'), DB::raw('SUM(oi.total) as total_amount'))
                ->groupBy('oi.product_name')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get()
                ->map(fn ($row): array => [
                    'name' => (string) ($row->product_name ?? '---'),
                    'quantity' => (int) ($row->total_qty ?? 0),
                    'revenue' => round((float) ($row->total_amount ?? 0), 2),
                ])
                ->all();

            $topCustomers = DB::table('eshop_orders as o')
                ->join('eshop_customers as c', 'c.id', '=', 'o.customer_id')
                ->where('o.instance_id', $instanceId)
                ->whereBetween('o.created_at', [$fromDateTime, $toDateTime])
                ->where('o.status', '!=', 'cancelled')
                ->select('c.name', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(o.total) as total_amount'))
                ->groupBy('c.name')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get()
                ->map(fn ($row): array => [
                    'name' => (string) ($row->name ?? '---'),
                    'orders' => (int) ($row->orders_count ?? 0),
                    'total' => round((float) ($row->total_amount ?? 0), 2),
                ])
                ->all();

            return [
                'total_sales' => $salesTotal,
                'total_purchases' => $purchaseTotal,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'total_orders' => (clone $sales)->count(),
                'total_customers' => Customer::where('instance_id', $instanceId)->count(),
                'total_products' => Product::where('instance_id', $instanceId)->count(),
                'revenue_collected' => round((float) (clone $sales)->sum('paid_amount'), 2),
                'outstanding_dues' => round((float) (clone $sales)->sum('due_amount'), 2),
                'top_products' => $topProducts,
                'top_customers' => $topCustomers,
            ];
        });
    }

    public function salesByCategory(int $instanceId, string $from, string $to): array
    {
        return $this->cached("report:salesByCategory:{$instanceId}:{$from}:{$to}", self::TTL_MEDIUM, $instanceId, function () use ($instanceId, $from, $to) {
            [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);

            return DB::table('eshop_order_items as oi')
                ->join('eshop_orders as o', 'o.id', '=', 'oi.order_id')
                ->join('eshop_products as p', 'p.id', '=', 'oi.product_id')
                ->leftJoin('eshop_categories as c', 'c.id', '=', 'p.category_id')
                ->where('o.instance_id', $instanceId)
                ->whereBetween('o.created_at', [$fromDateTime, $toDateTime])
                ->where('o.status', '!=', 'cancelled')
                ->select('c.name as category', DB::raw('SUM(oi.quantity) as total_qty'), DB::raw('SUM(oi.total) as total_amount'))
                ->groupBy('c.name')
                ->orderByDesc('total_amount')
                ->get()
                ->map(fn ($row): array => [
                    'category' => (string) ($row->category ?? __('eshop::eshop.uncategorized')),
                    'name' => (string) ($row->category ?? __('eshop::eshop.uncategorized')),
                    'quantity' => (int) ($row->total_qty ?? 0),
                    'revenue' => round((float) ($row->total_amount ?? 0), 2),
                ])
                ->all();
        });
    }

    public function salesByProduct(int $instanceId, string $from, string $to): array
    {
        return $this->cached("report:salesByProduct:{$instanceId}:{$from}:{$to}", self::TTL_MEDIUM, $instanceId, function () use ($instanceId, $from, $to) {
            [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);

            return DB::table('eshop_order_items as oi')
                ->join('eshop_orders as o', 'o.id', '=', 'oi.order_id')
                ->where('o.instance_id', $instanceId)
                ->whereBetween('o.created_at', [$fromDateTime, $toDateTime])
                ->where('o.status', '!=', 'cancelled')
                ->select('oi.product_name', 'oi.sku', DB::raw('SUM(oi.quantity) as total_qty'), DB::raw('SUM(oi.total) as total_amount'))
                ->groupBy('oi.product_name', 'oi.sku')
                ->orderByDesc('total_amount')
                ->get()
                ->map(fn ($row): array => [
                    'product' => (string) ($row->product_name ?? '---'),
                    'name' => (string) ($row->product_name ?? '---'),
                    'sku' => (string) ($row->sku ?? ''),
                    'quantity' => (int) ($row->total_qty ?? 0),
                    'revenue' => round((float) ($row->total_amount ?? 0), 2),
                ])
                ->all();
        });
    }

    public function cashbook(int $instanceId, string $from, string $to): array
    {
        return $this->cached("report:cashbook:{$instanceId}:{$from}:{$to}", self::TTL_MEDIUM, $instanceId, function () use ($instanceId, $from, $to) {
            [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);
            [$fromDate, $toDate] = $this->normalizeDateRange($from, $to);

            $payments = Payment::with('payable')
                ->where('instance_id', $instanceId)
                ->whereBetween('created_at', [$fromDateTime, $toDateTime])
                ->where('status', 'completed')
                ->get();
            $expenses = Expense::with(['category', 'account'])
                ->where('instance_id', $instanceId)
                ->whereDate('date', '>=', $fromDate)
                ->whereDate('date', '<=', $toDate)
                ->get();

            $entries = $payments
                ->map(fn (Payment $payment): array => [
                    'sort_at' => $payment->created_at?->timestamp ?? 0,
                    'date' => $payment->created_at?->format('d/m/Y H:i') ?? '',
                    'reference' => $payment->reference ?? $payment->gateway_reference ?? 'PAY-' . $payment->id,
                    'description' => $this->paymentDescription($payment),
                    'method' => $payment->method ?? __('eshop::eshop.unknown'),
                    'credit' => round((float) $payment->amount, 2),
                    'debit' => 0.0,
                ])
                ->concat($expenses->map(fn (Expense $expense): array => [
                    'sort_at' => Carbon::parse($expense->date ?? $expense->created_at)->timestamp,
                    'date' => Carbon::parse($expense->date ?? $expense->created_at)->format('d/m/Y'),
                    'reference' => 'EXP-' . $expense->id,
                    'description' => (string) ($expense->description ?? $expense->category?->name ?? __('eshop::eshop.expense')),
                    'method' => $expense->account?->name ?? __('eshop::eshop.expense'),
                    'credit' => 0.0,
                    'debit' => round((float) $expense->amount, 2),
                ]))
                ->sortBy('sort_at')
                ->values();

            $runningBalance = 0.0;
            $entries = $entries->map(function (array $entry) use (&$runningBalance): array {
                $runningBalance += ($entry['credit'] - $entry['debit']);
                $entry['balance'] = round($runningBalance, 2);
                unset($entry['sort_at']);
                return $entry;
            })->all();

            $paymentsByMethod = $payments
                ->groupBy(fn (Payment $payment): string => (string) ($payment->method ?? __('eshop::eshop.unknown')))
                ->map(fn ($group): float => round((float) $group->sum('amount'), 2))
                ->toArray();

            $expensesByCategory = $expenses
                ->groupBy(fn (Expense $expense): string => (string) ($expense->category?->name ?? __('eshop::eshop.uncategorized')))
                ->map(fn ($group): float => round((float) $group->sum('amount'), 2))
                ->toArray();

            $totalReceived = round((float) $payments->sum('amount'), 2);
            $totalExpenses = round((float) $expenses->sum('amount'), 2);

            return [
                'total_received' => $totalReceived,
                'total_expenses' => $totalExpenses,
                'payments_by_method' => $paymentsByMethod,
                'by_method' => $paymentsByMethod,
                'expenses_by_category' => $expensesByCategory,
                'entries' => $entries,
                'total_credit' => $totalReceived,
                'total_debit' => $totalExpenses,
                'closing_balance' => round($totalReceived - $totalExpenses, 2),
            ];
        });
    }

    public function stockReport(int $instanceId, ?int $warehouseId = null): array
    {
        $whKey = $warehouseId ?? 'all';

        return $this->cached("report:stockReport:{$instanceId}:{$whKey}", self::TTL_LONG, $instanceId, function () use ($instanceId, $warehouseId) {
            $stocks = Stock::where('instance_id', $instanceId)
                ->with(['product', 'warehouse'])
                ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
                ->get();

            $movementMap = StockMovement::where('instance_id', $instanceId)
                ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
                ->select('product_id', 'warehouse_id', 'type', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('product_id', 'warehouse_id', 'type')
                ->get()
                ->groupBy(fn ($row): string => $this->movementKey((int) $row->product_id, (int) ($row->warehouse_id ?? 0)));

            $items = $stocks->map(function (Stock $stock) use ($movementMap): array {
                $key = $this->movementKey((int) $stock->product_id, (int) ($stock->warehouse_id ?? 0));
                $movements = $movementMap->get($key, collect());
                $unitCost = (float) ($stock->product?->cost_price_real ?? $stock->product?->cost_price ?? 0);
                $minStock = (int) ($stock->product?->stock_alert_quantity ?? $stock->product?->alert_quantity ?? 0);

                return [
                    'product' => (string) ($stock->product?->name ?? '---'),
                    'name' => (string) ($stock->product?->name ?? '---'),
                    'sku' => (string) ($stock->product?->sku ?? ''),
                    'warehouse' => (string) ($stock->warehouse?->name ?? ''),
                    'current_stock' => (int) ($stock->quantity ?? 0),
                    'quantity' => (int) ($stock->quantity ?? 0),
                    'min_stock' => $minStock,
                    'reorder_level' => $minStock,
                    'stock_in' => (int) $movements->whereIn('type', ['in', 'return'])->sum('total_quantity'),
                    'stock_out' => (int) $movements->whereIn('type', ['out', 'transfer'])->sum('total_quantity'),
                    'stock_value' => round(((int) ($stock->quantity ?? 0)) * $unitCost, 2),
                ];
            })->values()->all();

            return [
                'items' => $items,
                'total_items' => count($items),
                'total_quantity' => array_sum(array_column($items, 'quantity')),
                'total_value' => round(array_sum(array_column($items, 'stock_value')), 2),
                'low_stock' => count(array_filter($items, fn (array $row): bool => $row['quantity'] <= $row['min_stock'])),
            ];
        });
    }

    public function taxReport(int $instanceId, string $from, string $to): array
    {
        return $this->cached("report:taxReport:{$instanceId}:{$from}:{$to}", self::TTL_LONG, $instanceId, function () use ($instanceId, $from, $to) {
            [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);

            $orders = Order::where('instance_id', $instanceId)
                ->whereBetween('created_at', [$fromDateTime, $toDateTime])
                ->where('status', '!=', 'cancelled')
                ->get(['subtotal', 'discount_amount', 'tax_amount']);

            $taxableAmount = round((float) $orders->sum(fn (Order $order): float => max(0, (float) $order->subtotal - (float) $order->discount_amount)), 2);
            $salesTax = round((float) $orders->sum('tax_amount'), 2);
            $transactions = $orders->count();
            $rate = $taxableAmount > 0 ? round(($salesTax / $taxableAmount) * 100, 2) : 0.0;

            return [
                'sales_tax_collected' => $salesTax,
                'period' => ['from' => $from, 'to' => $to],
                'taxes' => [[
                    'name' => __('eshop::eshop.sales_tax'),
                    'tax_name' => __('eshop::eshop.sales_tax'),
                    'rate' => $rate,
                    'taxable_amount' => $taxableAmount,
                    'tax_amount' => $salesTax,
                    'transactions' => $transactions,
                ]],
            ];
        });
    }

    public function customerDues(int $instanceId): array
    {
        return $this->cached("report:customerDues:{$instanceId}", self::TTL_LONG, $instanceId, function () use ($instanceId) {
            return Customer::where('instance_id', $instanceId)
                ->whereHas('orders', fn ($query) => $query->where('due_amount', '>', 0))
                ->with(['orders' => fn ($query) => $query->where('due_amount', '>', 0)])
                ->get()
                ->map(function (Customer $customer): array {
                    $orders = $customer->orders;
                    return [
                        'customer' => $customer->name,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'total_invoiced' => round((float) $orders->sum('total'), 2),
                        'total_paid' => round((float) $orders->sum('paid_amount'), 2),
                        'due' => round((float) $orders->sum('due_amount'), 2),
                        'orders_count' => $orders->count(),
                    ];
                })
                ->sortByDesc('due')
                ->values()
                ->all();
        });
    }

    public function supplierDues(int $instanceId): array
    {
        return $this->cached("report:supplierDues:{$instanceId}", self::TTL_LONG, $instanceId, function () use ($instanceId) {
            return Supplier::where('instance_id', $instanceId)
                ->whereHas('purchaseOrders', fn ($query) => $query->where('due_amount', '>', 0))
                ->with(['purchaseOrders' => fn ($query) => $query->where('due_amount', '>', 0)])
                ->get()
                ->map(function (Supplier $supplier): array {
                    $orders = $supplier->purchaseOrders;
                    return [
                        'supplier' => $supplier->name,
                        'name' => $supplier->name,
                        'phone' => $supplier->phone,
                        'total_invoiced' => round((float) $orders->sum('total'), 2),
                        'total_paid' => round((float) $orders->sum('paid_amount'), 2),
                        'due' => round((float) $orders->sum('due_amount'), 2),
                        'orders_count' => $orders->count(),
                    ];
                })
                ->sortByDesc('due')
                ->values()
                ->all();
        });
    }

    public function employeeCommissions(int $instanceId, string $from, string $to): array
    {
        return $this->cached("report:employeeCommissions:{$instanceId}:{$from}:{$to}", self::TTL_LONG, $instanceId, function () use ($instanceId, $from, $to) {
            [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);

            return DB::table('eshop_employee_commissions as ec')
                ->join('eshop_employees as e', 'e.id', '=', 'ec.employee_id')
                ->join('eshop_orders as o', 'o.id', '=', 'ec.order_id')
                ->where('e.instance_id', $instanceId)
                ->whereBetween('ec.created_at', [$fromDateTime, $toDateTime])
                ->select(
                    'e.name', 'e.position',
                    DB::raw('AVG(ec.rate) as avg_rate'),
                    DB::raw('SUM(ec.amount) as total_commission'),
                    DB::raw('COUNT(*) as sales_count'),
                    DB::raw('SUM(o.total) as total_sales')
                )
                ->groupBy('e.name', 'e.position')
                ->orderByDesc('total_commission')
                ->get()
                ->map(fn ($row): array => [
                    'employee' => (string) ($row->name ?? '---'),
                    'name' => (string) ($row->name ?? '---'),
                    'position' => (string) ($row->position ?? ''),
                    'sales_count' => (int) ($row->sales_count ?? 0),
                    'orders' => (int) ($row->sales_count ?? 0),
                    'revenue' => round((float) ($row->total_sales ?? 0), 2),
                    'total_sales' => round((float) ($row->total_sales ?? 0), 2),
                    'rate' => round((float) ($row->avg_rate ?? 0), 2),
                    'commission' => round((float) ($row->total_commission ?? 0), 2),
                ])
                ->all();
        });
    }

    public function posOverview(int $instanceId, string $from, string $to): array
    {
        return $this->cached("report:posOverview:{$instanceId}:{$from}:{$to}", self::TTL_SHORT, $instanceId, function () use ($instanceId, $from, $to) {
            [$fromDateTime, $toDateTime] = $this->normalizeDateTimeRange($from, $to);

            $collection = Order::with(['items', 'cashier'])
                ->where('instance_id', $instanceId)
                ->where('source', 'pos')
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$fromDateTime, $toDateTime])
                ->get();

            $totalRevenue = round((float) $collection->sum('total'), 2);
            $transactions = $collection->count();
            $averageBasket = round((float) ($collection->avg('total') ?? 0), 2);
            $totalItems = (int) $collection->sum(fn (Order $order) => $order->items->sum('quantity'));

            $byCashier = $collection
                ->groupBy(fn (Order $order): string => $order->cashier?->name ?? 'System')
                ->map(function ($group, string $name): array {
                    $revenue = round((float) $group->sum('total'), 2);
                    $count = $group->count();
                    return [
                        'name' => $name,
                        'transactions' => $count,
                        'items' => (int) $group->sum(fn (Order $order) => $order->items->sum('quantity')),
                        'revenue' => $revenue,
                        'average' => round($count > 0 ? $revenue / $count : 0, 2),
                    ];
                })
                ->values()
                ->all();

            $byPaymentMethod = $collection
                ->groupBy(fn (Order $order): string => (string) ($order->payment_method ?? 'unknown'))
                ->map(fn ($group, string $method): array => [
                    'method' => $method,
                    'total' => round((float) $group->sum('total'), 2),
                    'count' => $group->count(),
                ])
                ->values()
                ->all();

            return [
                'total_sales' => $totalRevenue,
                'total_revenue' => $totalRevenue,
                'total_transactions' => $transactions,
                'average_sale' => $averageBasket,
                'average_basket' => $averageBasket,
                'total_items' => $totalItems,
                'by_cashier' => $byCashier,
                'by_payment_method' => $byPaymentMethod,
            ];
        });
    }

    public function monthlyRevenue(int $instanceId, int $year): array
    {
        return $this->cached("report:monthlyRevenue:{$instanceId}:{$year}", self::TTL_LONG, $instanceId, function () use ($instanceId, $year) {
            return Order::query()
                ->where('instance_id', $instanceId)
                ->whereYear('created_at', $year)
                ->where('status', '!=', 'cancelled')
                ->get()
                ->groupBy(fn (Order $order): int => (int) $order->created_at->month)
                ->mapWithKeys(fn ($orders, int $month): array => [
                    $month => [
                        'month' => $month,
                        'revenue' => round((float) $orders->sum('total'), 2),
                        'orders' => $orders->count(),
                        'count' => $orders->count(),
                    ],
                ])
                ->toArray();
        });
    }

    public function monthlyExpenses(int $instanceId, int $year): array
    {
        return $this->cached("report:monthlyExpenses:{$instanceId}:{$year}", self::TTL_LONG, $instanceId, function () use ($instanceId, $year) {
            return Expense::query()
                ->where('instance_id', $instanceId)
                ->whereYear('date', $year)
                ->get()
                ->groupBy(fn (Expense $expense): int => (int) Carbon::parse($expense->date)->month)
                ->mapWithKeys(fn ($expenses, int $month): array => [
                    $month => [
                        'month' => $month,
                        'amount' => round((float) $expenses->sum('amount'), 2),
                        'total' => round((float) $expenses->sum('amount'), 2),
                        'count' => $expenses->count(),
                    ],
                ])
                ->toArray();
        });
    }

    // ─── Cache Helper ────────────────────────────────

    /**
     * Cache a report result and track the key in the instance manifest.
     */
    private function cached(string $key, int $ttl, int $instanceId, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, function () use ($key, $instanceId, $callback) {
            $this->trackCacheKey($key, $instanceId);
            return $callback();
        });
    }

    /**
     * Track a cache key in the instance manifest for later invalidation.
     */
    private function trackCacheKey(string $key, int $instanceId): void
    {
        $manifestKey = "report:manifest:{$instanceId}";
        $keys = Cache::get($manifestKey, []);

        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::put($manifestKey, $keys, 7200);
        }
    }

    // ─── Private Helpers ─────────────────────────────

    private function movementKey(int $productId, int $warehouseId): string
    {
        return $productId . '-' . $warehouseId;
    }

    private function paymentDescription(Payment $payment): string
    {
        $payableReference = $payment->payable?->reference;

        if ($payableReference) {
            return __('eshop::eshop.payment') . ' ' . $payableReference;
        }

        return (string) ($payment->notes ?: __('eshop::eshop.payment'));
    }

    private function normalizeDateTimeRange(string $from, string $to): array
    {
        return [
            Carbon::parse($from)->startOfDay()->toDateTimeString(),
            Carbon::parse($to)->endOfDay()->toDateTimeString(),
        ];
    }

    private function normalizeDateRange(string $from, string $to): array
    {
        return [
            Carbon::parse($from)->toDateString(),
            Carbon::parse($to)->toDateString(),
        ];
    }
}
