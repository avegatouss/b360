<x-dashboard::layouts.master
    :title="__('Tableau de bord ventes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Tableau de bord ventes')">

@php $slug = $instance->slug ?? ''; @endphp

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.sales.dashboard', $slug) }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    <span class="input-group-text">-</span>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
            </div>
            <div class="col-auto" style="min-width:200px;">
                <select name="channel_id" class="form-select form-select-sm dashboard-select2" data-placeholder="{{ __('Tous les canaux') }}">
                    <option value="">{{ __('Tous les canaux') }}</option>
                    @foreach($channels as $ch)
                        <option value="{{ $ch->id }}" {{ (string) $channelId === (string) $ch->id ? 'selected' : '' }}>{{ $ch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if($channelId || $dateFrom !== now()->startOfMonth()->toDateString())
                <div class="col-auto">
                    <a href="{{ route('eshop360.sales.dashboard', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Reset') }}</a>
                </div>
            @endif
            @if($channelId)
                <div class="col-auto">
                    <span class="badge bg-info-subtle text-info px-3 py-2">
                        <i class="ti ti-filter me-1"></i>Canal: {{ $channels->firstWhere('id', $channelId)?->name ?? $channelId }}
                    </span>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('CA Periode') }}</p>
                        <h3 class="fw-bold mb-0 text-primary">{{ number_format($totalSales, 0, ',', ' ') }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                        <i class="ti ti-chart-bar fs-4 text-primary"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    <span class="text-success fw-medium">{{ number_format($todaySales, 0, ',', ' ') }}</span> {{ __("aujourd'hui") }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Commandes') }}</p>
                        <h3 class="fw-bold mb-0">{{ $totalOrders }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                        <i class="ti ti-shopping-cart fs-4 text-success"></i>
                    </div>
                </div>
                <div class="mt-2 small">
                    <span class="badge bg-success-subtle text-success me-1">{{ $completedOrders }}</span>
                    <span class="badge bg-warning-subtle text-warning me-1">{{ $pendingOrders }}</span>
                    <span class="badge bg-danger-subtle text-danger">{{ $cancelledOrders }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Panier moyen') }}</p>
                        <h3 class="fw-bold mb-0">{{ number_format($avgOrderValue, 0, ',', ' ') }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-2">
                        <i class="ti ti-receipt fs-4 text-info"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    {{ __('Taxe') }}: {{ number_format($totalTax, 0, ',', ' ') }} | {{ __('Remise') }}: {{ number_format($totalDiscount, 0, ',', ' ') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">{{ __('Impayes') }}</p>
                        <h3 class="fw-bold mb-0 {{ $totalDue > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($totalDue, 0, ',', ' ') }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                        <i class="ti ti-alert-triangle fs-4 text-danger"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    {{ __('Encaisse') }}: <span class="text-success fw-medium">{{ number_format($totalPaid, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Chart: Daily Sales --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="ti ti-trending-up me-2"></i>{{ __('Ventes journalieres') }}</h6>
            </div>
            <div class="card-body">
                <div style="position:relative; height:280px;">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales by Source & Payment --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent">
                <h6 class="mb-0 fw-bold"><i class="ti ti-arrows-split me-2"></i>{{ __('Par source') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @php
                        $sourceLabels = ['pos' => ['POS', 'bg-primary'], 'online' => ['En ligne', 'bg-info'], 'manual' => ['Manuel', 'bg-secondary'], 'channel_portal' => ['Canal', 'bg-success']];
                    @endphp
                    @foreach($sourceLabels as $src => [$label, $badgeClass])
                        @php $s = $salesBySource->get($src); @endphp
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <span><span class="badge {{ $badgeClass }} me-2">{{ $label }}</span>{{ $s ? $s->count : 0 }} cmd.</span>
                            <span class="fw-bold">{{ number_format($s ? $s->total : 0, 0, ',', ' ') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="mb-0 fw-bold"><i class="ti ti-credit-card me-2"></i>{{ __('Par paiement') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($salesByPayment as $pm)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <span class="text-capitalize">{{ $pm->payment_method ?? '—' }} <small class="text-muted">({{ $pm->count }})</small></span>
                            <span class="fw-bold">{{ number_format($pm->total, 0, ',', ' ') }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted small py-3">{{ __('Aucune donnee') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Top Products --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="ti ti-star me-2"></i>{{ __('Top produits') }}</h6>
                <a href="{{ route('eshop360.reports.best-sellers', $slug) }}" class="btn btn-sm btn-outline-primary">{{ __('Voir tout') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">{{ __('Produit') }}</th>
                                <th class="text-center small">{{ __('Qte') }}</th>
                                <th class="text-end small">{{ __('CA') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $tp)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($tp->product?->image)
                                                <img src="{{ asset('storage/' . $tp->product->image) }}" class="rounded me-2" style="width:28px;height:28px;object-fit:cover;">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:28px;height:28px;"><i class="ti ti-package text-muted" style="font-size:.7rem;"></i></div>
                                            @endif
                                            <div>
                                                <div class="fw-medium small text-truncate" style="max-width:160px;">{{ $tp->product?->name ?? '—' }}</div>
                                                <div class="text-muted" style="font-size:.65rem;">{{ $tp->product?->sku ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold small">{{ $tp->total_qty }}</td>
                                    <td class="text-end fw-bold small text-primary">{{ number_format($tp->total_revenue, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3 small">{{ __('Aucune vente') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="ti ti-clock me-2"></i>{{ __('Dernieres commandes') }}</h6>
                <a href="{{ route('eshop360.orders.index', $slug) }}" class="btn btn-sm btn-outline-primary">{{ __('Voir tout') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">{{ __('Ref') }}</th>
                                <th class="small">{{ __('Client') }}</th>
                                <th class="small">{{ __('Source') }}</th>
                                <th class="text-center small">{{ __('Statut') }}</th>
                                <th class="text-center small">{{ __('Paiement') }}</th>
                                <th class="text-end small">{{ __('Total') }}</th>
                                <th class="small">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                @php
                                    $statusClass = match($sale->status) {
                                        'completed' => 'bg-success', 'pending' => 'bg-warning',
                                        'processing' => 'bg-info', 'cancelled', 'refunded' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    $payClass = match($sale->payment_status) {
                                        'paid' => 'bg-success', 'partial' => 'bg-warning', default => 'bg-danger',
                                    };
                                @endphp
                                <tr>
                                    <td class="small fw-medium">
                                        <a href="{{ route('eshop360.sales.show', [$slug, $sale]) }}" class="text-decoration-none">{{ $sale->reference }}</a>
                                    </td>
                                    <td class="small text-truncate" style="max-width:100px;">{{ $sale->customer?->name ?? '—' }}</td>
                                    <td class="small">
                                        <span class="badge bg-light text-dark" style="font-size:.6rem;">{{ $sale->source }}</span>
                                        @if($sale->channel)
                                            <span class="badge bg-info-subtle text-info" style="font-size:.55rem;">{{ $sale->channel->name }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="badge {{ $statusClass }} rounded-pill" style="font-size:.6rem;">{{ ucfirst($sale->status) }}</span></td>
                                    <td class="text-center"><span class="badge {{ $payClass }} rounded-pill" style="font-size:.6rem;">{{ ucfirst($sale->payment_status) }}</span></td>
                                    <td class="text-end small fw-bold">{{ number_format($sale->total, 0, ',', ' ') }}</td>
                                    <td class="small text-muted">{{ $sale->created_at?->format('d/m H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3 small">{{ __('Aucune commande') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var dailyData = @json($dailySales);
    var labels = dailyData.map(function (d) { return d.date; });
    var totals = dailyData.map(function (d) { return parseFloat(d.total); });
    var counts = dailyData.map(function (d) { return parseInt(d.count); });

    var ctx = document.getElementById('dailySalesChart');
    if (ctx && labels.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'CA',
                        data: totals,
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Commandes',
                        data: counts,
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle' } } },
                scales: {
                    y: { type: 'linear', position: 'left', ticks: { callback: function (v) { return v.toLocaleString('fr-FR'); } } },
                    y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, beginAtZero: true }
                }
            }
        });
    } else if (ctx) {
        ctx.parentElement.textContent = 'Aucune vente sur la periode.';
        ctx.parentElement.classList.add('text-center', 'text-muted', 'py-5');
    }
});
</script>
<script>
jQuery(function ($) {
    $('.dashboard-select2').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        width: '100%',
        placeholder: function () { return $(this).data('placeholder') || ''; }
    }).on('select2:select select2:clear', function () {
        $(this).closest('form')[0].submit();
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
