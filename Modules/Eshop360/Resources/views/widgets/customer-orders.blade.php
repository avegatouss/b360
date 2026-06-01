{{-- Widget: Customer Recent Orders --}}
@php
    $user = auth()->user();
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $customer = \Modules\Eshop360\Domain\CRM\Models\Customer::where('instance_id', $instance?->id)
        ->where(fn($q) => $q->where('user_id', $user->id)->orWhere('email', $user->email))
        ->first();

    if (!$customer) return;

    $slug = $instance->slug ?? '';

    // Online orders (portal)
    $onlineOrders = \Modules\Eshop360\Domain\Sales\Models\OnlineOrder::where('instance_id', $instance->id)
        ->where('customer_id', $customer->id)
        ->latest()
        ->limit(5)
        ->get();

    // Regular orders
    $orders = \Modules\Eshop360\Domain\Sales\Models\Order::where('instance_id', $instance->id)
        ->where('customer_id', $customer->id)
        ->latest()
        ->limit(5)
        ->get();

    $allOrders = $onlineOrders->map(fn($o) => (object)[
        'reference' => $o->reference,
        'total' => $o->total,
        'status' => $o->status,
        'date' => $o->created_at,
        'type' => 'online',
    ])->merge($orders->map(fn($o) => (object)[
        'reference' => $o->order_number ?? ('ORD-' . $o->id),
        'total' => $o->total,
        'status' => $o->status,
        'date' => $o->created_at,
        'type' => 'pos',
    ]))->sortByDesc('date')->take(8);

    $statusColors = [
        'pending_validation' => 'bg-warning',
        'validated' => 'bg-info',
        'preparing' => 'bg-primary',
        'shipping' => 'bg-primary',
        'delivered' => 'bg-success',
        'received' => 'bg-success',
        'invoiced' => 'bg-secondary',
        'cancelled' => 'bg-danger',
        'completed' => 'bg-success',
        'pending' => 'bg-warning',
        'refunded' => 'bg-danger',
    ];
@endphp
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-receipt me-2 text-info"></i>{{ __('Mes commandes recentes') }}</h6>
        <a href="{{ route('eshop360.portal.orders.index', $slug) }}" class="btn btn-sm btn-outline-info">{{ __('Voir tout') }}</a>
    </div>
    <div class="card-body p-0">
        @if($allOrders->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>
                {{ __('Aucune commande') }}
                <div class="mt-2"><a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-sm btn-primary">{{ __('Commander') }}</a></div>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($allOrders as $order)
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <div>
                            <div class="fw-semibold">{{ $order->reference }}</div>
                            <small class="text-muted">{{ $order->date->format('d/m/Y H:i') }}</small>
                            <span class="badge bg-light text-dark border ms-1" style="font-size:.65rem;">{{ $order->type === 'online' ? 'Portail' : 'Magasin' }}</span>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ number_format($order->total, 0, ',', ' ') }}</div>
                            <span class="badge {{ $statusColors[$order->status] ?? 'bg-secondary' }}" style="font-size:.7rem;">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
