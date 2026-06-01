<x-dashboard::layouts.master
    :title="$channel->name . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="$channel->name">

@php $slug = $instance->slug ?? ''; @endphp
@php $currencyCode = function_exists('currency') ? currency($instance->id ?? null) : 'XAF'; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold"><i class="ti ti-broadcast me-2"></i>{{ $channel->name }}</h4>
            <h6>{{ $channel->code ?? $channel->slug }} · {{ $channel->description ?? __('Canal de distribution') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.channels.margins', [$slug, $channel]) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-chart-bar me-1"></i>{{ __('Marges') }}</a>
        <a href="{{ route('eshop360.channels.orders', [$slug, $channel]) }}" class="btn btn-outline-success btn-sm"><i class="ti ti-shopping-cart me-1"></i>{{ __('Commandes') }}</a>
        <a href="{{ route('eshop360.channels.edit', [$slug, $channel]) }}" class="btn btn-outline-warning btn-sm"><i class="ti ti-edit me-1"></i>{{ __('Modifier') }}</a>
        <a href="{{ route('eshop360.channels.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Canaux') }}</a>
    </div>
</div>

{{-- Channel Config Overview --}}
<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-settings me-2"></i>{{ __('Configuration du canal') }}</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <div class="small text-muted mb-1">{{ __('Taux marge') }}</div>
                            <div class="fw-bold fs-3 text-primary">{{ number_format(($channel->margin_rate ?? 0) * 100, 1) }}%</div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="bg-info bg-opacity-10 rounded p-3">
                            <div class="small text-muted mb-1">{{ __('Taux achat') }}</div>
                            <div class="fw-bold fs-3 text-info">{{ number_format(($channel->buy_rate ?? 0) * 100, 1) }}%</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light rounded p-3">
                            <div class="small text-muted mb-2">{{ __('Repartition des marges') }}</div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-danger" style="width:{{ ($channel->debt_share ?? 0) * 100 }}%"></div>
                                    <div class="progress-bar bg-success" style="width:{{ ($channel->channel_share ?? 0) * 100 }}%"></div>
                                    <div class="progress-bar bg-primary" style="width:{{ ($channel->owner_share ?? 0) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-danger"><i class="ti ti-point-filled"></i>{{ __('Dette') }} {{ number_format(($channel->debt_share ?? 0) * 100) }}%</span>
                                <span class="text-success"><i class="ti ti-point-filled"></i>{{ __('Canal') }} {{ number_format(($channel->channel_share ?? 0) * 100) }}%</span>
                                <span class="text-primary"><i class="ti ti-point-filled"></i>{{ __('Proprio') }} {{ number_format(($channel->owner_share ?? 0) * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Infos') }}</h6></div>
            <div class="card-body small">
                <div class="mb-2"><span class="text-muted">{{ __('Statut') }}:</span> @if($channel->is_active)<span class="badge bg-success">{{ __('Actif') }}</span>@else<span class="badge bg-secondary">{{ __('Inactif') }}</span>@endif</div>
                <div class="mb-2"><span class="text-muted">{{ __('Entrepot') }}:</span> {{ $channel->warehouse?->name ?? '—' }}</div>
                <div class="mb-2"><span class="text-muted">{{ __('Portail') }}:</span> @if($channel->portal_enabled)<span class="badge bg-info">{{ __('Active') }}</span>@else<span class="text-muted">{{ __('Desactive') }}</span>@endif</div>
                <div class="mb-2"><span class="text-muted">{{ __('Commandes') }}:</span> <span class="fw-bold">{{ $channel->orders_count ?? $channel->orders()->count() }}</span></div>
                <div><span class="text-muted">{{ __('Produits') }}:</span> <span class="fw-bold">{{ $channel->productPrices()->count() }}</span></div>
            </div>
        </div>
    </div>
</div>

{{-- KPIs Marges --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-3">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Marge totale') }}</small>
                <div class="fw-bold fs-4 text-primary">{{ number_format($summary['total_margin'] ?? 0, 0, ',', ' ') }} {{ $currencyCode }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-danger border-3">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Part dette') }}</small>
                <div class="fw-bold fs-4 text-danger">{{ number_format($summary['total_debt'] ?? 0, 0, ',', ' ') }} {{ $currencyCode }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-3">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Part') }} {{ $channel->name }}</small>
                <div class="fw-bold fs-4 text-success">{{ number_format($summary['total_channel'] ?? 0, 0, ',', ' ') }} {{ $currencyCode }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-3">
            <div class="card-body py-3 text-center">
                <small class="text-muted">{{ __('Part proprietaire') }}</small>
                <div class="fw-bold fs-4 text-info">{{ number_format($summary['total_owner'] ?? 0, 0, ',', ' ') }} {{ $currencyCode }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Margin Logs --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-receipt me-2"></i>{{ __('Derniers mouvements de marge') }} <span class="badge bg-primary ms-1">{{ $recentLogs->count() }}</span></h6>
        <a href="{{ route('eshop360.channels.margins', [$slug, $channel]) }}" class="btn btn-sm btn-outline-primary">{{ __('Voir tout') }}</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Commande') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th class="text-end">{{ __('Marge') }}</th>
                        <th class="text-end">{{ __('Part dette') }}</th>
                        <th class="text-end">{{ __('Part canal') }}</th>
                        <th class="text-end">{{ __('Part proprio') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="small text-muted">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="fw-medium"><a href="{{ $log->order ? route('eshop360.orders.show', [$slug, $log->order]) : '#' }}" class="text-decoration-none">{{ $log->order?->reference ?? '—' }}</a></td>
                            <td class="small">{{ $log->order?->customer?->name ?? '—' }}</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($log->total_margin ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end small text-danger">{{ number_format($log->debt_part ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end small text-success">{{ number_format($log->channel_part ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end small text-info">{{ number_format($log->owner_part ?? 0, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-chart-bar-off fs-1 d-block mb-2"></i>{{ __('Aucun mouvement de marge.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
