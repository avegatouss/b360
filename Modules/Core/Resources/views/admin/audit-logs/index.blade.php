<x-dashboard::layouts.master
    :title="'Journal d\'audit — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Journal d'audit">

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('audit-logs.index', $instance->slug) }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Utilisateur</label>
                    <select name="user" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($userList as $user)
                            <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                {{ $user->name ?? $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Modele</label>
                    <select name="model" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($models as $model)
                            <option value="{{ $model }}" {{ request('model') === $model ? 'selected' : '' }}>
                                {{ class_basename($model) }}
                            </option>
                        @endforeach
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
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="ti ti-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Audit Logs Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Historique des actions
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
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Modele</th>
                            <th>ID</th>
                            <th>IP</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->user_name ?? $log->user_email ?? '—' }}</td>
                            <td>
                                @php
                                    $actionColors = [
                                        'created' => 'bg-success',
                                        'updated' => 'bg-info',
                                        'deleted' => 'bg-danger',
                                        'login' => 'bg-primary',
                                        'logout' => 'bg-secondary',
                                    ];
                                    $color = $actionColors[$log->action] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $color }}">{{ $log->action }}</span>
                            </td>
                            <td>{{ class_basename($log->model) }}</td>
                            <td>{{ $log->model_id ?? '—' }}</td>
                            <td class="small">{{ $log->ip_address ?? '—' }}</td>
                            <td>
                                <a href="{{ route('audit-logs.show', [$instance->slug, $log->id]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ti ti-clipboard-off fs-24"></i>
                                <p class="mb-0 mt-2">Aucune entree d'audit trouvee.</p>
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
