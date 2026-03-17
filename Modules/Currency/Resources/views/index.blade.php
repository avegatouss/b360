<x-dashboard::layouts.master
    :title="'Devises — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Gestion des devises">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title mb-0">Devises</h5>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('currencies.update-rates', $instance->slug) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-info">
                        <i class="ti ti-refresh me-1"></i>Actualiser les taux
                    </button>
                </form>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
                    <i class="ti ti-plus me-1"></i>Ajouter
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Symbole</th>
                            <th>Decimales</th>
                            <th>Taux</th>
                            <th>MAJ auto</th>
                            <th>Derniere MAJ</th>
                            <th>Statut</th>
                            <th style="width:180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                        <tr>
                            <td>
                                <strong>{{ $currency->code }}</strong>
                                @if($currency->is_default)
                                    <span class="badge bg-primary ms-1">Defaut</span>
                                @endif
                            </td>
                            <td>{{ $currency->name }}</td>
                            <td>{{ $currency->symbol }}</td>
                            <td>{{ $currency->decimals }}</td>
                            <td>
                                @if($currency->is_default)
                                    <span class="text-muted">1.000000 (base)</span>
                                @else
                                    {{ number_format($currency->rate, 6) }}
                                @endif
                            </td>
                            <td>
                                @if($currency->auto_update)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td>{{ $currency->rate_updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                @if($currency->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCurrency{{ $currency->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    @if(!$currency->is_default)
                                        <form method="POST" action="{{ route('currencies.set-default', [$instance->slug, $currency->id]) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Definir par defaut">
                                                <i class="ti ti-star"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('currencies.destroy', [$instance->slug, $currency->id]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Supprimer cette devise ?')">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Aucune devise configuree. Ajoutez-en une pour commencer.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add Currency Modal --}}
    <div class="modal fade" id="addCurrencyModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('currencies.store', $instance->slug) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter une devise</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" required placeholder="USD" maxlength="10">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Symbole</label>
                                <input type="text" name="symbol" class="form-control" required placeholder="$" maxlength="10">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="name" class="form-control" required placeholder="Dollar americain">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Decimales</label>
                                <input type="number" name="decimals" class="form-control" value="2" min="0" max="4" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Taux de change</label>
                                <input type="number" name="rate" class="form-control" step="0.000001" value="1.000000" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="auto_update" class="form-check-input" id="autoUpdateNew" value="1" checked>
                            <label class="form-check-label" for="autoUpdateNew">Mise a jour automatique des taux</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Currency Modals --}}
    @foreach($currencies as $currency)
    <div class="modal fade" id="editCurrency{{ $currency->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('currencies.update', [$instance->slug, $currency->id]) }}">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier {{ $currency->code }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="name" class="form-control" value="{{ $currency->name }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Symbole</label>
                                <input type="text" name="symbol" class="form-control" value="{{ $currency->symbol }}" required maxlength="10">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Decimales</label>
                                <input type="number" name="decimals" class="form-control" value="{{ $currency->decimals }}" min="0" max="4" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Taux de change</label>
                            <input type="number" name="rate" class="form-control" step="0.000001" value="{{ $currency->rate }}" required>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="auto_update" class="form-check-input" id="autoUpdate{{ $currency->id }}"
                                   value="1" {{ $currency->auto_update ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoUpdate{{ $currency->id }}">Mise a jour automatique des taux</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive{{ $currency->id }}"
                                   value="1" {{ $currency->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive{{ $currency->id }}">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</x-dashboard::layouts.master>
