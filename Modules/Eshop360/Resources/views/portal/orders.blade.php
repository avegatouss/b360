<x-dashboard::layouts.master
    :title="__('Mes commandes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Mes commandes')">

@php
    $slug = $instance->slug ?? '';
    $source = $source ?? 'all';
    $statusColors = [
        'pending_validation' => 'bg-warning text-dark',
        'validated' => 'bg-info',
        'preparing' => 'bg-primary',
        'prepared' => 'bg-primary',
        'shipping' => 'bg-primary',
        'delivered' => 'bg-success',
        'received' => 'bg-success',
        'invoiced' => 'bg-secondary',
        'cancelled' => 'bg-danger',
        'completed' => 'bg-success',
        'pending' => 'bg-warning text-dark',
        'refunded' => 'bg-danger',
    ];
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('Mes commandes') }}</h4>
        <h6>{{ $customer->name }} <span class="text-muted">({{ $customer->code }})</span></h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-package me-1"></i>{{ __('Catalogue') }}</a>
        <a href="{{ route('eshop360.portal.cart', $slug) }}" class="btn btn-primary btn-sm"><i class="ti ti-shopping-cart me-1"></i>{{ __('Panier') }}</a>
    </div>
</div>

{{-- KPI (calculees sur TOUTES les donnees, pas la page courante) --}}
@php $fmt = fn($n) => number_format((float)$n, 0, ',', ' '); @endphp
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-receipt fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $globalStats->total }}</div><div class="text-muted">{{ __('Total commandes') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-world fs-4 text-info"></i></div>
            <div><div class="fs-4 fw-bold text-info">{{ $globalStats->online_count }}</div><div class="text-muted">{{ __('En ligne') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-building-store fs-4 text-success"></i></div>
            <div><div class="fs-4 fw-bold text-success">{{ $globalStats->store_count }}</div><div class="text-muted">{{ __('Magasin') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-clock fs-4 text-warning"></i></div>
            <div><div class="fs-4 fw-bold {{ $globalStats->pending_count > 0 ? 'text-warning' : 'text-muted' }}">{{ $globalStats->pending_count }}</div><div class="text-muted">{{ __('En attente') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm mb-0"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-alert-triangle fs-4 text-danger"></i></div>
            <div><div class="fs-4 fw-bold {{ $globalStats->total_due > 0 ? 'text-danger' : 'text-muted' }}">{{ $fmt($globalStats->total_due) }}</div><div class="text-muted">{{ __('Impayes') }}</div></div>
        </div></div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.portal.orders.index', $slug) }}" id="orders-filter-form" class="row g-2 align-items-center">
            <div class="col-auto" style="min-width: 160px;">
                <select name="source" class="form-select form-select-sm portal-select2" data-placeholder="{{ __('Toutes les sources') }}">
                    <option value="all" {{ $source === 'all' ? 'selected' : '' }}>{{ __('Toutes les sources') }}</option>
                    <option value="online" {{ $source === 'online' ? 'selected' : '' }}>{{ __('Commandes en ligne') }}</option>
                    <option value="store" {{ $source === 'store' ? 'selected' : '' }}>{{ __('Achats magasin') }}</option>
                </select>
            </div>
            <div class="col-auto" style="min-width: 180px;">
                <select name="status" class="form-select form-select-sm portal-select2" data-placeholder="{{ __('Tous les statuts') }}">
                    <option value=""></option>
                    @foreach(['pending_validation' => 'En attente', 'validated' => 'Validee', 'preparing' => 'Preparation', 'shipping' => 'Expedition', 'delivered' => 'Livree', 'received' => 'Recue', 'completed' => 'Terminee', 'cancelled' => 'Annulee', 'pending' => 'En cours'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status', 'source']) && (request('source') !== 'all' || request('status')))
                <div class="col-auto">
                    <a href="{{ route('eshop360.portal.orders.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted">{{ $globalStats->total }} {{ __('commande(s)') }} &middot; {{ $fmt($globalStats->total_revenue) }} {{ __('CA') }}</span>
            </div>
        </form>
    </div>
</div>

{{-- Orders Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-center">{{ __('Paiement') }}</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-semibold">{{ $order->reference }}</td>
                            <td class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($order->source_type === 'online')
                                    <span class="badge bg-info-subtle text-info">{{ $order->source_label }}</span>
                                    @if($order->channel_name)
                                        <small class="text-muted d-block">{{ $order->channel_name }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success">{{ $order->source_label }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($order->total, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $statusColors[$order->status] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </td>
                            <td class="text-center">
                                @if($order->payment_status)
                                    <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : ($order->payment_status === 'partial' ? 'bg-warning text-dark' : 'bg-danger') }}">{{ ucfirst($order->payment_status) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($order->show_url)
                                    <a href="{{ $order->show_url }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye me-1"></i>{{ __('Voir') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune commande') }}
                                <div class="mt-2"><a href="{{ route('eshop360.portal.catalog', $slug) }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('Commander') }}</a></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-3 border-top">{{ $orders->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
jQuery(function ($) {
    $('.portal-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
