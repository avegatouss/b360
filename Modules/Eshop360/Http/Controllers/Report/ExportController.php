<?php

namespace Modules\Eshop360\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Expense;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Services\ExportService;

class ExportController extends Controller
{
    public function __construct(
        private ExportService $export,
    ) {}

    public function products(Request $request)
    {
        $instance = CurrentInstance::get();
        $products = Product::where('instance_id', $instance->id)
            ->with(['category', 'brand', 'stocks'])
            ->when($request->category_id, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->brand_id, fn ($query, $brandId) => $query->where('brand_id', $brandId))
            ->orderBy('name')
            ->get();

        return $this->export->products($products, $this->resolveFormat($request));
    }

    public function sales(Request $request)
    {
        $instance = CurrentInstance::get();
        $query = Order::where('instance_id', $instance->id)
            ->with(['customer', 'channel'])
            ->when($request->date_from, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($request->customer_id, fn ($query, $customerId) => $query->where('customer_id', $customerId))
            ->latest();

        return $this->export->sales($query->get(), $this->resolveFormat($request));
    }

    public function invoices(Request $request)
    {
        $instance = CurrentInstance::get();
        $query = Invoice::where('instance_id', $instance->id)
            ->with('customer')
            ->when($request->date_from, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest();

        return $this->export->invoices($query->get(), $this->resolveFormat($request));
    }

    public function customers(Request $request)
    {
        $instance = CurrentInstance::get();
        $query = Customer::where('instance_id', $instance->id)
            ->withSum('orders as total_orders_amount', 'total')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name');

        return $this->export->customers($query->get(), $this->resolveFormat($request));
    }

    public function suppliers(Request $request)
    {
        $instance = CurrentInstance::get();
        $suppliers = Supplier::where('instance_id', $instance->id)
            ->withSum('purchaseOrders as total_purchases_amount', 'total')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return $this->export->suppliers($suppliers, $this->resolveFormat($request));
    }

    public function stock(Request $request)
    {
        $instance = CurrentInstance::get();
        $stocks = Stock::where('instance_id', $instance->id)
            ->with(['product', 'warehouse'])
            ->when($request->warehouse_id, fn ($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
            ->get();

        return $this->export->stock($stocks, $this->resolveFormat($request));
    }

    public function purchases(Request $request)
    {
        $instance = CurrentInstance::get();
        $purchases = PurchaseOrder::where('instance_id', $instance->id)
            ->with('supplier')
            ->when($request->date_from, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->get();

        return $this->export->purchases($purchases, $this->resolveFormat($request));
    }

    public function expenses(Request $request)
    {
        $instance = CurrentInstance::get();
        $expenses = Expense::where('instance_id', $instance->id)
            ->with(['category', 'account'])
            ->when($request->date_from, fn ($query, $date) => $query->whereDate('date', '>=', $date))
            ->when($request->date_to, fn ($query, $date) => $query->whereDate('date', '<=', $date))
            ->latest('date')
            ->get();

        return $this->export->expenses($expenses, $this->resolveFormat($request));
    }

    private function resolveFormat(Request $request): string
    {
        $format = strtolower((string) $request->query('format', 'csv'));

        return in_array($format, ['csv', 'xlsx'], true) ? $format : 'csv';
    }
}
