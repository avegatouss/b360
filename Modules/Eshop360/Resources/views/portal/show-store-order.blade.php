<x-dashboard::layouts.master
    :title="__('Commande') . ' ' . ($order->order_number ?? 'ORD-' . $order->id) . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail commande')">

@php
    $slug = $instance->slug ?? '';
    $ref = $order->order_number ?? ('ORD-' . $order->id);
    $statusColors = [
        'completed' => 'bg-success', 'pending' => 'bg-warning text-dark', 'processing' => 'bg-primary',
        'cancelled' => 'bg-danger', 'refunded' => 'bg-danger',
    ];
    $paymentColors = [
        'paid' => 'bg-success', 'partial' => 'bg-warning text-dark', 'unpaid' => 'bg-danger',
    ];
@endphp

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('Commande') }} {{ $ref }}</h4>
        <h6>{{ $order->created_at->format('d/m/Y H:i') }} &middot; {{ $customer->name }}</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.portal.orders.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
        <a href="{{ route('eshop360.portal.orders.store-print', [$slug, $order]) }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="ti ti-printer me-1"></i>{{ __('Imprimer') }}</a>
        <a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-package me-1"></i>{{ __('Catalogue') }}</a>
    </div>
</div>

<div class="row g-3">
    {{-- Left: Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Reference') }}</td><td class="fw-bold">{{ $ref }}</td></tr>
                    <tr><td class="text-muted">{{ __('Date') }}</td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="text-muted">{{ __('Source') }}</td><td>
                        @if($order->source === 'pos')
                            <span class="badge bg-success-subtle text-success">{{ __('Point de vente') }}</span>
                        @else
                            <span class="badge bg-info-subtle text-info">{{ ucfirst($order->source) }}</span>
                        @endif
                    </td></tr>
                    <tr><td class="text-muted">{{ __('Statut') }}</td><td><span class="badge {{ $statusColors[$order->status] ?? 'bg-secondary' }}">{{ ucfirst($order->status) }}</span></td></tr>
                    <tr><td class="text-muted">{{ __('Paiement') }}</td><td><span class="badge {{ $paymentColors[$order->payment_status] ?? 'bg-secondary' }}">{{ ucfirst($order->payment_status) }}</span></td></tr>
                    <tr><td class="text-muted">{{ __('Methode') }}</td><td>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? '—')) }}</td></tr>
                    @if($order->store)
                        <tr><td class="text-muted">{{ __('Magasin') }}</td><td>{{ $order->store->name }}</td></tr>
                    @endif
                    @if($order->notes)
                        <tr><td class="text-muted">{{ __('Notes') }}</td><td>{{ $order->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Totaux --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0 fw-bold"><i class="ti ti-calculator me-2"></i>{{ __('Totaux') }}</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Sous-total') }}</td><td class="text-end">{{ number_format((float)$order->subtotal, 0, ',', ' ') }}</td></tr>
                    @if($order->tax_amount > 0)
                        <tr><td class="text-muted">{{ __('Taxes') }}</td><td class="text-end">{{ number_format((float)$order->tax_amount, 0, ',', ' ') }}</td></tr>
                    @endif
                    @if($order->discount_amount > 0)
                        <tr><td class="text-muted">{{ __('Remise') }}</td><td class="text-end text-danger">-{{ number_format((float)$order->discount_amount, 0, ',', ' ') }}</td></tr>
                    @endif
                    @if($order->shipping_amount > 0)
                        <tr><td class="text-muted">{{ __('Livraison') }}</td><td class="text-end">{{ number_format((float)$order->shipping_amount, 0, ',', ' ') }}</td></tr>
                    @endif
                    <tr class="border-top"><td class="fw-bold">{{ __('Total') }}</td><td class="text-end fw-bold fs-5 text-primary">{{ number_format((float)$order->total, 0, ',', ' ') }}</td></tr>
                    <tr><td class="text-muted">{{ __('Paye') }}</td><td class="text-end text-success fw-bold">{{ number_format((float)$order->paid_amount, 0, ',', ' ') }}</td></tr>
                    @if($order->due_amount > 0)
                        <tr><td class="text-muted">{{ __('Restant du') }}</td><td class="text-end text-danger fw-bold">{{ number_format((float)$order->due_amount, 0, ',', ' ') }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Items --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="ti ti-list me-2"></i>{{ __('Articles') }}</h6>
                <span class="badge bg-primary">{{ $order->items->count() }} {{ __('article(s)') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Produit') }}</th>
                                <th class="text-center">{{ __('Qte') }}</th>
                                <th class="text-end">{{ __('Prix unit.') }}</th>
                                @if($order->items->sum('discount') > 0)
                                    <th class="text-end">{{ __('Remise') }}</th>
                                @endif
                                @if($order->items->sum('tax') > 0)
                                    <th class="text-end">{{ __('Taxe') }}</th>
                                @endif
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product_name ?? $item->product?->name ?? '—' }}</div>
                                        <code class="text-muted" style="font-size:.7rem;">{{ $item->sku ?? $item->product?->sku ?? '' }}</code>
                                    </td>
                                    <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $item->quantity }}</span></td>
                                    <td class="text-end">{{ number_format((float)$item->unit_price, 0, ',', ' ') }}</td>
                                    @if($order->items->sum('discount') > 0)
                                        <td class="text-end text-danger">{{ $item->discount > 0 ? '-' . number_format((float)$item->discount, 0, ',', ' ') : '—' }}</td>
                                    @endif
                                    @if($order->items->sum('tax') > 0)
                                        <td class="text-end text-muted">{{ number_format((float)$item->tax, 0, ',', ' ') }}</td>
                                    @endif
                                    <td class="text-end fw-bold">{{ number_format((float)$item->total, 0, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>{{ __('Total') }}</td>
                                <td class="text-center">{{ $order->items->sum('quantity') }}</td>
                                <td></td>
                                @if($order->items->sum('discount') > 0)<td class="text-end text-danger">-{{ number_format($order->items->sum('discount'), 0, ',', ' ') }}</td>@endif
                                @if($order->items->sum('tax') > 0)<td class="text-end">{{ number_format($order->items->sum('tax'), 0, ',', ' ') }}</td>@endif
                                <td class="text-end text-primary">{{ number_format($order->items->sum('total'), 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
