@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Tableau de bord') }}</h4>
    <p class="text-muted mb-0">{{ __('Vue d\'ensemble de l\'activite du canal') }}<strong>{{ $channel->name }}</strong></p>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Ventes aujourd\'hui') }}</p>
                        <h4 class="fw-bold mb-0">{{ number_format($todaySales ?? 0, 0, ',', ' ') }} <small class="text-muted fs-6">{{ __('XAF') }}</small></h4>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="ti ti-currency-dollar fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Commandes aujourd\'hui') }}</p>
                        <h4 class="fw-bold mb-0">{{ $todayOrdersCount ?? 0 }}</h4>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="ti ti-shopping-cart fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Commandes en attente') }}</p>
                        <h4 class="fw-bold mb-0">{{ $pendingOrders ?? 0 }}</h4>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="ti ti-clock fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Produits au catalogue') }}</p>
                        <h4 class="fw-bold mb-0">{{ $productCount ?? 0 }}</h4>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="ti ti-package fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monthly Margins --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Marge totale (mois)') }}</h6>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($monthlyMargins->total_margin ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Part canal') }}</h6>
                <h4 class="fw-bold text-success mb-0">{{ number_format($monthlyMargins->channel_part ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Part proprietaire') }}</h6>
                <h4 class="fw-bold text-info mb-0">{{ number_format($monthlyMargins->owner_part ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Part dette') }}</h6>
                <h4 class="fw-bold text-danger mb-0">{{ number_format($monthlyMargins->debt_part ?? 0, 0, ',', ' ') }} XAF</h4>
            </div>
        </div>
    </div>
</div>

{{-- Recent Orders --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between bg-white">
        <h5 class="card-title mb-0">{{ __('Commandes recentes') }}</h5>
        <a href="{{ route('eshop360.channel-portal.orders.index', [$slug, $channel->slug ?? $channel->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('Voir tout') }}</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('N° commande') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('eshop360.channel-portal.orders.show', [$slug, $channel->slug ?? $channel->id, $order->id]) }}">
                                {{ $order->order_number ?? ('ORD-' . $order->id) }}
                            </a>
                        </td>
                        <td>{{ $order->customer->name ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($order->total, 0, ',', ' ') }} XAF</td>
                        <td>
                            @php
                                $statusColors = ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success', 'cancelled' => 'danger'];
                                $statusLabels = ['pending' => 'En attente', 'processing' => 'En cours', 'completed' => 'Terminee', 'cancelled' => 'Annulee'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">{{ __('Aucune commande pour le moment.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
