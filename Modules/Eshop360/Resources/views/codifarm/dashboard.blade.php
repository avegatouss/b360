<x-dashboard::layouts.master
    :title="__('Codifarm Dashboard') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Codifarm Dashboard')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Codifarm Dashboard') }}</h4>
            <h6>{{ __('Overview of margins, debts and distribution') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.codifarm.config', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-settings me-1"></i>Configuration</a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Total Margin') }}</h6>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($summary['total_margin'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Debt') }}</h6>
                <h3 class="fw-bold text-danger mb-0">{{ number_format($summary['debt'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Codifarm Part') }}</h6>
                <h3 class="fw-bold text-success mb-0">{{ number_format($summary['codifarm_part'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">{{ __('Saphir Part') }}</h6>
                <h3 class="fw-bold text-info mb-0">{{ number_format($summary['saphir_part'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
</div>

{{-- Recent Logs --}}
<div class="card table-list-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">{{ __('Recent Logs') }}</h5>
        <a href="{{ route('eshop360.codifarm.margins', $instance->slug ?? '') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Margin') }}</th>
                        <th>{{ __('Codifarm') }}</th>
                        <th>{{ __('Saphir') }}</th>
                        <th>{{ __('Type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs ?? [] as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            {{ $log->order?->order_number ?? 'Journal CODIFARM' }}
                            @if($log->order?->customer)
                                <div class="small text-muted">{{ $log->order->customer->name }}</div>
                            @endif
                        </td>
                        <td class="fw-bold">{{ number_format($log->total_margin ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($log->codifarm_part ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($log->saphir_part ?? 0, 0, ',', ' ') }} XAF</td>
                        <td><span class="badge bg-secondary">{{ $log->order_id ? 'Commande' : 'Ajustement' }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('No logs found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
