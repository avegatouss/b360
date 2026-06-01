<x-dashboard::layouts.master
    :title="__('Releve fournisseur') . ' — ' . ($supplier->name ?? '') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Releve fournisseur')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-file-text me-2"></i>{{ __('Releve de compte fournisseur') }}</h4>
        <p class="text-muted mb-0">{{ $supplier->name }} @if($supplier->company) — {{ $supplier->company }} @endif</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-info"><i class="ti ti-printer me-1"></i>{{ __('Imprimer') }}</button>
        <a href="{{ route('eshop360.suppliers.show', [$slug, $supplier]) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

{{-- Filtre par periode --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form action="{{ route('eshop360.suppliers.statement', [$slug, $supplier]) }}" method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label mb-1">{{ __('Periode') }}</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}">
                    <span class="input-group-text">-</span>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if($dateFrom || $dateTo)
            <div class="col-auto">
                <a href="{{ route('eshop360.suppliers.statement', [$slug, $supplier]) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Reset') }}</a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-shopping-cart text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($totals['purchases'] ?? 0, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total achats') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-cash text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($totals['payments'] ?? 0, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Total paiements') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-{{ ($totals['balance'] ?? 0) > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-scale text-{{ ($totals['balance'] ?? 0) > 0 ? 'danger' : 'secondary' }} fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ ($totals['balance'] ?? 0) > 0 ? 'text-danger' : '' }}">{{ number_format($totals['balance'] ?? 0, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Solde du') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-history text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($totals['opening_balance'] ?? 0, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Solde d\'ouverture') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tableau des mouvements --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="fw-bold mb-0"><i class="ti ti-list me-2"></i>{{ __('Mouvements') }} <span class="badge bg-primary ms-1">{{ $transactions->count() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-end">{{ __('Debit') }}</th>
                        <th class="text-end">{{ __('Credit') }}</th>
                        <th class="text-end">{{ __('Solde') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Solde d'ouverture --}}
                    <tr class="table-light">
                        <td colspan="6" class="fw-bold">{{ __('Solde d\'ouverture') }}</td>
                        <td class="text-end fw-bold">{{ number_format($totals['opening_balance'] ?? 0, 0, ',', ' ') }} {{ $currency }}</td>
                    </tr>

                    @forelse($transactions as $txn)
                        @php
                            $isPurchase = in_array($txn->type, ['purchase', 'import']);
                            $badgeColor = match($txn->type) { 'purchase' => 'primary', 'payment' => 'success', 'import' => 'info', default => 'secondary' };
                            $badgeLabel = match($txn->type) { 'purchase' => __('Achat'), 'payment' => __('Paiement'), 'import' => __('Importation'), default => $txn->type };
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $txn->date->format('d/m/Y') }}</td>
                            <td class="fw-medium">{{ $txn->reference ?? '—' }}</td>
                            <td><span class="badge bg-{{ $badgeColor }}">{{ $badgeLabel }}</span></td>
                            <td>{{ $txn->description ?? '—' }}</td>
                            <td class="text-end {{ $isPurchase ? 'fw-bold' : '' }}">{{ $isPurchase ? number_format($txn->amount, 0, ',', ' ') : '—' }}</td>
                            <td class="text-end {{ !$isPurchase ? 'fw-bold text-success' : '' }}">{{ !$isPurchase ? number_format($txn->amount, 0, ',', ' ') : '—' }}</td>
                            <td class="text-end fw-bold {{ ($txn->running_balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($txn->running_balance ?? 0, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="ti ti-receipt-off fs-1 d-block mb-2"></i>{{ __('Aucun mouvement sur cette periode.') }}</td></tr>
                    @endforelse

                    {{-- Solde de cloture --}}
                    @if($transactions->isNotEmpty())
                    <tr class="table-dark">
                        <td colspan="4" class="fw-bold">{{ __('Solde de cloture') }}</td>
                        <td class="text-end fw-bold">{{ number_format($totals['total_debit'] ?? 0, 0, ',', ' ') }} {{ $currency }}</td>
                        <td class="text-end fw-bold">{{ number_format($totals['total_credit'] ?? 0, 0, ',', ' ') }} {{ $currency }}</td>
                        <td class="text-end fw-bold">{{ number_format($totals['balance'] ?? 0, 0, ',', ' ') }} {{ $currency }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .page-header, .card-header form, .btn, .alert, nav, .sidebar, .header { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table { font-size: 11px; }
    }
</style>
@endpush

</x-dashboard::layouts.master>
