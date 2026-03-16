<?php

namespace Modules\Eshop360\Http\Controllers\Customer;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Order;

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

        return view('eshop360::customers.show', compact('customer', 'orders', 'stats'));
    }

    public function update(Request $request, string $slug, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
            'phone'     => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:500',
            'city'      => 'nullable|string|max:100',
            'country'   => 'nullable|string|max:100',
            'is_active' => 'boolean',
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
