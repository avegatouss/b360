@php $slug = $instance->slug ?? ''; @endphp

<x-dashboard::layouts.master
    :title="__('Devises') . ' — ' . ($instance->name ?? $slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Gestion des devises')">

    @php
        $defaultCurrency = collect($currencies)->firstWhere('is_default', true);
    @endphp

    {{-- Page header --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="ti ti-currency-dollar me-2"></i>{{ __('Devises') }}</h4>
            <p class="text-muted mb-0">{{ __('Gerer les devises et taux de change') }}</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('currencies.update-rates', $slug) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-info">
                    <i class="ti ti-refresh me-1"></i>{{ __('Actualiser les taux') }}
                </button>
            </form>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
                <i class="ti ti-plus me-1"></i>{{ __('Ajouter') }}
            </button>
        </div>
    </div>

    {{-- Session alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI row --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="ti ti-coins fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ count($currencies) }}</h6>
                        <small class="text-muted">{{ __('Devises configurees') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="ti ti-star fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $defaultCurrency ? $defaultCurrency->name . ' (' . $defaultCurrency->code . ')' : '—' }}</h6>
                        <small class="text-muted">{{ __('Devise par defaut') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Currencies table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Nom') }}</th>
                            <th>{{ __('Symbole') }}</th>
                            <th>{{ __('Decimales') }}</th>
                            <th>{{ __('Taux') }}</th>
                            <th>{{ __('MAJ auto') }}</th>
                            <th>{{ __('Derniere MAJ') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th style="width: 180px;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                        <tr>
                            <td>
                                <strong>{{ $currency->code }}</strong>
                                @if($currency->is_default)
                                    <span class="badge bg-primary ms-1">{{ __('Defaut') }}</span>
                                @endif
                            </td>
                            <td>{{ $currency->name }}</td>
                            <td>{{ $currency->symbol }}</td>
                            <td>{{ $currency->decimals }}</td>
                            <td>
                                @if($currency->is_default)
                                    <span class="text-muted">1,000000 ({{ __('base') }})</span>
                                @else
                                    {{ number_format($currency->rate, 6, ',', ' ') }}
                                @endif
                            </td>
                            <td>
                                @if($currency->auto_update)
                                    <span class="badge bg-success">{{ __('Oui') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Non') }}</span>
                                @endif
                            </td>
                            <td>{{ $currency->rate_updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                @if($currency->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCurrency{{ $currency->id }}"
                                            title="{{ __('Modifier') }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    @if(!$currency->is_default)
                                        <form method="POST" action="{{ route('currencies.set-default', [$slug, $currency->id]) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ __('Definir par defaut') }}">
                                                <i class="ti ti-star"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('currencies.destroy', [$slug, $currency->id]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('{{ __('Supprimer cette devise ?') }}')"
                                                    title="{{ __('Supprimer') }}">
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
                                <i class="ti ti-coins-off fs-1 d-block mb-2"></i>
                                {{ __('Aucune devise configuree. Ajoutez-en une pour commencer.') }}
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
            <form method="POST" action="{{ route('currencies.store', $slug) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-plus me-2"></i>{{ __('Ajouter une devise') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Code') }}</label>
                                <input type="text" name="code" class="form-control" required placeholder="USD" maxlength="10">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Symbole') }}</label>
                                <input type="text" name="symbol" class="form-control" required placeholder="$" maxlength="10">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Nom') }}</label>
                            <input type="text" name="name" class="form-control" required placeholder="{{ __('Dollar americain') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Decimales') }}</label>
                                <input type="number" name="decimals" class="form-control" value="2" min="0" max="4" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Taux de change') }}</label>
                                <input type="number" name="rate" class="form-control" step="0.000001" value="1.000000" required>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="auto_update" class="form-check-input" id="autoUpdateNew" value="1" checked>
                            <label class="form-check-label" for="autoUpdateNew">{{ __('Mise a jour automatique des taux') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Ajouter') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Currency Modals --}}
    @foreach($currencies as $currency)
    <div class="modal fade" id="editCurrency{{ $currency->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('currencies.update', [$slug, $currency->id]) }}">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-edit me-2"></i>{{ __('Modifier') }} {{ $currency->code }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Nom') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ $currency->name }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Symbole') }}</label>
                                <input type="text" name="symbol" class="form-control" value="{{ $currency->symbol }}" required maxlength="10">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Decimales') }}</label>
                                <input type="number" name="decimals" class="form-control" value="{{ $currency->decimals }}" min="0" max="4" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Taux de change') }}</label>
                            <input type="number" name="rate" class="form-control" step="0.000001" value="{{ $currency->rate }}" required>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="auto_update" class="form-check-input" id="autoUpdate{{ $currency->id }}"
                                   value="1" {{ $currency->auto_update ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoUpdate{{ $currency->id }}">{{ __('Mise a jour automatique des taux') }}</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive{{ $currency->id }}"
                                   value="1" {{ $currency->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive{{ $currency->id }}">{{ __('Active') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</x-dashboard::layouts.master>
