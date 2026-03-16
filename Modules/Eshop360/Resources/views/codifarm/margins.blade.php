<x-dashboard::layouts.master
    :title="'Codifarm Margins — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Codifarm Margins">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Margins</h4>
            <h6>Margin logs and revenue distribution</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.codifarm.dashboard', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back to Dashboard</a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Margin</h6>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($summary['total_margin'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Codifarm Part</h6>
                <h3 class="fw-bold text-success mb-0">{{ number_format($summary['codifarm_part'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Saphir Part</h6>
                <h3 class="fw-bold text-info mb-0">{{ number_format($summary['saphir_part'] ?? 0, 0, ',', ' ') }} XAF</h3>
            </div>
        </div>
    </div>
</div>

{{-- Date Range Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('eshop360.codifarm.margins', $instance->slug ?? '') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Logs Table --}}
<div class="card table-list-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Margin Logs</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Order Ref</th>
                        <th>Margin</th>
                        <th>Codifarm</th>
                        <th>Saphir</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->order?->customer->name ?? 'Client comptoir' }}</td>
                        <td>{{ $log->order?->order_number ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($log->total_margin ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($log->codifarm_part ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($log->saphir_part ?? 0, 0, ',', ' ') }} XAF</td>
                        <td><span class="badge bg-secondary">{{ $log->order_id ? 'Commande' : 'Ajustement' }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No margin logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
