<x-dashboard::layouts.master
    :title="__('Revenus') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Revenus')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Revenus') }}</h4>
            <h6>{{ __('Suivi des revenus par source') }}</h6>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.expenses', $slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <a href="{{ route('eshop360.finance.incomes.sources', $slug) }}" class="btn btn-outline-primary">
            <i class="ti ti-list me-1"></i>{{ __('Sources') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-income">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter un revenu') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.finance.incomes.index', $slug) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher un revenu...') }}">
            </div>
            <div class="col-md-2">
                <select name="source_id" class="form-select form-select-sm">
                    <option value="">{{ __('Toutes les sources') }}</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}" {{ request('source_id') == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'source_id', 'date_from', 'date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.finance.incomes.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPI --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                        <i class="ti ti-arrow-up-right fs-3 text-white"></i>
                    </div>
                    <div>
                        <div class="text-white-50 small">{{ __('Total revenus du mois') }}</div>
                        <h3 class="fw-bold mb-0">{{ number_format($totalIncomes ?? 0, 0, ',', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="ti ti-trending-up fs-4 me-2 text-success"></i>
            <h5 class="mb-0 fw-bold">{{ __('Liste des revenus') }}</h5>
            <span class="badge bg-success-subtle text-success ms-2">{{ $incomes->total() ?? $incomes->count() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Compte') }}</th>
                        <th class="text-end">{{ __('Montant') }}</th>
                        <th>{{ __('Par') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomes as $income)
                        <tr>
                            <td class="small">{{ $income->date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info">{{ $income->source->name ?? '—' }}</span>
                            </td>
                            <td>{{ $income->description }}</td>
                            <td class="small text-muted">{{ $income->account->name ?? '—' }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($income->amount, 0, ',', ' ') }}</td>
                            <td class="small text-muted">{{ $income->user->name ?? '—' }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-income-{{ $income->id }}" title="{{ __('Modifier') }}"><i class="ti ti-edit"></i></button>
                                    <form action="{{ route('eshop360.finance.incomes.destroy', [$slug, $income]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce revenu ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="ti ti-trending-down fs-1 d-block mb-2"></i>
                                {{ __('Aucun revenu trouve.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($incomes->hasPages())
            <div class="p-3">{{ $incomes->links() }}</div>
        @endif
    </div>
</div>

{{-- Edit Income Modals --}}
@foreach($incomes as $income)
<div class="modal fade" id="edit-income-{{ $income->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Modifier le revenu') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.finance.incomes.update', [$slug, $income]) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ $income->date->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Source') }} <span class="text-danger">*</span></label>
                        <select name="source_id" class="form-select" required>
                            <option value="">{{ __('Selectionner une source') }}</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ $income->source_id == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Compte') }} <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select" required>
                            <option value="">{{ __('Selectionner un compte') }}</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $income->account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="{{ $income->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" value="{{ $income->description }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Reference') }}</label>
                        <input type="text" name="reference" class="form-control" value="{{ $income->reference }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $income->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- Add Income Modal --}}
<div class="modal fade" id="add-income" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouveau revenu') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.finance.incomes.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Source') }} <span class="text-danger">*</span></label>
                        <select name="source_id" class="form-select" required>
                            <option value="">{{ __('Selectionner une source') }}</option>
                            @foreach($sources as $source)
                                <option value="{{ $source->id }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Compte') }} <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select" required>
                            <option value="">{{ __('Selectionner un compte') }}</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ number_format($account->balance, 0, ',', ' ') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required placeholder="{{ __('Description du revenu') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Reference') }}</label>
                        <input type="text" name="reference" class="form-control" placeholder="{{ __('Optionnel') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Notes supplementaires') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Creer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
