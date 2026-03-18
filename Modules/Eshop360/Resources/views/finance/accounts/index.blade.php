<x-dashboard::layouts.master
    :title="__('Comptes financiers') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Comptes financiers')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Comptes financiers') }}</h4>
            <h6>{{ __('Gerer vos comptes bancaires et caisses') }}</h6>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.expenses', $slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-download me-1"></i>{{ __('Exporter') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-account">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Ajouter un compte') }}
        </button>
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
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                        <i class="ti ti-wallet fs-3 text-white"></i>
                    </div>
                    <div>
                        <div class="text-white-50 small">{{ __('Solde total') }}</div>
                        <h3 class="fw-bold mb-0">{{ number_format($totalBalance ?? 0, 0, ',', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Comptes en cartes --}}
<div class="row g-3">
    @forelse($accounts as $account)
        @php
            $typeLabels = ['cash' => 'Caisse', 'bank' => 'Banque', 'mobile_money' => 'Mobile Money', 'credit' => 'Autre'];
            $typeColors = ['cash' => 'success', 'bank' => 'primary', 'mobile_money' => 'info', 'credit' => 'warning'];
            $typeIcons = ['cash' => 'ti-cash', 'bank' => 'ti-building-bank', 'mobile_money' => 'ti-device-mobile', 'credit' => 'ti-credit-card'];
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-{{ $typeColors[$account->type] ?? 'secondary' }}-subtle p-2 me-2">
                                <i class="ti {{ $typeIcons[$account->type] ?? 'ti-wallet' }} fs-4 text-{{ $typeColors[$account->type] ?? 'secondary' }}"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $account->name }}</h6>
                                <span class="badge bg-{{ $typeColors[$account->type] ?? 'secondary' }}-subtle text-{{ $typeColors[$account->type] ?? 'secondary' }} mt-1">{{ $typeLabels[$account->type] ?? ucfirst($account->type) }}</span>
                            </div>
                        </div>
                        @if($account->is_active)
                            <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                        @endif
                    </div>

                    @if($account->account_number)
                        <div class="small text-muted mb-2">
                            <i class="ti ti-hash me-1"></i>{{ $account->account_number }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <div class="small text-muted">{{ __('Solde') }}</div>
                        <h4 class="fw-bold mb-0 {{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($account->balance, 0, ',', ' ') }}
                        </h4>
                    </div>

                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('eshop360.finance.accounts.show', [$slug, $account]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir detail') }}">
                            <i class="ti ti-eye me-1"></i>{{ __('Detail') }}
                        </a>
                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#deposit-{{ $account->id }}" title="{{ __('Depot') }}">
                            <i class="ti ti-arrow-down-left me-1"></i>{{ __('Depot') }}
                        </button>
                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#withdraw-{{ $account->id }}" title="{{ __('Retrait') }}">
                            <i class="ti ti-arrow-up-right me-1"></i>{{ __('Retrait') }}
                        </button>
                        <form action="{{ route('eshop360.finance.accounts.destroy', [$slug, $account]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce compte ?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="{{ __('Supprimer') }}"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Depot Modal --}}
        <div class="modal fade" id="deposit-{{ $account->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-arrow-down-left me-1 text-success"></i>{{ __('Depot sur') }}: {{ $account->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('eshop360.finance.accounts.deposit', [$slug, $account]) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <input type="text" name="description" class="form-control" placeholder="{{ __('Motif du depot') }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                            <button type="submit" class="btn btn-success">{{ __('Deposer') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Retrait Modal --}}
        <div class="modal fade" id="withdraw-{{ $account->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-arrow-up-right me-1 text-warning"></i>{{ __('Retrait de') }}: {{ $account->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('eshop360.finance.accounts.withdraw', [$slug, $account]) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <input type="text" name="description" class="form-control" placeholder="{{ __('Motif du retrait') }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                            <button type="submit" class="btn btn-warning">{{ __('Retirer') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="ti ti-wallet-off fs-1 d-block mb-2"></i>
                    {{ __('Aucun compte financier trouve.') }}
                </div>
            </div>
        </div>
    @endforelse
</div>

{{-- Add Account Modal --}}
<div class="modal fade" id="add-account" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Nouveau compte') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.finance.accounts.store', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="255" placeholder="{{ __('Nom du compte') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">{{ __('Selectionner un type') }}</option>
                            <option value="cash">{{ __('Caisse') }}</option>
                            <option value="bank">{{ __('Banque') }}</option>
                            <option value="mobile_money">{{ __('Mobile Money') }}</option>
                            <option value="credit">{{ __('Autre') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Numero de compte') }}</label>
                        <input type="text" name="account_number" class="form-control" placeholder="{{ __('Optionnel') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Solde initial') }}</label>
                        <input type="number" name="balance" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="{{ __('Description du compte') }}"></textarea>
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
