<x-dashboard::layouts.master
    :title="__('Mes commandes') . ' - ' . $channel->name . ' - ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Mes commandes') . ' - ' . $channel->name">

@php
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

<div class="page-header">
    <div class="page-title me-auto">
        <h4 class="fw-bold">{{ __('Mes commandes') }} &mdash; {{ $channel->name }}</h4>
        <h6>{{ $customer->name }} <span class="text-muted">({{ $customer->code }})</span></h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.canal-portal.catalog', [$slug, $channelKey]) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-package me-1"></i>{{ __('Catalogue') }}</a>
        <a href="{{ route('eshop360.canal-portal.cart', [$slug, $channelKey]) }}" class="btn btn-primary btn-sm"><i class="ti ti-shopping-cart me-1"></i>{{ __('Panier') }}</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.canal-portal.orders.index', [$slug, $channelKey]) }}" id="orders-filter-form" class="row g-2 align-items-center">
            <div class="col-auto" style="min-width: 180px;">
                <select name="status" class="form-select form-select-sm portal-select2" data-placeholder="{{ __('Tous les statuts') }}">
                    <option value=""></option>
                    @foreach(['pending_validation' => 'En attente', 'validated' => 'Validee', 'preparing' => 'Preparation', 'shipping' => 'Expedition', 'delivered' => 'Livree', 'received' => 'Recue', 'cancelled' => 'Annulee'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->filled('status'))
                <div class="col-auto">
                    <a href="{{ route('eshop360.canal-portal.orders.index', [$slug, $channelKey]) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <span class="text-muted">{{ $orders->total() }} {{ __('commande(s)') }}</span>
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
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-semibold">{{ $order->reference }}</td>
                            <td class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end fw-bold">{{ number_format((float) $order->total, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $statusColors[$order->status] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.canal-portal.orders.show', [$slug, $channelKey, $order]) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye me-1"></i>{{ __('Voir') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune commande') }}
                                <div class="mt-2"><a href="{{ route('eshop360.canal-portal.catalog', [$slug, $channelKey]) }}" class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>{{ __('Commander') }}</a></div>
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
