<?php

namespace Modules\Eshop360\Http\Controllers\Customer;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\CustomerTransaction;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Services\FinanceService;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%"))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->city, fn ($q, $c) => $q->where('city', $c))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::customers.index', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'city'    => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $instance = CurrentInstance::get();
        $validated['instance_id'] = $instance?->id;
        $validated['code'] = 'CUS-' . str_pad(Customer::where('instance_id', $instance?->id)->count() + 1, 6, '0', STR_PAD_LEFT);

        Customer::create($validated);

        return redirect()->route('eshop360.customers.index', $request->route('slug'))
            ->with('success', __('Customer created successfully.'));
    }

    public function show(string $slug, Customer $customer)
    {
        $customer->load('user');

        $orders = Order::where('customer_id', $customer->id)
            ->with('items')
            ->latest()
            ->paginate(15);

        $stats = [
            'total_orders'  => Order::where('customer_id', $customer->id)->count(),
            'total_spent'   => Order::where('customer_id', $customer->id)->where('status', 'completed')->sum('total'),
            'total_due'     => Order::where('customer_id', $customer->id)->where('payment_status', '!=', 'paid')->sum('due_amount'),
            'last_order_at' => Order::where('customer_id', $customer->id)->latest()->value('created_at'),
        ];

        $walletTransactions = CustomerTransaction::where('customer_id', $customer->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('eshop360::customers.show', compact('customer', 'orders', 'stats', 'walletTransactions'));
    }

    public function walletTopup(Request $request, string $slug, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes'  => 'nullable|string|max:500',
        ]);

        $amount = (float) $validated['amount'];

        app(FinanceService::class)->creditWallet(
            $customer,
            $amount,
            $validated['notes'] ?? null,
        );

        return redirect()->route('eshop360.customers.show', [$slug, $customer])
            ->with('success', __('Portefeuille recharge de :amount.', ['amount' => number_format($amount, 2)]));
    }

    public function update(Request $request, string $slug, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:30',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'is_active'    => 'boolean',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update($validated);

        return redirect()->route('eshop360.customers.show', [$slug, $customer])
            ->with('success', __('Customer updated successfully.'));
    }

    public function destroy(string $slug, Customer $customer): RedirectResponse
    {
        $orderCount = Order::where('customer_id', $customer->id)->count();

        if ($orderCount > 0) {
            return redirect()->route('eshop360.customers.index', $slug)
                ->with('error', __('Cannot delete customer with :count orders.', ['count' => $orderCount]));
        }

        $customer->delete();

        return redirect()->route('eshop360.customers.index', $slug)
            ->with('success', __('Customer deleted successfully.'));
    }

    public function stats(Request $request)
    {
        $instance = CurrentInstance::get();
        $instanceId = $instance?->id ?? 0;
        $from = $request->input('date_from', now()->startOfYear()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        // Global customer KPIs
        $totalCustomers = Customer::withoutGlobalScopes()->where('instance_id', $instanceId)->count();
        $activeCustomers = Customer::withoutGlobalScopes()->where('instance_id', $instanceId)->where('is_active', true)->count();
        $withAccount = Customer::withoutGlobalScopes()->where('instance_id', $instanceId)->whereNotNull('user_id')->count();
        $newCustomers = Customer::withoutGlobalScopes()->where('instance_id', $instanceId)
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->count();

        // Financial KPIs
        $financialStats = DB::table('eshop_orders')
            ->where('instance_id', $instanceId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(due_amount), 0) as total_due,
                COUNT(DISTINCT customer_id) as buying_customers,
                COUNT(*) as total_orders
            ')->first();

        // Top 30 customers by revenue
        $topCustomers = DB::table('eshop_orders')
            ->join('eshop_customers', 'eshop_orders.customer_id', '=', 'eshop_customers.id')
            ->where('eshop_orders.instance_id', $instanceId)
            ->where('eshop_orders.status', 'completed')
            ->whereBetween('eshop_orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('
                eshop_customers.id, eshop_customers.name, eshop_customers.code, eshop_customers.email,
                eshop_customers.wallet_balance, eshop_customers.credit_limit,
                COUNT(*) as order_count,
                SUM(eshop_orders.total) as revenue,
                SUM(eshop_orders.paid_amount) as paid,
                SUM(eshop_orders.due_amount) as due,
                AVG(eshop_orders.total) as avg_order,
                MAX(eshop_orders.created_at) as last_order_at
            ')
            ->groupBy('eshop_customers.id', 'eshop_customers.name', 'eshop_customers.code',
                       'eshop_customers.email', 'eshop_customers.wallet_balance', 'eshop_customers.credit_limit')
            ->orderByDesc('revenue')
            ->limit(30)
            ->get();

        // Customers by store
        $byStore = DB::table('eshop_orders')
            ->leftJoin('eshop_stores', 'eshop_orders.store_id', '=', 'eshop_stores.id')
            ->where('eshop_orders.instance_id', $instanceId)
            ->where('eshop_orders.status', 'completed')
            ->whereBetween('eshop_orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('COALESCE(eshop_stores.name, "N/A") as store_name, COUNT(DISTINCT customer_id) as customers, COUNT(*) as orders, SUM(eshop_orders.total) as revenue')
            ->groupBy('eshop_orders.store_id', 'eshop_stores.name')
            ->orderByDesc('revenue')
            ->get();

        // Customers by channel
        $byChannel = DB::table('eshop_orders')
            ->leftJoin('eshop_distribution_channels', 'eshop_orders.channel_id', '=', 'eshop_distribution_channels.id')
            ->where('eshop_orders.instance_id', $instanceId)
            ->where('eshop_orders.status', 'completed')
            ->whereBetween('eshop_orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereNotNull('eshop_orders.customer_id')
            ->selectRaw('COALESCE(eshop_distribution_channels.name, "Direct") as channel_name, COUNT(DISTINCT customer_id) as customers, COUNT(*) as orders, SUM(eshop_orders.total) as revenue')
            ->groupBy('eshop_orders.channel_id', 'eshop_distribution_channels.name')
            ->orderByDesc('revenue')
            ->get();

        // Monthly new customers trend (12 months)
        $monthlyNewCustomers = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        // Top products bought by customers (what customers buy most)
        $topProductsBought = DB::table('eshop_order_items')
            ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
            ->leftJoin('eshop_products', 'eshop_order_items.product_id', '=', 'eshop_products.id')
            ->where('eshop_orders.instance_id', $instanceId)
            ->where('eshop_orders.status', 'completed')
            ->whereNotNull('eshop_orders.customer_id')
            ->whereBetween('eshop_orders.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('
                COALESCE(eshop_products.name, eshop_order_items.product_name) as product_name,
                eshop_products.sku, eshop_products.cost_price, eshop_products.purchase_price_factory,
                SUM(eshop_order_items.quantity) as qty, SUM(eshop_order_items.total) as revenue,
                COUNT(DISTINCT eshop_orders.customer_id) as unique_buyers
            ')
            ->groupBy('eshop_order_items.product_id', 'eshop_products.name', 'eshop_order_items.product_name',
                       'eshop_products.sku', 'eshop_products.cost_price', 'eshop_products.purchase_price_factory')
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();

        // Charges for coverage
        $chargesService = app(\Modules\Eshop360\Services\ChargesService::class);
        $monthlyCharges = $chargesService->getTotalCostPerSecond($instanceId) * \Modules\Eshop360\Services\ChargesService::SECONDS_PER_MONTH;

        // Customer settings
        $customerSettings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('customer');

        return view('eshop360::customers.stats', compact(
            'totalCustomers', 'activeCustomers', 'withAccount', 'newCustomers',
            'financialStats', 'topCustomers', 'byStore', 'byChannel',
            'monthlyNewCustomers', 'topProductsBought', 'monthlyCharges',
            'customerSettings', 'from', 'to'
        ));
    }

    public function createUserAccount(Request $request, string $slug, Customer $customer): \Illuminate\Http\RedirectResponse
    {
        $settings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('customer');
        $instance = CurrentInstance::get();

        // Check if customer already has an account
        if ($customer->user_id && !($settings['allow_multi_user_accounts'] ?? false)) {
            return redirect()->route('eshop360.customers.show', [$slug, $customer])
                ->with('error', __('Ce client a deja un compte utilisateur.'));
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
        ]);

        $user = \App\Models\User::create([
            'full_name' => $customer->name,
            'email' => $validated['email'],
            'password' => $validated['password'] ?? 'password',
            'is_active' => true,
            'is_blocked' => false,
        ]);

        // Link to instance
        \Illuminate\Support\Facades\DB::connection('system')->table('instance_user')->updateOrInsert(
            ['user_id' => $user->id, 'instance_id' => $instance->id],
            ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
        );

        // Assign 'user' role for portal access
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($instance->id);
        if (! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        // Link customer to user
        $customer->update(['user_id' => $user->id]);

        // Update customer email if empty
        if (empty($customer->email)) {
            $customer->update(['email' => $validated['email']]);
        }

        return redirect()->route('eshop360.customers.show', [$slug, $customer])
            ->with('success', __('Compte utilisateur cree avec succes. Email: :email', ['email' => $validated['email']]));
    }

    public function report(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfYear()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $topCustomers = Customer::select('eshop_customers.*')
            ->join('eshop_orders', 'eshop_customers.id', '=', 'eshop_orders.customer_id')
            ->where('eshop_orders.status', 'completed')
            ->whereBetween('eshop_orders.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('eshop_customers.id')
            ->selectRaw('SUM(eshop_orders.total) as total_spent')
            ->selectRaw('COUNT(eshop_orders.id) as order_count')
            ->selectRaw('AVG(eshop_orders.total) as avg_order_value')
            ->orderByDesc('total_spent')
            ->paginate(30)
            ->withQueryString();

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::whereHas('orders', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        })->count();

        $newCustomers = Customer::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count();

        return view('eshop360::customers.report', compact(
            'topCustomers', 'totalCustomers', 'activeCustomers', 'newCustomers', 'dateFrom', 'dateTo'
        ));
    }

    public function dueReport(Request $request)
    {
        $customersWithDue = Customer::select('eshop_customers.*')
            ->join('eshop_orders', 'eshop_customers.id', '=', 'eshop_orders.customer_id')
            ->where('eshop_orders.due_amount', '>', 0)
            ->where('eshop_orders.payment_status', '!=', 'paid')
            ->groupBy('eshop_customers.id')
            ->selectRaw('SUM(eshop_orders.due_amount) as total_due')
            ->selectRaw('COUNT(eshop_orders.id) as unpaid_orders')
            ->selectRaw('MIN(eshop_orders.created_at) as oldest_due_date')
            ->when($request->search, fn ($q, $s) => $q->where('eshop_customers.name', 'like', "%{$s}%"))
            ->when($request->min_due, fn ($q, $m) => $q->havingRaw('SUM(eshop_orders.due_amount) >= ?', [$m]))
            ->orderByDesc('total_due')
            ->paginate(30)
            ->withQueryString();

        $totalDueAmount = Order::where('payment_status', '!=', 'paid')->sum('due_amount');
        $customersDueCount = Order::where('due_amount', '>', 0)->distinct('customer_id')->count('customer_id');

        return view('eshop360::customers.due-report', compact('customersWithDue', 'totalDueAmount', 'customersDueCount'));
    }
}
