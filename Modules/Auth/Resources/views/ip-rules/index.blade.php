<x-dashboard::layouts.master
    :title="'Regles IP — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Regles IP (Whitelist / Blacklist)">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">

        {{-- Add new rule form --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ajouter une regle</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('ip-rules.store', $instance->slug) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Adresse IP <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="ip_address"
                                   class="form-control"
                                   placeholder="192.168.1.1"
                                   value="{{ old('ip_address') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="deny" {{ old('type') === 'deny' ? 'selected' : '' }}>Refuser (Deny)</option>
                                <option value="allow" {{ old('type') === 'allow' ? 'selected' : '' }}>Autoriser (Allow)</option>
                            </select>
                            <small class="form-text text-muted">
                                Si des regles "Autoriser" existent, seules les IP autorisees pourront acceder (mode whitelist).
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <input type="text"
                                   name="note"
                                   class="form-control"
                                   placeholder="Ex: Bureau principal"
                                   value="{{ old('note') }}"
                                   maxlength="255">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-plus me-1"></i>Ajouter la regle
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Rules list --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Regles actives</h5>
                    <span class="badge bg-secondary">{{ $rules->count() }} regle(s)</span>
                </div>
                <div class="card-body p-0">
                    @if($rules->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="ti ti-shield-off fs-1 d-block mb-2"></i>
                            <p>Aucune regle IP configuree.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Adresse IP</th>
                                        <th>Type</th>
                                        <th>Utilisateur</th>
                                        <th>Note</th>
                                        <th>Cree par</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rules as $rule)
                                    <tr>
                                        <td><code>{{ $rule->ip_address }}</code></td>
                                        <td>
                                            @if($rule->type === 'deny')
                                                <span class="badge bg-danger">Refuser</span>
                                            @else
                                                <span class="badge bg-success">Autoriser</span>
                                            @endif
                                        </td>
                                        <td>{{ $rule->user?->name ?? $rule->user?->email ?? '—' }}</td>
                                        <td>{{ $rule->note ?? '—' }}</td>
                                        <td>{{ $rule->creator?->name ?? $rule->creator?->email ?? '—' }}</td>
                                        <td>{{ $rule->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            <form method="POST"
                                                  action="{{ route('ip-rules.destroy', [$instance->slug, $rule->id]) }}"
                                                  onsubmit="return confirm('Supprimer cette regle IP ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</x-dashboard::layouts.master>
