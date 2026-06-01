<x-dashboard::layouts.master
    :title="$account->name . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail du compte')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
    $typeLabels = ['cash' => 'Caisse', 'bank' => 'Banque', 'mobile_money' => 'Mobile Money', 'other' => 'Autre'];
    $typeColors = ['cash' => 'success', 'bank' => 'primary', 'mobile_money' => 'info', 'other' => 'warning'];
    $typeIcons = ['cash' => 'ti-cash', 'bank' => 'ti-building-bank', 'mobile_money' => 'ti-device-mobile', 'other' => 'ti-credit-card'];
    $txTypeLabels = ['deposit' => 'Depot', 'withdrawal' => 'Retrait', 'transfer_in' => 'Transfert entrant', 'transfer_out' => 'Transfert sortant'];
    $txTypeColors = ['deposit' => 'success', 'withdrawal' => 'danger', 'transfer_in' => 'info', 'transfer_out' => 'warning'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti {{ $typeIcons[$account->type] ?? 'ti-wallet' }} me-2"></i>{{ $account->name }}</h4>
        <p class="text-muted mb-0">
            <span class="badge bg-{{ $typeColors[$account->type] ?? 'secondary' }}">{{ __($typeLabels[$account->type] ?? $account->type) }}</span>
            @if($account->bank_name) — {{ $account->bank_name }} @endif
            @if($account->account_number) — {{ $account->account_number }} @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#deposit-modal"><i class="ti ti-arrow-down-left me-1"></i>{{ __('Depot') }}</button>
        <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#withdraw-modal"><i class="ti ti-arrow-up-right me-1"></i>{{ __('Retrait') }}</button>
        <a href="{{ route('eshop360.finance.accounts.index', $slug) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Stats --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body py-3 text-center">
                <div class="text-white-50">{{ __('Solde actuel') }}</div>
                <h2 class="fw-bold mb-0">{{ number_format($account->balance, 0, ',', ' ') }} {{ $currency }}</h2>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="ti ti-arrow-down-left text-success fs-4"></i></div>
                    <div class="ms-3">
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($stats->total_deposits, 0, ',', ' ') }}</h4>
                        <span class="text-muted">{{ __('Depots') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="ti ti-arrow-up-right text-danger fs-4"></i></div>
                    <div class="ms-3">
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($stats->total_withdrawals, 0, ',', ' ') }}</h4>
                        <span class="text-muted">{{ __('Retraits') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="ti ti-arrows-exchange text-info fs-4"></i></div>
                    <div class="ms-3">
                        <h4 class="fw-bold mb-0">{{ $stats->transaction_count }}</h4>
                        <span class="text-muted">{{ __('Operations') }}</span>
                    </div>
                </div>
                @if($stats->last_transaction)
                <div class="mt-1 text-muted">{{ __('Derniere') }}: {{ $stats->last_transaction->format('d/m/Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Description...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Type') }}</label>
                <select name="type" class="form-select form-select-sm acct-filter-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    <option value="deposit" @selected(request('type') === 'deposit')>{{ __('Depot') }}</option>
                    <option value="withdrawal" @selected(request('type') === 'withdrawal')>{{ __('Retrait') }}</option>
                    <option value="transfer_in" @selected(request('type') === 'transfer_in')>{{ __('Transfert entrant') }}</option>
                    <option value="transfer_out" @selected(request('type') === 'transfer_out')>{{ __('Transfert sortant') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-1">{{ __('Periode') }}</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    <span class="input-group-text">-</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search','type','date_from','date_to']))
                <div class="col-auto"><a href="{{ route('eshop360.finance.accounts.show', [$slug, $account]) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Reset') }}</a></div>
            @endif
        </form>
    </div>
</div>

{{-- Transactions --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="ti ti-list me-2"></i>{{ __('Historique des operations') }} <span class="badge bg-primary ms-1">{{ $transactions->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Par') }}</th>
                        <th class="text-end">{{ __('Montant') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $isCredit = in_array($tx->type, ['deposit', 'transfer_in']);
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-{{ $txTypeColors[$tx->type] ?? 'secondary' }}">{{ __($txTypeLabels[$tx->type] ?? $tx->type) }}</span></td>
                            <td>{{ $tx->notes ?? '—' }}</td>
                            <td class="text-muted">{{ $tx->user?->full_name ?? $tx->user?->name ?? '—' }}</td>
                            <td class="text-end fw-bold {{ $isCredit ? 'text-success' : 'text-danger' }}">
                                {{ $isCredit ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} {{ $currency }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="ti ti-receipt-off fs-1 d-block mb-2"></i>{{ __('Aucune operation trouvee.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())<div class="p-3">{{ $transactions->links() }}</div>@endif
    </div>
</div>

{{-- Deposit Modal --}}
<div class="modal fade" id="deposit-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-arrow-down-left me-1 text-success"></i>{{ __('Depot') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.accounts.deposit', [$slug, $account]) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div>
            </div>
            <div class="mb-3"><label class="form-label">{{ __('Description') }}</label><input type="text" name="notes" class="form-control" placeholder="{{ __('Motif du depot') }}"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-success"><i class="ti ti-arrow-down-left me-1"></i>{{ __('Deposer') }}</button></div>
    </form>
</div></div></div>

{{-- Withdraw Modal --}}
<div class="modal fade" id="withdraw-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-arrow-up-right me-1 text-warning"></i>{{ __('Retrait') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.finance.accounts.withdraw', [$slug, $account]) }}" method="POST">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                <div class="input-group"><input type="number" name="amount" class="form-control" step="1" min="1" required><span class="input-group-text">{{ $currency }}</span></div>
                <div class="text-muted mt-1">{{ __('Solde disponible') }}: <strong>{{ number_format($account->balance, 0, ',', ' ') }} {{ $currency }}</strong></div>
            </div>
            <div class="mb-3"><label class="form-label">{{ __('Description') }}</label><input type="text" name="notes" class="form-control" placeholder="{{ __('Motif du retrait') }}"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-warning"><i class="ti ti-arrow-up-right me-1"></i>{{ __('Retirer') }}</button></div>
    </form>
</div></div></div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.acct-filter-select2').each(function () {
        $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' })
            .on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
