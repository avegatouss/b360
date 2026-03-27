<?php

namespace Modules\Eshop360\Http\Controllers\Sales;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\PdfService;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly ChannelAccessService $channelAccess,
    ) {
    }

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $user = auth()->user();
        $channelFilter = $request->integer('channel_id') ?: null;

        $query = Order::with(['customer'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->source, fn ($q, $s) => $q->where('source', $s))
            ->when($request->payment_method, fn ($q, $m) => $q->where('payment_method', $m))
            ->when($request->customer_id, fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->min_total, fn ($q, $m) => $q->where('total', '>=', $m))
            ->when($request->max_total, fn ($q, $m) => $q->where('total', '<=', $m))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('eshop_orders.created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('eshop_orders.created_at', '<=', $d))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('order_number', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            }));

        // Optional channel filter (access control is handled by ChannelScope)
        $query->when($channelFilter, fn ($q) => $q->where('channel_id', $channelFilter));

        // KPIs from filtered query
        $fq = clone $query;
        $kpi = (object) [
            'total'     => (clone $fq)->count(),
            'revenue'   => round((float) (clone $fq)->sum('total'), 0),
            'paid'      => round((float) (clone $fq)->sum('paid_amount'), 0),
            'due'       => round((float) (clone $fq)->where('payment_status', '!=', 'paid')->sum('due_amount'), 0),
            'completed' => (clone $fq)->where('status', 'completed')->count(),
            'pending'   => (clone $fq)->where('status', 'pending')->count(),
            'cancelled' => (clone $fq)->whereIn('status', ['cancelled', 'refunded'])->count(),
            'avg'       => round((float) (clone $fq)->avg('total'), 0),
        ];

        $orders = $query->latest()->paginate(25)->withQueryString();

        // Filter lookups (scoped by channel access)
        $customers = \Modules\Eshop360\Models\Customer::where('instance_id', $instance->id)
            ->where('is_active', true)
            ->when($channelFilter, fn ($q) => $q->where('channel_id', $channelFilter))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $channels = $this->channelAccess->availableChannelsForFilter($user);

        $paymentMethods = Order::where('instance_id', $instance->id)
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method');

        return view('eshop360::sales.orders.index', compact('orders', 'kpi', 'customers', 'channels', 'paymentMethods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $validated = $request->validate([
            'customer_id'        => 'nullable|exists:eshop_customers,id',
            'payment_method'     => 'required|string|in:cash,card,cheque,paypal,bank_transfer,points,deposit,gift_card,external',
            'paid_amount'        => 'nullable|numeric|min:0',
            'discount_amount'    => 'nullable|numeric|min:0',
            'shipping_amount'    => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:1000',
            'coupon_code'        => 'nullable|string|max:50',
            'source'             => 'required|in:pos,online,manual',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
        ]);

        $order = $this->orderService->createFromItems($validated['items'], [
            'instance_id' => $instance?->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'paid_amount' => (float) ($validated['paid_amount'] ?? 0),
            'discount_amount' => (float) ($validated['discount_amount'] ?? 0),
            'shipping_amount' => (float) ($validated['shipping_amount'] ?? 0),
            'coupon_code' => $validated['coupon_code'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => $validated['source'],
            'biller_id' => auth()->id(),
        ]);

        return redirect()->route('eshop360.orders.show', [
            'slug' => $instance?->slug,
            'order' => $order,
        ])
            ->with('success', __('Order :number created successfully.', ['number' => $order->order_number]));
    }

    public function show(string $slug, Order $order)
    {
        $this->authorizeOrderAccess($order);
        $order->load(['customer', 'items.product', 'payments', 'cashRegister.store', 'store', 'holding']);

        return view('eshop360::sales.orders.show', compact('order'));
    }

    public function receipt(string $slug, Order $order, PdfService $pdf)
    {
        $this->authorizeOrderAccess($order);
        $order->load(['items.product', 'customer', 'payments', 'cashier', 'creator', 'store']);

        return $pdf->stream(
            $pdf->orderReceipt($order),
            "recu-{$order->order_number}.pdf"
        );
    }

    public function update(Request $request, string $slug, Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);
        $instance = CurrentInstance::get();
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
                'ORD-UPD',
                'Manual order payment update'
            );
            unset($updateData['paid_amount'], $updateData['payment_status']);
        }

        $order->update($updateData);

        return redirect()->route('eshop360.orders.show', [
            'slug' => $instance?->slug,
            'order' => $order,
        ])
            ->with('success', __('Order updated successfully.'));
    }

    public function destroy(string $slug, Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);
        $instance = CurrentInstance::get();
        $order->delete();

        return redirect()->route('eshop360.orders.index', ['slug' => $instance?->slug])
            ->with('success', __('Order deleted successfully.'));
    }

    public function online(Request $request)
    {
        $query = Order::with(['customer', 'items'])
            ->where('source', 'online')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('order_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%")))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('eshop360::sales.orders.online', compact('orders'));
    }

    private function authorizeOrderAccess(Order $order): void
    {
        $user = auth()->user();
        if ($this->channelAccess->isHubAdmin($user)) {
            return;
        }
        if ($order->channel_id === null) {
            abort(403, 'Acces refuse: commande du hub.');
        }
        abort_unless(
            $this->channelAccess->canAccessChannel($user, $order->channel_id),
            403, 'Acces refuse: cette commande appartient a un autre canal.'
        );
    }
}
