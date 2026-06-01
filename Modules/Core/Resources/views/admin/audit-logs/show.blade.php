<x-dashboard::layouts.master
    :title="'Audit #' . $log->id . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Detail de l'audit">

    <div class="mb-3">
        <a href="{{ route('audit-logs.index', $instance->slug) }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour au journal
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Entree #{{ $log->id }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td class="fw-medium text-muted" style="width:200px;">Date</td>
                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Utilisateur</td>
                            <td>{{ $log->user_name ?? $log->user_email ?? '—' }} (ID: {{ $log->user_id ?? '—' }})</td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Action</td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Modele</td>
                            <td>{{ $log->model }}</td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">ID du modele</td>
                            <td>{{ $log->model_id ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">Adresse IP</td>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-medium text-muted">User Agent</td>
                            <td class="small">{{ $log->user_agent ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Values Diff --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-danger bg-opacity-10">
                    <h6 class="card-title mb-0 text-danger">Anciennes valeurs</h6>
                </div>
                <div class="card-body">
                    @if(!empty($oldValues))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Champ</th>
                                        <th>Valeur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($oldValues as $key => $value)
                                    <tr class="{{ isset($newValues[$key]) && $newValues[$key] !== $value ? 'table-danger' : '' }}">
                                        <td class="fw-medium">{{ $key }}</td>
                                        <td>
                                            @if(is_array($value))
                                                <pre class="mb-0 small">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @else
                                                {{ $value ?? 'null' }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucune ancienne valeur enregistree.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success bg-opacity-10">
                    <h6 class="card-title mb-0 text-success">Nouvelles valeurs</h6>
                </div>
                <div class="card-body">
                    @if(!empty($newValues))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Champ</th>
                                        <th>Valeur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($newValues as $key => $value)
                                    <tr class="{{ isset($oldValues[$key]) && $oldValues[$key] !== $value ? 'table-success' : '' }}">
                                        <td class="fw-medium">{{ $key }}</td>
                                        <td>
                                            @if(is_array($value))
                                                <pre class="mb-0 small">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @else
                                                {{ $value ?? 'null' }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucune nouvelle valeur enregistree.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Combined diff view --}}
    @if(!empty($oldValues) && !empty($newValues))
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Comparaison des changements</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Champ</th>
                            <th>Avant</th>
                            <th>Apres</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues))); @endphp
                        @foreach($allKeys as $key)
                        @php
                            $old = $oldValues[$key] ?? null;
                            $new = $newValues[$key] ?? null;
                            $changed = $old !== $new;
                        @endphp
                        <tr class="{{ $changed ? 'table-warning' : '' }}">
                            <td class="fw-medium">{{ $key }}</td>
                            <td>
                                @if(is_array($old))
                                    <pre class="mb-0 small">{{ json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    {{ $old ?? '—' }}
                                @endif
                            </td>
                            <td>
                                @if(is_array($new))
                                    <pre class="mb-0 small">{{ json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    {{ $new ?? '—' }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</x-dashboard::layouts.master>
