<x-dashboard::layouts.master
    :title="__('Stats clients') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Stats clients')">

@php
    $slug = $instance->slug ?? '';
    $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-chart-dots me-2"></i>{{ __('Statistiques clients') }}</h4>
        <p class="text-muted mb-0">{{ __('Analyse du') }} {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} {{ __('au') }} {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.customers.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-users me-1"></i>{{ __('Clients') }}</a>
        <a href="{{ route('eshop360.customers.report', $slug) }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-chart-bar me-1"></i>{{ __('Rapport') }}</a>
        <a href="{{ route('eshop360.export.customers', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.customers.stats', $slug) }}" class="row g-2 align-items-center">
            <div class="col-auto"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}"></div>
            <div class="col-auto"><span class="text-muted">{{ __('au') }}</span></div>
            <div class="col-auto"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}"></div>
            <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
            @if(request()->hasAny(['date_from', 'date_to']))
                <div class="col-auto"><a href="{{ route('eshop360.customers.stats', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>
            @endif
        </form>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-users fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $totalCustomers }}</div><div class="text-muted">{{ __('Total clients') }} <small class="text-success">({{ $activeCustomers }} {{ __('actifs') }})</small></div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-user-plus fs-4 text-success"></i></div>
            <div><div class="fs-4 fw-bold text-success">{{ $newCustomers }}</div><div class="text-muted">{{ __('Nouveaux (periode)') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-shopping-cart fs-4 text-info"></i></div>
            <div><div class="fs-4 fw-bold">{{ $fmt($financialStats->total_revenue) }}</div><div class="text-muted">{{ __('CA clients') }} <small>({{ $financialStats->buying_customers }} {{ __('acheteurs') }})</small></div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-alert-triangle fs-4 text-danger"></i></div>
            <div><div class="fs-4 fw-bold {{ $financialStats->total_due > 0 ? 'text-danger' : 'text-muted' }}">{{ $fmt($financialStats->total_due) }}</div><div class="text-muted">{{ __('Impayes') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-key fs-4 text-warning"></i></div>
            <div><div class="fs-4 fw-bold">{{ $withAccount }}</div><div class="text-muted">{{ __('Avec compte portail') }}</div></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">

        {{-- Top 30 clients --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-trophy me-2"></i>{{ __('Top 30 clients') }}</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>#</th><th>{{ __('Client') }}</th><th class="text-center">{{ __('Cmd') }}</th><th class="text-end">{{ __('CA') }}</th><th class="text-end">{{ __('Paye') }}</th><th class="text-end">{{ __('Du') }}</th><th class="text-end">{{ __('Moy.') }}</th><th>{{ __('Dernier achat') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $i => $c)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <a href="{{ route('eshop360.customers.show', [$slug, $c->id]) }}" class="fw-semibold text-decoration-none">{{ $c->name }}</a>
                                        <div><code class="text-muted" style="font-size:.7rem;">{{ $c->code }}</code>
                                        @if($c->wallet_balance > 0)<span class="badge bg-success-subtle text-success ms-1" style="font-size:.65rem;">{{ $fmt($c->wallet_balance) }} wallet</span>@endif</div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $c->order_count }}</span></td>
                                    <td class="text-end fw-bold">{{ $fmt($c->revenue) }}</td>
                                    <td class="text-end text-success">{{ $fmt($c->paid) }}</td>
                                    <td class="text-end {{ $c->due > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $fmt($c->due) }}</td>
                                    <td class="text-end text-muted">{{ $fmt($c->avg_order) }}</td>
                                    <td class="text-muted" style="font-size:.8rem;">{{ $c->last_order_at ? \Carbon\Carbon::parse($c->last_order_at)->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Produits les plus achetes par les clients --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-package me-2"></i>{{ __('Produits les plus achetes') }}</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>{{ __('Produit') }}</th><th class="text-center">{{ __('Qte') }}</th><th class="text-center">{{ __('Acheteurs') }}</th><th class="text-end">{{ __('CA') }}</th><th class="text-end">{{ __('Marge') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topProductsBought as $p)
                                @php
                                    $cost = (float)($p->cost_price ?? 0);
                                    $margin = (float)$p->revenue - ($cost * (int)$p->qty);
                                    $marginPct = $p->revenue > 0 ? round($margin / $p->revenue * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td><div class="fw-medium">{{ $p->product_name }}</div><code class="text-muted" style="font-size:.7rem;">{{ $p->sku ?? '—' }}</code></td>
                                    <td class="text-center"><span class="badge bg-info-subtle text-info">{{ $p->qty }}</span></td>
                                    <td class="text-center">{{ $p->unique_buyers }}</td>
                                    <td class="text-end fw-bold">{{ $fmt($p->revenue) }}</td>
                                    <td class="text-end">
                                        <span class="{{ $margin >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ $fmt($margin) }}</span>
                                        <small class="text-muted">({{ $marginPct }}%)</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tendance nouveaux clients --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-trending-up me-2"></i>{{ __('Nouveaux clients (12 mois)') }}</h5></div>
            <div class="card-body">
                @if($monthlyNewCustomers->count() > 0)
                <div class="row g-2">
                    @foreach($monthlyNewCustomers as $m)
                        @php $max = $monthlyNewCustomers->max('count'); $pct = $max > 0 ? round($m->count / $max * 100) : 0; @endphp
                        <div class="col">
                            <div class="text-center">
                                <div class="d-flex flex-column align-items-center" style="height:100px; justify-content:flex-end;">
                                    <small class="fw-bold text-success">{{ $m->count }}</small>
                                    <div class="bg-success bg-opacity-75 rounded-top" style="width:24px; height:{{ max($pct, 5) }}%; min-height:4px;"></div>
                                </div>
                                <div class="text-muted mt-1" style="font-size:.65rem;">{{ \Carbon\Carbon::createFromDate($m->year, $m->month)->translatedFormat('M') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                    <div class="text-center text-muted py-3">{{ __('Aucune donnee') }}</div>
                @endif
            </div>
        </div>

    </div>

    <div class="col-xl-4">

        {{-- Par magasin --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-building-store me-2"></i>{{ __('Par magasin') }}</h5></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($byStore as $s)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div><span class="fw-medium">{{ $s->store_name }}</span><br><small class="text-muted">{{ $s->customers }} {{ __('clients') }}</small></div>
                            <div class="text-end"><div class="fw-bold">{{ $fmt($s->revenue) }}</div><small class="text-muted">{{ $s->orders }} cmd</small></div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-3">{{ __('Aucune donnee') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Par canal --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-sitemap me-2"></i>{{ __('Par canal') }}</h5></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($byChannel as $ch)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div><span class="fw-medium">{{ $ch->channel_name }}</span><br><small class="text-muted">{{ $ch->customers }} {{ __('clients') }}</small></div>
                            <div class="text-end"><div class="fw-bold">{{ $fmt($ch->revenue) }}</div><small class="text-muted">{{ $ch->orders }} cmd</small></div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-3">{{ __('Aucune donnee') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Couverture charges --}}
        @can('eshop.charges.view')
        <div class="card border-0 shadow-sm mb-3 border-start border-4 border-info">
            <div class="card-header bg-info bg-opacity-10"><h5 class="card-title mb-0"><i class="ti ti-chart-pie me-2 text-info"></i>{{ __('Couverture charges') }}</h5></div>
            <div class="card-body">
                @php $revPeriod = (float)$financialStats->total_revenue; $chargePct = $monthlyCharges > 0 ? round($revPeriod / $monthlyCharges * 100, 1) : 0; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('CA clients / Charges') }}</span><span class="fw-bold {{ $chargePct >= 100 ? 'text-success' : 'text-danger' }}">{{ $chargePct }}%</span></div>
                    <div class="progress" style="height:8px;"><div class="progress-bar {{ $chargePct >= 100 ? 'bg-success' : 'bg-danger' }}" style="width:{{ min($chargePct, 100) }}%"></div></div>
                </div>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Charges mensuelles') }}</td><td class="text-end fw-bold">{{ $fmt($monthlyCharges) }}</td></tr>
                    <tr><td class="text-muted">{{ __('CA clients periode') }}</td><td class="text-end">{{ $fmt($revPeriod) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Encaisse') }}</td><td class="text-end text-success">{{ $fmt($financialStats->total_paid) }}</td></tr>
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
                <div class="table-responsive" style="max-height:300px; overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top"><tr><th>{{ __('Produit') }}</th><th class="text-end">{{ __('PA Usine') }}</th><th class="text-end">{{ __('Marge') }}</th></tr></thead>
                        <tbody>
                            @foreach($topProductsBought->filter(fn($p) => $p->purchase_price_factory > 0) as $p)
                                @php $paU = (float)$p->purchase_price_factory; $rev = (float)$p->revenue; $mSaphir = $rev - ($paU * (int)$p->qty); @endphp
                                <tr>
                                    <td><div class="fw-medium">{{ $p->product_name }}</div><small class="text-muted">x{{ $p->qty }}</small></td>
                                    <td class="text-end text-muted">{{ number_format($paU, 2, ',', ' ') }}</td>
                                    <td class="text-end fw-bold {{ $mSaphir >= 0 ? 'text-success' : 'text-danger' }}">{{ $fmt($mSaphir) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endcan

        {{-- Parametres portail --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-settings me-2"></i>{{ __('Portail client') }}</h5></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Portail active') }}</td><td class="text-end"><span class="badge {{ ($customerSettings['customer_portal_enabled'] ?? true) ? 'bg-success' : 'bg-secondary' }}">{{ ($customerSettings['customer_portal_enabled'] ?? true) ? __('Oui') : __('Non') }}</span></td></tr>
                    <tr><td class="text-muted">{{ __('Multi-comptes') }}</td><td class="text-end"><span class="badge {{ ($customerSettings['allow_multi_user_accounts'] ?? false) ? 'bg-success' : 'bg-secondary' }}">{{ ($customerSettings['allow_multi_user_accounts'] ?? false) ? __('Oui') : __('Non') }}</span></td></tr>
                    <tr><td class="text-muted">{{ __('Avec compte') }}</td><td class="text-end fw-bold">{{ $withAccount }} / {{ $totalCustomers }}</td></tr>
                </table>
            </div>
        </div>

    </div>
</div>

</x-dashboard::layouts.master>
