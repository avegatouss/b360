<x-dashboard::layouts.master
    :title="'Cron Logs — B360'"
    :instance="$instance ?? null"
    pageTitle="Cron Logs">

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Total Executions</p>
                    <h3 class="fw-bold text-primary mb-0">{{ number_format($stats['total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Success</p>
                    <h3 class="fw-bold text-success mb-0">{{ number_format($stats['success']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Failed</p>
                    <h3 class="fw-bold text-danger mb-0">{{ number_format($stats['failed']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Avg Duration</p>
                    <h3 class="fw-bold text-info mb-0">{{ number_format($stats['avg_duration']) }} ms</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.cron-logs') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Commande</label>
                    <select name="command" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($commands as $cmd)
                            <option value="{{ $cmd }}" {{ request('command') === $cmd ? 'selected' : '' }}>
                                {{ $cmd }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date debut</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="ti ti-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Historique des executions cron
                <span class="badge bg-secondary ms-2">{{ $logs->total() }} entrees</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Commande</th>
                            <th>Statut</th>
                            <th>Duree</th>
                            <th>Sortie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td class="text-nowrap">{{ $log->executed_at->format('d/m/Y H:i:s') }}</td>
                            <td><code>{{ $log->command }}</code></td>
                            <td>
                                @if($log->status === 'success')
                                    <span class="badge bg-success">success</span>
                                @else
                                    <span class="badge bg-danger">failed</span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($log->duration_ms) }} ms</td>
                            <td>
                                @if($log->output)
                                    <span class="d-inline-block text-truncate" style="max-width: 300px;" title="{{ $log->output }}">
                                        {{ $log->output }}
                                    </span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="ti ti-clock-off fs-24"></i>
                                <p class="mb-0 mt-2">Aucune execution cron enregistree.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
