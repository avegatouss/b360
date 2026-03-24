<?php

namespace Modules\Eshop360\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Services\ReportService;
use Modules\Eshop360\Services\ChargesService;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\MarginService;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\ProductPricingService;

class ApiController extends Controller
{
    public function __construct(
        private readonly ChannelAccessService $channelAccess,
        private readonly ProductPricingService $pricingService,
    ) {}

    // ─── Products ────────────────────────────────────

    public function products(Request $request): JsonResponse
    {
        $channelId = $this->requestedChannelId($request);
        $query = $this->scopedProductsQuery($channelId);

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

    public function productShow(Request $request, int $id): JsonResponse
    {
        $channelId = $this->requestedChannelId($request);
        $product = $this->scopedProductsQuery($channelId)
            ->with('category', 'brand', 'stocks', 'variations')
            ->findOrFail($id);

        return response()->json($product);
    }

    public function productStore(Request $request): JsonResponse
    {
        $this->ensureHubAdmin();

        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:eshop_categories,id',
            'brand_id' => 'nullable|exists:eshop_brands,id',
        ]);
        $validated['instance_id'] = $this->instanceId();
        $validated['slug'] = Str::slug($validated['name']) ?: Str::lower(Str::random(10));
        $validated['sku'] = $validated['sku'] ?? Str::upper(Str::random(10));
        $validated['cost_price'] = (float) ($validated['cost_price'] ?? 0);
        $validated['tax_rate'] = (float) ($validated['tax_rate'] ?? 0);
        $validated['discount_type'] = $validated['discount_type'] ?? 'none';
        $validated['discount_value'] = (float) ($validated['discount_value'] ?? 0);
        $validated['unit'] = $validated['unit'] ?? 'pc';
        $validated['min_quantity'] = (int) ($validated['min_quantity'] ?? 0);
        $validated['alert_quantity'] = (int) ($validated['alert_quantity'] ?? 10);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['created_by'] = auth()->id();
        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function productUpdate(Request $request, int $id): JsonResponse
    {
        $this->ensureHubAdmin();

        $product = Product::query()
            ->where('instance_id', $this->instanceId())
            ->findOrFail($id);

        $product->update($request->only(['name', 'sku', 'price', 'cost_price', 'category_id', 'brand_id', 'is_active']));
        return response()->json($product);
    }

    public function productDestroy(int $id): JsonResponse
    {
        $this->ensureHubAdmin();

        Product::query()
            ->where('instance_id', $this->instanceId())
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    // ─── Stock ───────────────────────────────────────

    public function stock(Request $request): JsonResponse
    {
        $query = Stock::with('product', 'warehouse')
            ->where('instance_id', $this->instanceId());

        $warehouseId = $request->integer('warehouse_id');

        if ($warehouseId) {
            $this->assertWarehouseAccessible($warehouseId);
            $query->where('warehouse_id', $warehouseId);
        } elseif (!$this->isHubAdmin()) {
            $warehouseIds = $this->accessibleWarehouseIds();
            abort_if($warehouseIds->isEmpty(), 403, 'No accessible warehouse found for this user.');
            $query->whereIn('warehouse_id', $warehouseIds->all());
        }

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

        $this->assertWarehouseAccessible($validated['warehouse_id']);

        if (!$this->isHubAdmin()) {
            if ($validated['type'] === 'in') {
                abort(403, 'Channels cannot increase stock manually via the API.');
            }
            if ($validated['type'] === 'adjustment' && $validated['quantity'] > 0) {
                abort(403, 'Channels cannot increase stock via positive adjustments.');
            }
        }

        $stockService = app(\Modules\Eshop360\Services\StockService::class);
        $product = $this->scopedProductsQuery()->findOrFail($validated['product_id']);
        $stockService->adjustStock($product, $validated['warehouse_id'], $validated['quantity'], $validated['type'], $validated['notes'], auth()->id());

        return response()->json(['message' => 'Stock movement recorded']);
    }

    // ─── Clients ─────────────────────────────────────

    public function clients(Request $request): JsonResponse
    {
        $query = $this->scopedCustomersQuery($this->requestedChannelId($request));

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    public function clientShow(Request $request, int $id): JsonResponse
    {
        $customer = $this->scopedCustomersQuery($this->requestedChannelId($request))
            ->with('orders', 'invoices')
            ->findOrFail($id);

        return response()->json($customer);
    }

    public function clientStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $validated['instance_id'] = $this->instanceId();
        $validated['channel_id'] = $this->normalizeRequestedChannelForWrite($validated['channel_id'] ?? null, true);

        return response()->json(Customer::create($validated), 201);
    }

    // ─── Sales ───────────────────────────────────────

    public function sales(Request $request): JsonResponse
    {
        $query = $this->scopedOrdersQuery($this->requestedChannelId($request))
            ->with('customer', 'items');

        if ($request->has('status')) $query->where('status', $request->status);
        return response()->json($query->latest()->paginate($request->get('per_page', 20)));
    }

    public function saleShow(Request $request, int $id): JsonResponse
    {
        $order = $this->scopedOrdersQuery($this->requestedChannelId($request))
            ->with('customer', 'items', 'payments', 'invoice')
            ->findOrFail($id);

        return response()->json($order);
    }

    public function saleStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
            'customer_id' => 'nullable|exists:eshop_customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        $orderService = app(\Modules\Eshop360\Services\OrderService::class);
        $channelId = $this->normalizeRequestedChannelForWrite($validated['channel_id'] ?? null, true);
        $customerId = $this->normalizeCustomerForWrite($validated['customer_id'] ?? null, $channelId);

        $normalizedItems = collect($validated['items'])->map(function (array $item) use ($channelId): array {
            $product = $this->scopedProductsQuery($channelId)->findOrFail($item['product_id']);

            $unitPrice = $item['unit_price'];
            if ($channelId !== null && !$this->isHubAdmin()) {
                $pricing = $this->pricingService->resolve($product, $channelId, true);
                $unitPrice = (float) $pricing['unit_price'];
            }

            return [
                'product' => $product,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => (float) $unitPrice,
            ];
        })->values();

        $subtotal = $normalizedItems->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

        $order = Order::create([
            'instance_id' => $this->instanceId(),
            'channel_id' => $channelId,
            'customer_id' => $customerId,
            'order_number' => $orderService->generateOrderNumber(),
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'paid_amount' => $subtotal,
            'due_amount' => 0,
            'source' => $channelId !== null ? 'channel_portal' : 'manual',
        ]);

        foreach ($normalizedItems as $item) {
            $product = $item['product'];
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'sku' => $product->sku ?? '',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json($order->load('items'));
    }

    // ─── Purchases ───────────────────────────────────

    public function purchases(Request $request): JsonResponse
    {
        $this->ensureHubAdmin();

        $query = PurchaseOrder::with('items')
            ->where('instance_id', $this->instanceId());

        return response()->json($query->latest()->paginate($request->get('per_page', 20)));
    }

    // ─── Reports ─────────────────────────────────────

    public function reportOverview(Request $request): JsonResponse
    {
        $this->ensureHubAdmin();
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(ReportService::class);
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        return response()->json($service->overview($this->instanceId(), $from, $to));
    }

    public function reportProfitLoss(Request $request): JsonResponse
    {
        $this->ensureHubAdmin();
        $request->validate(['instance_id' => 'required|integer']);
        $financeService = app(\Modules\Eshop360\Services\FinanceService::class);
        $chargesService = app(ChargesService::class);
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $pnl = $financeService->profitAndLoss($this->instanceId(), $from, $to);
        $pnl['charges_imputees'] = $chargesService->getDashboardData($this->instanceId());

        return response()->json($pnl);
    }

    public function reportStock(Request $request): JsonResponse
    {
        $this->ensureHubAdmin();
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(ReportService::class);
        return response()->json($service->stockReport($this->instanceId(), $request->get('warehouse_id')));
    }

    // ─── Online Orders ──────────────────────────────

    public function onlineOrders(Request $request): JsonResponse
    {
        $query = $this->scopedOnlineOrdersQuery($this->requestedChannelId($request))
            ->with('customer', 'items.product');

        return response()->json($query->latest()->paginate($request->get('per_page', 20)));
    }

    public function onlineOrderShow(Request $request, int $id): JsonResponse
    {
        $order = $this->scopedOnlineOrdersQuery($this->requestedChannelId($request))
            ->with('customer', 'items.product')
            ->findOrFail($id);

        return response()->json($order);
    }

    public function onlineOrderStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'instance_id' => 'required|integer',
            'customer_id' => 'required|exists:eshop_customers,id',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $channelId = $this->normalizeRequestedChannelForWrite($validated['channel_id'] ?? null, false);
        $customerId = $this->normalizeCustomerForWrite($validated['customer_id'], $channelId);
        $items = collect($validated['items'])->map(function (array $item) use ($channelId): array {
            $product = $this->scopedProductsQuery($channelId)->findOrFail($item['product_id']);

            return [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
            ];
        })->values()->all();

        $service = app(OnlineOrderService::class);
        $order = $service->createOrder(
            $this->instanceId(),
            $customerId,
            $items,
            $validated['delivery_address'] ?? null,
            $validated['notes'] ?? null,
            $channelId,
        );

        return response()->json($order, 201);
    }

    public function onlineOrderUpdateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['status' => 'required|string']);
        $order = $this->scopedOnlineOrdersQuery($this->requestedChannelId($request))->findOrFail($id);
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
        $this->ensureHubAdmin();
        $request->validate(['instance_id' => 'required|integer']);
        $reportService = app(ReportService::class);
        $chargesService = app(ChargesService::class);

        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();

        return response()->json([
            'overview' => $reportService->overview($this->instanceId(), $from, $to),
            'charges' => $chargesService->getDashboardData($this->instanceId()),
        ]);
    }

    // ─── Channel Margins ────────────────────────────

    public function channelMargins(Request $request, int $channelId): JsonResponse
    {
        $request->validate(['instance_id' => 'required|integer']);
        $this->assertChannelAccessible($channelId);
        $service = app(MarginService::class);
        $from = $request->get('from', now()->startOfMonth()->toDateTimeString());
        $to = $request->get('to', now()->toDateTimeString());
        return response()->json($service->getMarginSummary($this->instanceId(), $from, $to, $channelId));
    }

    // ─── Charges Realtime ────────────────────────────

    public function chargesRealtime(Request $request): JsonResponse
    {
        $this->ensureHubAdmin();
        $request->validate(['instance_id' => 'required|integer']);
        $service = app(ChargesService::class);
        return response()->json($service->getDashboardData($this->instanceId()));
    }

    private function instanceId(): int
    {
        return (int) (CurrentInstance::get()?->id ?? request()->integer('instance_id'));
    }

    private function isHubAdmin(): bool
    {
        return $this->channelAccess->isHubAdmin(auth()->user());
    }

    private function ensureHubAdmin(): void
    {
        abort_unless($this->isHubAdmin(), 403, 'This API action is reserved for Saphir Plus administrators.');
    }

    private function requestedChannelId(Request $request): ?int
    {
        if (!$request->filled('channel_id')) {
            return null;
        }

        $channelId = $request->integer('channel_id');
        $this->assertChannelAccessible($channelId);

        return $channelId;
    }

    private function normalizeRequestedChannelForWrite(?int $channelId, bool $requiredForChannelUsers): ?int
    {
        if ($channelId === null) {
            abort_if($requiredForChannelUsers && !$this->isHubAdmin(), 422, 'channel_id is required for channel-scoped writes.');
            return null;
        }

        $this->assertChannelAccessible($channelId);

        return $channelId;
    }

    private function assertChannelAccessible(int $channelId): void
    {
        $channel = DistributionChannel::query()
            ->where('instance_id', $this->instanceId())
            ->findOrFail($channelId);

        abort_unless(
            $this->channelAccess->canAccessChannel(auth()->user(), $channel, $this->instanceId()),
            403,
            'You do not have access to this channel.'
        );
    }

    private function assertWarehouseAccessible(int $warehouseId): void
    {
        if ($this->isHubAdmin()) {
            return;
        }

        abort_unless(
            $this->accessibleWarehouseIds()->contains($warehouseId),
            403,
            'You do not have access to this warehouse.'
        );
    }

    private function normalizeCustomerForWrite(?int $customerId, ?int $channelId): ?int
    {
        if ($customerId === null) {
            return null;
        }

        $customerQuery = Customer::query()
            ->where('instance_id', $this->instanceId());

        if (!$this->isHubAdmin()) {
            $customerQuery->whereIn('channel_id', $this->accessibleChannelIds()->all());
        } elseif ($channelId !== null) {
            $customerQuery->where(function (Builder $query) use ($channelId) {
                $query->where('channel_id', $channelId)
                    ->orWhereNull('channel_id');
            });
        }

        $customer = $customerQuery->findOrFail($customerId);

        if ($channelId !== null) {
            abort_unless(
                $this->isHubAdmin()
                    ? ((int) $customer->channel_id === $channelId || $customer->channel_id === null)
                    : (int) $customer->channel_id === $channelId,
                422,
                'Customer must belong to the selected channel.'
            );
        } elseif (!$this->isHubAdmin()) {
            abort(422, 'Channel users cannot create or use hub-level customers.');
        }

        return $customer->id;
    }

    private function scopedProductsQuery(?int $channelId = null): Builder
    {
        $query = Product::query()->where('instance_id', $this->instanceId());

        if ($this->isHubAdmin()) {
            if ($channelId !== null) {
                $query->whereHas('channelPrices', fn (Builder $priceQuery) => $priceQuery->where('channel_id', $channelId));
            }

            return $query;
        }

        $channelIds = $this->accessibleChannelIds();
        if ($channelId !== null) {
            $query->whereHas('channelPrices', fn (Builder $priceQuery) => $priceQuery->where('channel_id', $channelId));
        } else {
            $query->whereHas('channelPrices', fn (Builder $priceQuery) => $priceQuery->whereIn('channel_id', $channelIds->all()));
        }

        return $query;
    }

    private function scopedCustomersQuery(?int $channelId = null): Builder
    {
        $query = Customer::query()->where('instance_id', $this->instanceId());

        if ($this->isHubAdmin()) {
            if ($channelId !== null) {
                $query->where('channel_id', $channelId);
            }

            return $query;
        }

        $query->whereIn('channel_id', $this->accessibleChannelIds()->all());

        if ($channelId !== null) {
            $query->where('channel_id', $channelId);
        }

        return $query;
    }

    private function scopedOrdersQuery(?int $channelId = null): Builder
    {
        $query = Order::query()->where('instance_id', $this->instanceId());

        if ($this->isHubAdmin()) {
            if ($channelId !== null) {
                $query->where('channel_id', $channelId);
            }

            return $query;
        }

        $query->whereIn('channel_id', $this->accessibleChannelIds()->all());

        if ($channelId !== null) {
            $query->where('channel_id', $channelId);
        }

        return $query;
    }

    private function scopedOnlineOrdersQuery(?int $channelId = null): Builder
    {
        $query = OnlineOrder::query()->where('instance_id', $this->instanceId());

        if ($this->isHubAdmin()) {
            if ($channelId !== null) {
                $query->where('channel_id', $channelId);
            }

            return $query;
        }

        $query->whereIn('channel_id', $this->accessibleChannelIds()->all());

        if ($channelId !== null) {
            $query->where('channel_id', $channelId);
        }

        return $query;
    }

    /**
     * @return Collection<int, int>
     */
    private function accessibleChannelIds(): Collection
    {
        return $this->channelAccess->accessibleChannelIds(auth()->user(), $this->instanceId()) ?? collect();
    }

    /**
     * @return Collection<int, int>
     */
    private function accessibleWarehouseIds(): Collection
    {
        return DistributionChannel::query()
            ->where('instance_id', $this->instanceId())
            ->whereIn('id', $this->accessibleChannelIds()->all())
            ->whereNotNull('warehouse_id')
            ->pluck('warehouse_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }
}
