<x-dashboard::layouts.master
    :title="__('Comptes financiers') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Comptes financiers')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
    $typeLabels = ['cash' => 'Caisse', 'bank' => 'Banque', 'mobile_money' => 'Mobile Money', 'other' => 'Autre'];
    $typeColors = ['cash' => 'success', 'bank' => 'primary', 'mobile_money' => 'info', 'other' => 'warning'];
    $typeIcons = ['cash' => 'ti-cash', 'bank' => 'ti-building-bank', 'mobile_money' => 'ti-device-mobile', 'other' => 'ti-credit-card'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-wallet me-2"></i>{{ __('Comptes financiers') }}</h4>
        <p class="text-muted mb-0">{{ __('Gerer vos comptes bancaires, caisses et portefeuilles') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#transfer-modal"><i class="ti ti-arrows-exchange me-1"></i>{{ __('Transfert') }}</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-account"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouveau compte') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-wallet text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpi->total_balance, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Solde total') }}</span>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-primary-subtle text-primary">{{ $kpi->total_accounts }} {{ __('comptes') }}</span>
                    <span class="badge bg-success-subtle text-success ms-1">{{ $kpi->active }} {{ __('actifs') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-arrow-down-left text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($kpi->total_deposits, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total depots') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-arrow-up-right text-danger fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($kpi->total_withdrawals, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total retraits') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-chart-bar text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpi->total_transactions }}</h3>
                        <span class="text-muted">{{ __('Operations') }}</span>
                    </div>
                </div>
                <div class="mt-2">
                    @foreach($kpi->by_type as $type => $bal)
                        @if($bal > 0)
                        <span class="badge bg-{{ $typeColors[$type] ?? 'secondary' }}-subtle text-{{ $typeColors[$type] ?? 'secondary' }}">{{ __($typeLabels[$type] ?? $type) }}: {{ number_format($bal, 0, ',', ' ') }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Account Cards --}}
<div class="row g-3">
    @forelse($accounts as $account)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 {{ !$account->is_active ? 'opacity-50' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-{{ $typeColors[$account->type] ?? 'secondary' }}-subtle p-2 me-2">
                                <i class="ti {{ $typeIcons[$account->type] ?? 'ti-wallet' }} fs-4 text-{{ $typeColors[$account->type] ?? 'secondary' }}"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $account->name }}</h6>
                                <span class="badge bg-{{ $typeColors[$account->type] ?? 'secondary' }}-subtle text-{{ $typeColors[$account->type] ?? 'secondary' }} mt-1">{{ __($typeLabels[$account->type] ?? ucfirst($account->type)) }}</span>
                            </div>
                        </div>
                        @if($account->is_active)
                            <span class="badge bg-success-subtle text-success">{{ __('Actif') }}</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Inactif') }}</span>
                        @endif
                    </div>

                    @if($account->account_number)
                        <div class="text-muted mb-2"><i class="ti ti-hash me-1"></i>{{ $account->account_number }}</div>
                    @endif
                    @if($account->bank_name)
                        <div class="text-muted mb-2"><i class="ti ti-building-bank me-1"></i>{{ $account->bank_name }}</div>
                    @endif

                    <div class="mb-3">
                        <div class="text-muted">{{ __('Solde') }}</div>
                        <h3 class="fw-bold mb-0 {{ $account->balance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($account->balance, 0, ',', ' ') }} {{ $currency }}
                        </h3>
                    </div>

                    <div class="text-muted mb-3">{{ $account->transactions_count }} {{ __('operations') }}</div>

                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('eshop360.finance.accounts.show', [$slug, $account]) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye me-1"></i>{{ __('Detail') }}</a>
                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#deposit-{{ $account->id }}"><i class="ti ti-arrow-down-left me-1"></i>{{ __('Depot') }}</button>
                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#withdraw-{{ $account->id }}"><i class="ti ti-arrow-up-right me-1"></i>{{ __('Retrait') }}</button>
                        <form action="{{ route('eshop360.finance.accounts.destroy', [$slug, $account]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce compte ?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deposit Modal --}}
        <div class="modal fade" id="deposit-{{ $account->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="ti ti-arrow-down-left me-1 text-success"></i>{{ __('Depot sur') }}: {{ $account->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('eshop360.finance.accounts.deposit', [$slug, $account]) }}" method="POST">@csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div>
                    </div>
                    <div class="mb-3"><label class="form-label">{{ __('Description') }}</label><input type="text" name="notes" class="form-control" placeholder="{{ __('Motif du depot') }}"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-success">{{ __('Deposer') }}</button></div>
            </form>
        </div></div></div>

        {{-- Withdraw Modal --}}
        <div class="modal fade" id="withdraw-{{ $account->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="ti ti-arrow-up-right me-1 text-warning"></i>{{ __('Retrait de') }}: {{ $account->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('eshop360.finance.accounts.withdraw', [$slug, $account]) }}" method="POST">@csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div>
                        <div class="text-muted mt-1">{{ __('Solde disponible') }}: <strong>{{ number_format($account->balance, 0, ',', ' ') }} {{ $currency }}</strong></div>
                    </div>
                    <div class="mb-3"><label class="form-label">{{ __('Description') }}</label><input type="text" name="notes" class="form-control" placeholder="{{ __('Motif du retrait') }}"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-warning">{{ __('Retirer') }}</button></div>
            </form>
        </div></div></div>
    @empty
        <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="ti ti-wallet-off fs-1 d-block mb-2"></i>{{ __('Aucun compte financier.') }}</div></div></div>
    @endforelse
</div>

{{-- Add Account Modal --}}
<div class="modal fade" id="add-account" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-wallet me-2"></i>{{ __('Nouveau compte') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.accounts.store', $slug) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required maxlength="255"></div>
            <div class="mb-3"><label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                <select name="type" class="form-select select2-account-modal" required>
                    <option value="">{{ __('Selectionner') }}</option>
                    <option value="cash">{{ __('Caisse') }}</option>
                    <option value="bank">{{ __('Banque') }}</option>
                    <option value="mobile_money">{{ __('Mobile Money') }}</option>
                    <option value="other">{{ __('Autre') }}</option>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">{{ __('Banque') }}</label><input type="text" name="bank_name" class="form-control" placeholder="{{ __('Nom de la banque (si applicable)') }}"></div>
            <div class="mb-3"><label class="form-label">{{ __('Numero de compte') }}</label><input type="text" name="account_number" class="form-control"></div>
            <div class="mb-3"><label class="form-label">{{ __('Solde initial') }}</label>
                <div class="input-group"><input type="number" name="balance" class="form-control" step="1" value="0" min="0"><span class="input-group-text">{{ $currency }}</span></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer') }}</button></div>
    </form>
</div></div></div>

{{-- Transfer Modal --}}
<div class="modal fade" id="transfer-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-arrows-exchange me-2"></i>{{ __('Transfert entre comptes') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.transfers.store', $slug) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Compte source') }} <span class="text-danger">*</span></label>
                <select name="from_account_id" class="form-select select2-account-modal" required>
                    <option value="">{{ __('Selectionner') }}</option>
                    @foreach($accounts->where('is_active', true) as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ number_format($acc->balance, 0, ',', ' ') }} {{ $currency }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label">{{ __('Compte destination') }} <span class="text-danger">*</span></label>
                <select name="to_account_id" class="form-select select2-account-modal" required>
                    <option value="">{{ __('Selectionner') }}</option>
                    @foreach($accounts->where('is_active', true) as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                    <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div>
                </div>
                <div class="col-md-4"><label class="form-label">{{ __('Frais') }}</label>
                    <input type="number" name="fee" class="form-control" step="1" min="0" value="0">
                </div>
            </div>
            <div class="mb-3 mt-3"><label class="form-label">{{ __('Notes') }}</label><input type="text" name="notes" class="form-control" placeholder="{{ __('Motif du transfert') }}"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-info"><i class="ti ti-arrows-exchange me-1"></i>{{ __('Transferer') }}</button></div>
    </form>
</div></div></div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.select2-account-modal').each(function () {
        var $el = $(this), $modal = $el.closest('.modal');
        $el.select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $modal.length ? $modal : undefined });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
