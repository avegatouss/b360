<x-dashboard::layouts.master
    :title="__('Absorption des charges') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Absorption des charges')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Rapprochement charges / ventes') }}</h4>
            <h6>{{ __('Repartition des charges fixes dans le cout de revient de chaque article vendu') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.charges.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Charges') }}</a>
    </div>
</div>

{{-- Filtres periode --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.charges.cost-absorption', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2"><label class="form-label small mb-1">{{ __('Du') }}</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}"></div>
            <div class="col-md-2"><label class="form-label small mb-1">{{ __('Au') }}</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}"></div>
            <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i>{{ __('Analyser') }}</button></div>
            <div class="col-auto"><small class="text-muted">{{ $daysInPeriod }} {{ __('jours') }}</small></div>
        </form>
    </div>
</div>

{{-- KPIs visuels --}}
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-3 h-100">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Charges periode') }}</small>
                <div class="fw-bold fs-4 text-primary" id="kpi-charges">{{ number_format($totalChargesForPeriod, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('CA periode') }}</small>
                <div class="fw-bold fs-4 text-success">{{ number_format($totalRevenue, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card border-0 shadow-sm border-start border-info border-3 h-100">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Unites vendues') }}</small>
                <div class="fw-bold fs-4 text-info">{{ number_format($totalUnitsSold, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card border-0 shadow-sm border-start border-warning border-3 h-100">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Charge/unite') }}</small>
                <div class="fw-bold fs-4 text-warning" id="kpi-per-unit">{{ number_format($chargePerUnit, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card border-0 shadow-sm border-start border-danger border-3 h-100">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Taux absorption') }}</small>
                <div class="fw-bold fs-4 {{ $absorptionRate > 50 ? 'text-danger' : ($absorptionRate > 30 ? 'text-warning' : 'text-success') }}">{{ $absorptionRate }}%</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-3 h-100">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Marge apres charges') }}</small>
                <div class="fw-bold fs-4 text-success">{{ number_format($totalRevenue - $totalChargesForPeriod, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Graphique camembert repartition charges --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-chart-pie me-2"></i>{{ __('Repartition des charges') }}</h6></div>
            <div class="card-body">
                <canvas id="chargesPieChart" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- Decomposition charge par unite vendue --}}
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-stack me-2"></i>{{ __('Decomposition du cout par unite vendue') }}</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Type de charge') }}</th>
                                <th class="text-end">{{ __('Total mensuel') }}</th>
                                <th class="text-end">{{ __('Total periode') }}</th>
                                <th class="text-end">{{ __('Par unite') }}</th>
                                <th>{{ __('Part') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $categoryLabels = ['rent'=>'Loyer','electricity'=>'Electricite','salary'=>'Salaires','transport'=>'Transport','maintenance'=>'Maintenance','insurance'=>'Assurance','other'=>'Divers'];
                            @endphp
                            @foreach($chargeBreakdown as $cb)
                                @php $pct = $totalChargesForPeriod > 0 ? round(($cb['period_total'] / $totalChargesForPeriod) * 100) : 0; @endphp
                                <tr>
                                    <td class="fw-medium">
                                        <i class="ti ti-point-filled me-1 text-primary"></i>{{ $categoryLabels[$cb['category']] ?? ucfirst($cb['category']) }}
                                    </td>
                                    <td class="text-end small text-muted">{{ number_format($cb['monthly'], 0, ',', ' ') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($cb['period_total'], 0, ',', ' ') }}</td>
                                    <td class="text-end text-warning fw-bold">{{ number_format($cb['per_unit'], 0, ',', ' ') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:5px;"><div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div></div>
                                            <small class="text-muted">{{ $pct }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>{{ __('TOTAL') }}</td>
                                <td class="text-end">{{ number_format($charges->where('is_active', true)->sum('amount_monthly'), 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($totalChargesForPeriod, 0, ',', ' ') }}</td>
                                <td class="text-end text-warning">{{ number_format($chargePerUnit, 0, ',', ' ') }}</td>
                                <td>100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tableau par produit --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-package me-2"></i>{{ __('Impact des charges par produit vendu') }} <span class="badge bg-primary ms-1">{{ $products->count() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Produit') }}</th>
                        <th class="text-center">{{ __('Qte vendue') }}</th>
                        <th class="text-end">{{ __('CA') }}</th>
                        <th class="text-end">{{ __('Charges allouees') }}</th>
                        <th class="text-end">{{ __('Marge apres charges') }}</th>
                        <th class="text-center">{{ __('Poids charges') }}</th>
                        <th>{{ __('Sante') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                        @php
                            $healthColor = $prod->charge_pct > 50 ? 'danger' : ($prod->charge_pct > 30 ? 'warning' : 'success');
                            $healthIcon = $prod->charge_pct > 50 ? 'ti-alert-triangle' : ($prod->charge_pct > 30 ? 'ti-alert-circle' : 'ti-circle-check');
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $prod->name }}</td>
                            <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $prod->qty_sold }}</span></td>
                            <td class="text-end fw-bold text-success">{{ number_format($prod->revenue, 0, ',', ' ') }}</td>
                            <td class="text-end text-danger">{{ number_format($prod->charge_allocated, 0, ',', ' ') }}</td>
                            <td class="text-end fw-bold {{ $prod->margin_after_charges >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($prod->margin_after_charges, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <div class="progress" style="width:60px;height:5px;"><div class="progress-bar bg-{{ $healthColor }}" style="width:{{ min(100, $prod->charge_pct) }}%"></div></div>
                                    <small class="fw-bold text-{{ $healthColor }}">{{ $prod->charge_pct }}%</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $healthColor }}-subtle text-{{ $healthColor }}"><i class="ti {{ $healthIcon }} me-1"></i>{{ $prod->charge_pct > 50 ? __('Critique') : ($prod->charge_pct > 30 ? __('Attention') : __('Sain')) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-chart-bar-off fs-1 d-block mb-2"></i>{{ __('Aucune vente sur cette periode.') }}</td></tr>
                    @endforelse
                </tbody>
                @if($products->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td>{{ __('TOTAL') }}</td>
                        <td class="text-center">{{ number_format($totalUnitsSold, 0, ',', ' ') }}</td>
                        <td class="text-end text-success">{{ number_format($totalRevenue, 0, ',', ' ') }}</td>
                        <td class="text-end text-danger">{{ number_format($totalChargesForPeriod, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($totalRevenue - $totalChargesForPeriod, 0, ',', ' ') }}</td>
                        <td class="text-center">{{ $absorptionRate }}%</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- Realtime counter --}}
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">{{ __('Charges qui s\'accumulent en temps reel') }}</small>
                <div class="fw-bold fs-4 text-primary" id="rt-counter">{{ number_format($totalChargesForPeriod, 0, ',', ' ') }}</div>
            </div>
            <div class="text-end">
                <small class="text-muted">{{ __('Prochaine unite vendue absorbera') }}</small>
                <div class="fw-bold fs-4 text-warning" id="rt-per-unit">{{ number_format($chargePerUnit, 0, ',', ' ') }}</div>
            </div>
        </div>
        <div class="progress mt-2" style="height:8px;">
            <div class="progress-bar bg-success" style="width:{{ 100 - min(100, $absorptionRate) }}%" title="{{ __('Marge') }}"></div>
            <div class="progress-bar bg-danger" style="width:{{ min(100, $absorptionRate) }}%" title="{{ __('Charges') }}"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-success">{{ __('Marge') }}: {{ 100 - $absorptionRate }}%</small>
            <small class="text-danger">{{ __('Charges') }}: {{ $absorptionRate }}%</small>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pie chart
    var breakdownData = @json($chargeBreakdown);
    var labels = breakdownData.map(function (d) { return d.category.charAt(0).toUpperCase() + d.category.slice(1); });
    var values = breakdownData.map(function (d) { return d.period_total; });
    var colors = ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6', '#6b7280'];

    var ctx = document.getElementById('chargesPieChart');
    if (ctx && labels.length > 0) {
        new Chart(ctx, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colors.slice(0, labels.length), borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } } } }
        });
    }

    // Realtime counter
    var costPerSecond = {{ $totalChargesForPeriod > 0 ? $charges->where('is_active', true)->sum(fn($c) => $c->amount_monthly / (30*86400)) : 0 }};
    var currentTotal = {{ $totalChargesForPeriod }};
    var totalUnits = {{ $totalUnitsSold }};
    var counterEl = document.getElementById('rt-counter');
    var perUnitEl = document.getElementById('rt-per-unit');

    if (costPerSecond > 0) {
        setInterval(function () {
            currentTotal += costPerSecond;
            counterEl.textContent = Math.round(currentTotal).toLocaleString('fr-FR');
            if (totalUnits > 0) {
                perUnitEl.textContent = Math.round(currentTotal / totalUnits).toLocaleString('fr-FR');
            }
        }, 1000);
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
