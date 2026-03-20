{{-- Widget: Recent Orders --}}
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $instanceId = $instance?->id ?? 0;
    $slug = $instance->slug ?? '';

    $recentOrders = \Modules\Eshop360\Models\Order::where('instance_id', $instanceId)
        ->with('customer')
        ->latest()
        ->limit(5)
        ->get();
@endphp
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-receipt me-2 text-info"></i>Commandes recentes</h6>
        <a href="{{ route('eshop360.orders.index', $slug) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        @if($recentOrders->isEmpty())
            <div class="text-center text-muted py-4">Aucune commande</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="">Ref</th>
                            <th class="">Client</th>
                            <th class=" text-end">Total</th>
                            <th class=" text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class=" fw-medium">{{ $order->reference }}</td>
                                <td class=" text-truncate" style="max-width:120px;">{{ $order->customer->name ?? '—' }}</td>
                                <td class=" text-end fw-bold">{{ number_format($order->total, 0, ',', ' ') }}</td>
                                <td class=" text-center">
                                    @php
                                        $statusClass = match($order->status) {
                                            'completed' => 'bg-success',
                                            'pending' => 'bg-warning',
                                            'cancelled', 'refunded' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill" style="font-size:.8rem;">{{ ucfirst($order->status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
