<x-dashboard::layouts.master
    :title="__('Prets') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Prets')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Prets') }}</h4>
            <h6>{{ __('Suivi des prets accordes et recus') }}</h6>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-loan">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter un pret') }}
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.finance.loans.index', $slug) }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Rechercher (nom, reference...)') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Rembourse') }}</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>{{ __('En retard') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Annule') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ __('Tous les types') }}</option>
                    <option value="given" {{ request('type') === 'given' ? 'selected' : '' }}>{{ __('Accorde') }}</option>
                    <option value="received" {{ request('type') === 'received' ? 'selected' : '' }}>{{ __('Recu') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'status', 'type']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.finance.loans.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="ti ti-cash-banknote fs-4 me-2 text-primary"></i>
            <h5 class="mb-0 fw-bold">{{ __('Liste des prets') }}</h5>
            <span class="badge bg-primary-subtle text-primary ms-2">{{ $loans->total() ?? $loans->count() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th class="text-end">{{ __('Montant') }}</th>
                        <th class="text-center">{{ __('Taux interet') }}</th>
                        <th>{{ __('Duree') }}</th>
                        <th class="text-end">{{ __('Paye') }}</th>
                        <th class="text-end">{{ __('Reste') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        @php
                            $typeLabels = ['given' => 'Accorde', 'received' => 'Recu'];
                            $typeColors = ['given' => 'info', 'received' => 'warning'];
                            $statusLabels = ['active' => 'Actif', 'paid' => 'Rembourse', 'overdue' => 'En retard', 'cancelled' => 'Annule'];
                            $statusColors = ['active' => 'primary', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('eshop360.finance.loans.show', [$slug, $loan]) }}" class="fw-medium">{{ $loan->reference }}</a>
                            </td>
                            <td>
                                @php
                                    $partyLabels = ['customer' => 'Client', 'supplier' => 'Fournisseur', 'employee' => 'Employe'];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$loan->type] ?? 'secondary' }}-subtle text-{{ $typeColors[$loan->type] ?? 'secondary' }}">
                                    {{ $typeLabels[$loan->type] ?? ucfirst($loan->type) }}
                                </span>
                                @if($loan->party_type)
                                    <span class="small text-muted d-block">{{ $partyLabels[$loan->party_type] ?? ucfirst($loan->party_type) }}</span>
                                @endif
                            </td>
                            <td>{{ $loan->contact_name }}</td>
                            <td class="text-end fw-bold">{{ number_format($loan->amount, 0, ',', ' ') }}</td>
                            <td class="text-center small">{{ $loan->interest_rate ? $loan->interest_rate . '%' : '—' }}</td>
                            <td class="small">
                                {{ $loan->date->format('d/m/Y') }}
                                @if($loan->due_date)
                                    <span class="text-muted">→ {{ $loan->due_date->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td class="text-end text-success">{{ number_format($loan->paid_amount ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end text-danger fw-bold">{{ number_format($loan->remaining_amount ?? ($loan->amount - ($loan->paid_amount ?? 0)), 0, ',', ' ') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColors[$loan->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$loan->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$loan->status] ?? ucfirst($loan->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.finance.loans.show', [$slug, $loan]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir detail') }}"><i class="ti ti-eye"></i></a>
                                    @if($loan->status === 'active')
                                        <form action="{{ route('eshop360.finance.loans.destroy', [$slug, $loan]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Annuler ce pret ?') }}')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Annuler') }}"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="ti ti-cash-banknote-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun pret trouve.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
            <div class="p-3">{{ $loans->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Loan Modal --}}
<div class="modal fade" id="add-loan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouveau pret') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.finance.loans.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type de pret') }} <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">{{ __('Selectionner un type') }}</option>
                            <option value="given">{{ __('Accorde (prete)') }}</option>
                            <option value="received">{{ __('Recu (emprunte)') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type de tiers') }}</label>
                        <select name="party_type" class="form-select">
                            <option value="">{{ __('Selectionner') }}</option>
                            <option value="customer">{{ __('Client') }}</option>
                            <option value="supplier">{{ __('Fournisseur') }}</option>
                            <option value="employee">{{ __('Employe') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom du contact') }} <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" required placeholder="{{ __('Nom complet') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Taux d\'interet (%)') }}</label>
                            <input type="number" name="interest_rate" class="form-control" step="0.01" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Echeance') }}</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
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
