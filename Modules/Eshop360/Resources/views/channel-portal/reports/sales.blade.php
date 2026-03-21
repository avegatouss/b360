@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Rapport des ventes') }}</h4>
    <p class="text-muted mb-0">{{ __('Analyse des ventes du canal') }}</p>
</div>

{{-- Date Range Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">{{ __('Du') }}</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Au') }}</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-filter me-1"></i> {{ __('Filtrer') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Ventes totales') }}</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['total_sales'] ?? 0, 0, ',', ' ') }} <small class="text-muted fs-6">{{ $eshopCurrency ?? 'FCFA' }}</small></h4>
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
                        <p class="text-muted mb-1 small">{{ __('Total encaisse') }}</p>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($stats['total_paid'] ?? 0, 0, ',', ' ') }} <small class="text-muted fs-6">{{ $eshopCurrency ?? 'FCFA' }}</small></h4>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="ti ti-cash fs-4 text-success"></i>
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
                        <p class="text-muted mb-1 small">{{ __('Total du') }}</p>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($stats['total_due'] ?? 0, 0, ',', ' ') }} <small class="text-muted fs-6">{{ $eshopCurrency ?? 'FCFA' }}</small></h4>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="ti ti-alert-triangle fs-4 text-danger"></i>
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
                        <p class="text-muted mb-1 small">{{ __('Nombre de commandes') }}</p>
                        <h4 class="fw-bold mb-0">{{ $stats['order_count'] ?? 0 }}</h4>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="ti ti-shopping-cart fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Status Breakdown --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Terminees') }}</h6>
                <h4 class="fw-bold text-success mb-0">{{ $stats['completed'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('En attente') }}</h6>
                <h4 class="fw-bold text-warning mb-0">{{ $stats['pending'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Annulees') }}</h6>
                <h4 class="fw-bold text-danger mb-0">{{ $stats['cancelled'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Daily Sales Table --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">{{ __('Ventes par jour') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th class="text-center">{{ __('Commandes') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailySales ?? [] as $day)
                    <tr>
                        <td>{{ $day->date ?? '---' }}</td>
                        <td class="text-center">{{ $day->count ?? 0 }}</td>
                        <td class="text-end fw-bold">{{ number_format($day->total ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">{{ __('Aucune donnee disponible.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent Orders --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-bold">{{ __('Commandes recentes') }}</h6>
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
                    @php
                        $statusColors = ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success', 'cancelled' => 'danger', 'refunded' => 'dark'];
                        $statusLabels = ['pending' => 'En attente', 'processing' => 'En cours', 'completed' => 'Terminee', 'cancelled' => 'Annulee', 'refunded' => 'Remboursee'];
                    @endphp
                    @forelse($recentOrders ?? [] as $order)
                    <tr>
                        <td class="fw-medium">{{ $order->order_number ?? ('ORD-' . $order->id) }}</td>
                        <td>{{ $order->customer->name ?? '---' }}</td>
                        <td class="fw-bold">{{ number_format($order->total, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">{{ __('Aucune commande trouvee.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
