<?php

namespace Modules\Eshop360\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Services\ReportService;
use Modules\Eshop360\Services\ChargesService;
use Modules\Eshop360\Services\MarginService;
use Modules\Eshop360\Services\OnlineOrderService;

class ApiController extends Controller
{
    // ─── Products ────────────────────────────────────

    public function products(Request $request): JsonResponse
    {
        $query = Product::query();
        if ($request->has('instance_id')) $query->where('instance_id', $request->instance_id);
        if ($request->has('category_id')) $query->where('category_id', $request->category_id);
        if ($request->has('brand_id')) $query->where('brand_id', $request->brand_id);
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }
        return response()->json($query->with('category', 'brand')->paginate($request->get('per_page', 20)));
    }

    public function productShow(int $id): JsonResponse
    {
        return response()->json(Product::with('category', 'brand', 'stocks', 'variations')->findOrFail($id));
    }

    public function productStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:eshop_categories,id',
            'brand_id' => 'nullable|exists:eshop_brands,id',
        ]);
        $validated['created_by'] = auth()->id();
        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function productUpdate(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->only(['name', 'sku', 'price', 'cost_price', 'category_id', 'brand_id', 'is_active']));
        return response()->json($product);
    }

    public function productDestroy(int $id): JsonResponse
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    // ─── Stock ───────────────────────────────────────

    public function stock(Request $request): JsonResponse
    {
        $query = Stock::with('product', 'warehouse');
        if ($request->has('instance_id')) $query->where('instance_id', $request->instance_id);
        if ($request->has('warehouse_id')) $query->where('warehouse_id', $request->warehouse_id);
        return response()->json($query->get());
    }

    public function stockMovement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'warehouse_id' => 'required|exists:eshop_warehouses,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $stockService = app(\Modules\Eshop360\Services\StockService::class);
        $product = Product::findOrFail($validated['product_id']);
        $stockService->adjustStock($product, $validated['warehouse_id'], $validated['quantity'], $validated['type'], $validated['notes'], auth()->id());

        return response()->json(['message' => 'Stock movement recorded']);
    }

    // ─── Clients ─────────────────────────────────────

    public function clients(Request $request): JsonResponse
    {
        $query = Customer::query();
        if ($request->has('instance_id')) $query->where('instance_id', $request->instance_id);
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    public function clientShow(int $id): JsonResponse
    {
        return response()->json(Customer::with('orders', 'invoices')->findOrFail($id));
    }

    public function clientStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);
        return response()->json(Customer::create($validated), 201);
    }

    // ─── Sales ───────────────────────────────────────

    public function sales(Request $request): JsonResponse
    {
        $query = Order::with('customer', 'items');
        if ($request->has('instance_id')) $query->where('instance_id', $request->instance_id);
        if ($request->has('status')) $query->where('status', $request->status);
        return response()->json($query->latest()->paginate($request->get('per_page', 20)));
    }

    public function saleShow(int $id): JsonResponse
    {
        return response()->json(Order::with('customer', 'items', 'payments', 'invoice')->findOrFail($id));
    }

    public function saleStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'customer_id' => 'nullable|exists:eshop_customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        $orderService = app(\Modules\Eshop360\Services\OrderService::class);
        $subtotal = collect($validated['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

        $order = Order::create([
            'instance_id' => $validated['instance_id'],
            'customer_id' => $validated['customer_id'] ?? null,
            'order_number' => $orderService->generateOrderNumber(),
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'paid_amount' => $subtotal,
            'due_amount' => 0,
            'source' => 'api',
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'sku' => $product->sku ?? '',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json($order->load('items'), 201);
    }

    // ─── Purchases ───────────────────────────────────

    public function purchases(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with('items');
        if ($request->has('instance_id')) $query->where('instance_id', $request->instance_id);
        return response()->json($query->latest()->paginate($request->get('per_page', 20)));
    }

    // ─── Reports ─────────────────────────────────────

    public function reportOverview(Request $request): JsonResponse
    {
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(ReportService::class);
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        return response()->json($service->overview($request->instance_id, $from, $to));
    }

    public function reportProfitLoss(Request $request): JsonResponse
    {
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(\Modules\Eshop360\Services\FinanceService::class);
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        return response()->json($service->profitAndLoss($request->instance_id, $from, $to));
    }

    public function reportStock(Request $request): JsonResponse
    {
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(ReportService::class);
        return response()->json($service->stockReport($request->instance_id, $request->get('warehouse_id')));
    }

    // ─── Online Orders ──────────────────────────────

    public function onlineOrders(Request $request): JsonResponse
    {
        $query = OnlineOrder::with('customer', 'items.product');
        if ($request->has('instance_id')) $query->where('instance_id', $request->instance_id);
        return response()->json($query->latest()->paginate($request->get('per_page', 20)));
    }

    public function onlineOrderShow(int $id): JsonResponse
    {
        return response()->json(OnlineOrder::with('customer', 'items.product')->findOrFail($id));
    }

    public function onlineOrderStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'customer_id' => 'required|exists:eshop_customers,id',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
            'is_codifarm' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $service = app(OnlineOrderService::class);
        $order = $service->createOrder(
            $validated['instance_id'],
            $validated['customer_id'],
            $validated['items'],
            $validated['delivery_address'] ?? null,
            $validated['notes'] ?? null,
            $validated['channel_id'] ?? null,
            (bool) ($validated['is_codifarm'] ?? false),
        );

        return response()->json($order, 201);
    }

    public function onlineOrderUpdateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['status' => 'required|string']);
        $order = OnlineOrder::findOrFail($id);
        $service = app(OnlineOrderService::class);

        try {
            $service->advanceStatus($order, $validated['status']);
            return response()->json($order->fresh());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ─── Dashboard ───────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $request->validate(['instance_id' => 'required|integer']);
        $reportService = app(ReportService::class);
        $chargesService = app(ChargesService::class);

        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();

        return response()->json([
            'overview' => $reportService->overview($request->instance_id, $from, $to),
            'charges' => $chargesService->getDashboardData($request->instance_id),
        ]);
    }

    // ─── Channel Margins ────────────────────────────

    public function channelMargins(Request $request, int $channelId): JsonResponse
    {
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(MarginService::class);
        $from = $request->get('from', now()->startOfMonth()->toDateTimeString());
        $to = $request->get('to', now()->toDateTimeString());
        return response()->json($service->getMarginSummary($request->instance_id, $from, $to, $channelId));
    }

    // ─── Charges Realtime ────────────────────────────

    public function chargesRealtime(Request $request): JsonResponse
    {
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(ChargesService::class);
        return response()->json($service->getDashboardData($request->instance_id));
    }
}
