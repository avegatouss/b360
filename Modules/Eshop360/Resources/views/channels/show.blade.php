<x-dashboard::layouts.master
    :title="($channel->name ?? 'Canal') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    ::pageTitle="__('$channel->name')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $channel->name }}</h4>
            <h6>{{ $channel->code ?? $channel->slug }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.channels.margins', [$instance->slug ?? '', $channel]) }}" class="btn btn-info me-2"><i class="ti ti-chart-bar me-1"></i>Marges</a>
        <a href="{{ route('eshop360.channels.orders', [$instance->slug ?? '', $channel]) }}" class="btn btn-success me-2"><i class="ti ti-shopping-cart me-1"></i>Commandes</a>
        <a href="{{ route('eshop360.channels.edit', [$instance->slug ?? '', $channel]) }}" class="btn btn-warning me-2"><i data-feather="edit" class="me-1"></i>Modifier</a>
        <a href="{{ route('eshop360.channels.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour</a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Marge totale') }}</h6>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($summary['total_margin'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Part dette') }}</h6>
                <h3 class="fw-bold text-danger mb-0">{{ number_format($summary['total_debt'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Part {{ $channel->name }}</h6>
                <h3 class="fw-bold text-success mb-0">{{ number_format($summary['total_channel'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Part propriétaire') }}</h6>
                <h3 class="fw-bold text-info mb-0">{{ number_format($summary['total_owner'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
</div>

{{-- Recent Margin Logs --}}
<div class="card table-list-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">{{ __('Derniers mouvements de marge') }}</h5>
        <a href="{{ route('eshop360.channels.margins', [$instance->slug ?? '', $channel]) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Commande') }}</th>
                        <th>{{ __('Marge') }}</th>
                        <th>{{ __('Part canal') }}</th>
                        <th>{{ __('Part propriétaire') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->order_ref ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($log->margin ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($log->channel_part ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($log->owner_part ?? 0, 0, ',', ' ') }} XAF</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">{{ __('Aucun mouvement de marge.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
