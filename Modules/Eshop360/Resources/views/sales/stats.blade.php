<x-dashboard::layouts.master
    :title="__('Statistiques ventes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Statistiques ventes')">

@php
    $slug = $instance->slug ?? '';
    $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-chart-dots me-2"></i>{{ __('Statistiques des ventes') }}</h4>
        <p class="text-muted mb-0">{{ __('Analyse detaillee du') }} {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} {{ __('au') }} {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.sales.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-list me-1"></i>{{ __('Ventes') }}</a>
        <a href="{{ route('eshop360.sales.dashboard', $slug) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-chart-bar me-1"></i>{{ __('Dashboard') }}</a>
        <a href="{{ route('eshop360.export.sales', array_merge([$slug], request()->only(['date_from','date_to']))) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.sales.stats', $slug) }}" id="stats-filter-form" class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-auto"><span class="text-muted">{{ __('au') }}</span></div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['date_from', 'date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.sales.stats', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

{{-- KPI Global --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-receipt fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $fmt($globalStats->total_revenue) }}</div><div class="text-muted">{{ __('CA total') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-cash fs-4 text-success"></i></div>
            <div><div class="fs-4 fw-bold text-success">{{ $fmt($globalStats->total_paid) }}</div><div class="text-muted">{{ __('Encaisse') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-alert-triangle fs-4 text-danger"></i></div>
            <div><div class="fs-4 fw-bold {{ $globalStats->total_due > 0 ? 'text-danger' : 'text-muted' }}">{{ $fmt($globalStats->total_due) }}</div><div class="text-muted">{{ __('Impayes') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-shopping-cart fs-4 text-info"></i></div>
            <div><div class="fs-4 fw-bold">{{ $globalStats->total_orders }}</div><div class="text-muted">{{ __('Commandes') }} &middot; {{ __('moy.') }} {{ $fmt($globalStats->avg_order) }}</div></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    {{-- ═══════ COLONNE GAUCHE ═══════ --}}
    <div class="col-xl-8">

        {{-- Tendance mensuelle --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-trending-up me-2"></i>{{ __('Tendance 12 mois') }}</h5></div>
            <div class="card-body">
                @if($monthlyTrend->count() > 0)
                <div class="row g-2">
                    @foreach($monthlyTrend as $mt)
                        @php $maxRev = $monthlyTrend->max('revenue'); $pct = $maxRev > 0 ? round($mt->revenue / $maxRev * 100) : 0; @endphp
                        <div class="col">
                            <div class="text-center">
                                <div class="d-flex flex-column align-items-center" style="height:120px; justify-content:flex-end;">
                                    <small class="fw-bold text-primary" style="font-size:.65rem;">{{ $fmt($mt->revenue) }}</small>
                                    <div class="bg-primary bg-opacity-75 rounded-top" style="width:28px; height:{{ max($pct, 5) }}%; min-height:4px;"></div>
                                </div>
                                <div class="text-muted mt-1" style="font-size:.65rem;">{{ \Carbon\Carbon::createFromDate($mt->year, $mt->month)->translatedFormat('M') }}</div>
                                <div class="fw-medium" style="font-size:.7rem;">{{ $mt->orders }} cmd</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                    <div class="text-center text-muted py-3">{{ __('Aucune donnee') }}</div>
                @endif
            </div>
        </div>

        {{-- Ventes par canal --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-sitemap me-2"></i>{{ __('Ventes par canal') }}</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>{{ __('Canal') }}</th><th class="text-center">{{ __('Commandes') }}</th><th class="text-end">{{ __('CA') }}</th><th class="text-end">{{ __('% CA') }}</th></tr></thead>
                        <tbody>
                            @foreach($byChannel as $ch)
                                @php $pct = $globalStats->total_revenue > 0 ? round($ch->revenue / $globalStats->total_revenue * 100, 1) : 0; @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $ch->channel_name }}</td>
                                    <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $ch->orders }}</span></td>
                                    <td class="text-end fw-bold">{{ $fmt($ch->revenue) }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress flex-grow-1" style="height:6px; max-width:80px;"><div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div></div>
                                            <span class="text-muted">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Ventes par client (Top 20) --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-users me-2"></i>{{ __('Top 20 clients') }}</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light sticky-top"><tr><th>#</th><th>{{ __('Client') }}</th><th class="text-center">{{ __('Cmd') }}</th><th class="text-end">{{ __('CA') }}</th><th class="text-end">{{ __('Du') }}</th><th class="text-end">{{ __('% CA') }}</th></tr></thead>
                        <tbody>
                            @foreach($byCustomer as $i => $cust)
                                @php $pct = $globalStats->total_revenue > 0 ? round($cust->revenue / $globalStats->total_revenue * 100, 1) : 0; @endphp
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $cust->customer_name }}</td>
                                    <td class="text-center"><span class="badge bg-secondary-subtle text-secondary">{{ $cust->orders }}</span></td>
                                    <td class="text-end fw-bold">{{ $fmt($cust->revenue) }}</td>
                                    <td class="text-end {{ $cust->due > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $fmt($cust->due) }}</td>
                                    <td class="text-end text-muted">{{ $pct }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Produits & Marges --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti ti-package me-2"></i>{{ __('Produits vendus & Marges') }}</h5>
                <span class="badge bg-secondary">{{ $marginData->count() }} {{ __('produits') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>{{ __('Produit') }}</th>
                                <th class="text-center">{{ __('Qte') }}</th>
                                <th class="text-end">{{ __('CA') }}</th>
                                <th class="text-end">{{ __('Cout total') }}</th>
                                <th class="text-end">{{ __('Marge brute') }}</th>
                                <th class="text-end">{{ __('% Marge') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($marginData as $p)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $p->product_name }}</div>
                                        <code class="text-muted" style="font-size:.7rem;">{{ $p->sku ?? '—' }}</code>
                                    </td>
                                    <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $p->qty_sold }}</span></td>
                                    <td class="text-end fw-bold">{{ $fmt($p->revenue) }}</td>
                                    <td class="text-end text-muted">{{ $fmt($p->total_cost) }}</td>
                                    <td class="text-end fw-bold {{ $p->gross_margin >= 0 ? 'text-success' : 'text-danger' }}">{{ $fmt($p->gross_margin) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $p->margin_pct >= 30 ? 'bg-success' : ($p->margin_pct >= 10 ? 'bg-warning' : 'bg-danger') }}">{{ $p->margin_pct }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>{{ __('Total') }}</td>
                                <td class="text-center">{{ $marginData->sum('qty_sold') }}</td>
                                <td class="text-end">{{ $fmt($marginData->sum('revenue')) }}</td>
                                <td class="text-end">{{ $fmt($marginData->sum('total_cost')) }}</td>
                                <td class="text-end text-success">{{ $fmt($marginData->sum('gross_margin')) }}</td>
                                @php $totalRev = $marginData->sum('revenue'); $totalMargin = $marginData->sum('gross_margin'); $avgMarginPct = $totalRev > 0 ? round($totalMargin / $totalRev * 100, 1) : 0; @endphp
                                <td class="text-end"><span class="badge bg-primary">{{ $avgMarginPct }}%</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══════ COLONNE DROITE ═══════ --}}
    <div class="col-xl-4">

        {{-- Par source --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-device-desktop me-2"></i>{{ __('Par source') }}</h5></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($bySource as $src)
                        @php
                            $srcIcon = match($src->source) { 'pos' => 'ti-cash-register', 'online' => 'ti-world', 'channel_portal' => 'ti-building-store', default => 'ti-pencil' };
                            $srcLabel = match($src->source) { 'pos' => 'POS', 'online' => 'En ligne', 'channel_portal' => 'Canal', default => ucfirst($src->source) };
                        @endphp
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div><i class="ti {{ $srcIcon }} me-2 text-primary"></i><span class="fw-medium">{{ $srcLabel }}</span></div>
                            <div class="text-end">
                                <div class="fw-bold">{{ $fmt($src->revenue) }}</div>
                                <small class="text-muted">{{ $src->orders }} cmd</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Par methode de paiement --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-credit-card me-2"></i>{{ __('Par methode') }}</h5></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($byPaymentMethod as $pm)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span>{{ ucfirst(str_replace('_', ' ', $pm->payment_method)) }}</span>
                            <div class="text-end">
                                <div class="fw-bold">{{ $fmt($pm->revenue) }}</div>
                                <small class="text-muted">{{ $pm->orders }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Par magasin --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-building-store me-2"></i>{{ __('Par magasin') }}</h5></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($byStore as $st)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span class="fw-medium">{{ $st->store_name }}</span>
                            <div class="text-end">
                                <div class="fw-bold">{{ $fmt($st->revenue) }}</div>
                                <small class="text-muted">{{ $st->orders }} cmd</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Par entrepot --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-building-warehouse me-2"></i>{{ __('Par entrepot') }}</h5></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($byWarehouse as $wh)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span class="fw-medium">{{ $wh->warehouse_name }}</span>
                            <div class="text-end">
                                <div class="fw-bold">{{ $fmt($wh->revenue) }}</div>
                                <small class="text-muted">{{ $wh->orders }} cmd</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Couverture charges --}}
        @can('eshop.charges.view')
        <div class="card border-0 shadow-sm mb-3 border-start border-4 border-info">
            <div class="card-header bg-info bg-opacity-10"><h5 class="card-title mb-0"><i class="ti ti-chart-pie me-2 text-info"></i>{{ __('Couverture des charges') }}</h5></div>
            <div class="card-body">
                @php
                    $revPeriod = (float)$globalStats->total_revenue;
                    $chargePct = $monthlyCharges > 0 ? round($revPeriod / $monthlyCharges * 100, 1) : 0;
                    $marginTotal = $marginData->sum('gross_margin');
                    $marginCoverage = $monthlyCharges > 0 ? round($marginTotal / $monthlyCharges * 100, 1) : 0;
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('CA / Charges mensuelles') }}</span>
                        <span class="fw-bold {{ $chargePct >= 100 ? 'text-success' : 'text-danger' }}">{{ $chargePct }}%</span>
                    </div>
                    <div class="progress" style="height:8px;"><div class="progress-bar {{ $chargePct >= 100 ? 'bg-success' : 'bg-danger' }}" style="width:{{ min($chargePct, 100) }}%"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ __('Marge / Charges') }}</span>
                        <span class="fw-bold {{ $marginCoverage >= 100 ? 'text-success' : 'text-warning' }}">{{ $marginCoverage }}%</span>
                    </div>
                    <div class="progress" style="height:8px;"><div class="progress-bar {{ $marginCoverage >= 100 ? 'bg-success' : 'bg-warning' }}" style="width:{{ min($marginCoverage, 100) }}%"></div></div>
                </div>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Charges mensuelles') }}</td><td class="text-end fw-bold">{{ $fmt($monthlyCharges) }}</td></tr>
                    <tr><td class="text-muted">{{ __('CA periode') }}</td><td class="text-end">{{ $fmt($revPeriod) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Marge brute') }}</td><td class="text-end text-success fw-bold">{{ $fmt($marginTotal) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Taxes collectees') }}</td><td class="text-end">{{ $fmt($globalStats->total_tax) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Remises accordees') }}</td><td class="text-end text-warning">{{ $fmt($globalStats->total_discount) }}</td></tr>
                </table>
            </div>
        </div>
        @endcan

        {{-- Section SAPHIR --}}
        @can('products.factory_price')
        <div class="card border-warning shadow-sm mb-3">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="card-title mb-0"><i class="ti ti-building-factory me-2 text-warning"></i>{{ __('Marges SAPHIR') }} <span class="badge bg-warning text-dark ms-2">{{ __('Restreint') }}</span></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>{{ __('Produit') }}</th><th class="text-end">{{ __('PA Usine') }}</th><th class="text-end">{{ __('PV') }}</th><th class="text-end">{{ __('Marge Saphir') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($marginData->filter(fn($p) => $p->purchase_price_factory > 0) as $p)
                                @php
                                    $paUsine = (float) $p->purchase_price_factory;
                                    $pv = (float) $p->current_price;
                                    $margeSaphir = $pv - $paUsine;
                                    $margeSaphirPct = $pv > 0 ? round($margeSaphir / $pv * 100, 1) : 0;
                                    $totalMargeSaphir = $margeSaphir * $p->qty_sold;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $p->product_name }}</div>
                                        <small class="text-muted">x{{ $p->qty_sold }}</small>
                                    </td>
                                    <td class="text-end text-muted">{{ number_format($paUsine, 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ $fmt($pv) }}</td>
                                    <td class="text-end">
                                        <div class="fw-bold {{ $margeSaphir >= 0 ? 'text-success' : 'text-danger' }}">{{ $fmt($totalMargeSaphir) }}</div>
                                        <small class="{{ $margeSaphirPct >= 0 ? 'text-success' : 'text-danger' }}">{{ $margeSaphirPct }}%</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endcan

    </div>
</div>

</x-dashboard::layouts.master>
