<x-dashboard::layouts.master
    :title="__('Marges') . ' — ' . ($channel->name ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Marges') . ' — ' . ($channel->name ?? '')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Marges &mdash; {{ $channel->name }}</h4>
            <h6>{{ __('Détail des marges et répartition des revenus') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.channels.show', [$instance->slug ?? '', $channel]) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour au canal</a>
    </div>
</div>

{{-- Date Range Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('eshop360.channels.margins', [$instance->slug ?? '', $channel]) }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Du') }}</label>
                    <input type="date" name="from" class="form-control" value="{{ $from ?? now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Au') }}</label>
                    <input type="date" name="to" class="form-control" value="{{ $to ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Marge totale') }}</h6>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($summary['total_margin'] ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Part dette') }}</h6>
                <h3 class="fw-bold text-danger mb-0">{{ number_format($summary['total_debt'] ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Part {{ $channel->name }}</h6>
                <h3 class="fw-bold text-success mb-0">{{ number_format($summary['total_channel'] ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Part propriétaire') }}</h6>
                <h3 class="fw-bold text-info mb-0">{{ number_format($summary['total_owner'] ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- Logs Table --}}
<div class="card table-list-card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Journal des marges') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Réf. commande') }}</th>
                        <th>{{ __('Marge totale') }}</th>
                        <th>{{ __('Part canal') }}</th>
                        <th>{{ __('Part propriétaire') }}</th>
                        <th>{{ __('Type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->description ?? '—' }}</td>
                        <td>{{ $log->order_ref ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($log->margin ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        <td>{{ number_format($log->channel_part ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        <td>{{ number_format($log->owner_part ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                        <td><span class="badge bg-secondary">{{ $log->type ?? '—' }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">{{ __('Aucun mouvement de marge pour cette période.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-3">{{ $logs->links() }}</div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
