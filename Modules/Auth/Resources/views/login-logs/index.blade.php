<x-dashboard::layouts.master
    :title="(isset($userMode) && $userMode ? 'Mon historique de connexion' : 'Historique des connexions') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="isset($userMode) && $userMode ? 'Mon historique de connexion' : 'Historique des connexions'">

    {{-- Filters (admin mode only) --}}
    @if(empty($userMode))
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('login-logs.index', $instance->slug) }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Utilisateur</label>
                    <input type="text"
                           name="user"
                           class="form-control"
                           placeholder="Nom ou e-mail"
                           value="{{ $filters['user'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="success" {{ ($filters['status'] ?? '') === 'success' ? 'selected' : '' }}>Succes</option>
                        <option value="failed" {{ ($filters['status'] ?? '') === 'failed' ? 'selected' : '' }}>Echec</option>
                        <option value="locked" {{ ($filters['status'] ?? '') === 'locked' ? 'selected' : '' }}>Verrouille</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date debut</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('login-logs.index', $instance->slug) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-x me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Logs table --}}
    <div class="card">
        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="ti ti-history fs-1 d-block mb-2"></i>
                    <p>Aucun historique de connexion.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                @if(empty($userMode))
                                    <th>Utilisateur</th>
                                @endif
                                <th>Adresse IP</th>
                                <th>Navigateur</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                @if(empty($userMode))
                                    <td>
                                        <div>
                                            <strong>{{ $log->user?->name ?? '—' }}</strong>
                                        </div>
                                        <small class="text-muted">{{ $log->user?->email }}</small>
                                    </td>
                                @endif
                                <td><code>{{ $log->ip_address }}</code></td>
                                <td>
                                    <span title="{{ $log->user_agent }}">
                                        {{ $log->user_agent ? \Illuminate\Support\Str::limit($log->user_agent, 60) : '—' }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->status === 'success')
                                        <span class="badge bg-success">Succes</span>
                                    @elseif($log->status === 'failed')
                                        <span class="badge bg-danger">Echec</span>
                                    @elseif($log->status === 'locked')
                                        <span class="badge bg-warning">Verrouille</span>
                                    @endif
                                </td>
                                <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="d-flex justify-content-center py-3">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

</x-dashboard::layouts.master>
