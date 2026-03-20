@php
    $slug = $instance->slug ?? '';
    $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
    $currency = $settings['currency_symbol'] ?? 'FCFA';
@endphp

<x-dashboard::layouts.master
    :title="__('Mon compte') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Mon compte')">

<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-wallet me-2"></i>{{ __('Mon compte') }}</h4>
        <p class="text-muted mb-0">{{ $customer->name }} ({{ $customer->code }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.portal.account.export', [$slug, 'csv']) }}" class="btn btn-outline-success btn-sm"><i class="ti ti-file-spreadsheet me-1"></i>{{ __('Excel/CSV') }}</a>
        <a href="{{ route('eshop360.portal.account.export', [$slug, 'print']) }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="ti ti-printer me-1"></i>{{ __('Imprimer') }}</a>
        <a href="{{ route('eshop360.portal.orders.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-list me-1"></i>{{ __('Commandes') }}</a>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-wallet fs-4 text-success"></i></div>
            <div><div class="fs-4 fw-bold text-success">{{ $fmt($customer->wallet_balance) }}</div><div class="text-muted">{{ __('Solde portefeuille') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-info bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-credit-card fs-4 text-info"></i></div>
            <div><div class="fs-4 fw-bold text-info">{{ $fmt($customer->credit_limit) }}</div><div class="text-muted">{{ __('Limite credit') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-alert-triangle fs-4 text-danger"></i></div>
            <div><div class="fs-4 fw-bold {{ $totalDueAmount > 0 ? 'text-danger' : 'text-muted' }}">{{ $fmt($totalDueAmount) }}</div><div class="text-muted">{{ __('Dette en cours') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-clock-dollar fs-4 text-warning"></i></div>
            <div><div class="fs-4 fw-bold {{ ($totalOrderDue ?? 0) > 0 ? 'text-warning' : 'text-muted' }}">{{ $fmt($totalOrderDue ?? 0) }}</div><div class="text-muted">{{ __('Reste a payer') }}</div></div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex"><i class="ti ti-cash fs-4 text-primary"></i></div>
            <div><div class="fs-4 fw-bold">{{ $fmt($totalPaid) }}</div><div class="text-muted">{{ __('Total regle') }}</div></div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">

        {{-- Transactions portefeuille --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti ti-arrows-exchange me-2"></i>{{ __('Transactions portefeuille') }}</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-success">{{ __('Depots') }}: {{ $fmt($totalDeposits) }}</span>
                    <span class="badge bg-danger">{{ __('Retraits') }}: {{ $fmt($totalDebits) }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th class="text-end">{{ __('Montant') }}</th><th>{{ __('Details') }}</th></tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                                <tr>
                                    <td class="text-muted">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($t->type === 'credit')
                                            <span class="badge bg-success"><i class="ti ti-arrow-down-left me-1"></i>{{ __('Depot') }}</span>
                                        @else
                                            <span class="badge bg-danger"><i class="ti ti-arrow-up-right me-1"></i>{{ __('Retrait') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $t->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $t->type === 'credit' ? '+' : '-' }}{{ $fmt($t->amount) }} {{ $currency }}
                                    </td>
                                    <td class="text-muted" style="max-width:250px;">{{ Str::limit($t->notes, 60) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('Aucune transaction') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="p-3 border-top">{{ $transactions->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Paiements commandes --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-receipt me-2"></i>{{ __('Paiements commandes') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Commande') }}</th>
                                <th>{{ __('Methode') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                                <th class="text-end">{{ __('Paye') }}</th>
                                <th class="text-end">{{ __('Restant') }}</th>
                                <th class="text-center">{{ __('Statut') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $p)
                                <tr>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
                                    <td class="fw-medium">{{ $p->order_number }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</td>
                                    <td class="text-end">{{ $fmt($p->total) }}</td>
                                    <td class="text-end text-success fw-bold">{{ $fmt($p->paid_amount) }}</td>
                                    <td class="text-end {{ $p->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $fmt($p->due_amount) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $p->payment_status === 'paid' ? 'bg-success' : ($p->payment_status === 'partial' ? 'bg-warning text-dark' : 'bg-danger') }}">{{ ucfirst($p->payment_status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">{{ __('Aucun paiement') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Releve par methode de paiement --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ti ti-credit-card me-2"></i>{{ __('Releve par methode de paiement') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Methode') }}</th>
                                <th class="text-center">{{ __('Operations') }}</th>
                                <th class="text-end">{{ __('Total regle') }}</th>
                                <th class="text-end">{{ __('Reste du') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paymentsByMethod ?? [] as $pm)
                                @php
                                    $methodLabels = [
                                        'cash' => __('Especes'), 'card' => __('Carte bancaire'), 'bank_transfer' => __('Virement'),
                                        'wallet' => __('Compte client'), 'cheque' => __('Cheque'), 'gift_card' => __('Carte cadeau'),
                                        'points' => __('Points fidelite'), 'deposit' => __('Acompte'), 'paypal' => 'PayPal', 'external' => __('Externe'),
                                    ];
                                    $methodIcons = [
                                        'cash' => 'ti-cash', 'card' => 'ti-credit-card', 'bank_transfer' => 'ti-building-bank',
                                        'wallet' => 'ti-wallet', 'cheque' => 'ti-file-check', 'gift_card' => 'ti-gift',
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <i class="ti {{ $methodIcons[$pm->payment_method] ?? 'ti-coin' }} me-2 text-primary"></i>
                                        <span class="fw-medium">{{ $methodLabels[$pm->payment_method] ?? ucfirst(str_replace('_', ' ', $pm->payment_method)) }}</span>
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $pm->count }}</span></td>
                                    <td class="text-end fw-bold text-success">{{ $fmt($pm->total_paid) }} {{ $currency }}</td>
                                    <td class="text-end {{ $pm->total_due > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $fmt($pm->total_due) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('Aucune operation') }}</td></tr>
                            @endforelse
                        </tbody>
                        @if(($paymentsByMethod ?? collect())->count() > 1)
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>{{ __('Total') }}</td>
                                <td class="text-center">{{ ($paymentsByMethod ?? collect())->sum('count') }}</td>
                                <td class="text-end text-success">{{ $fmt(($paymentsByMethod ?? collect())->sum('total_paid')) }} {{ $currency }}</td>
                                <td class="text-end text-danger">{{ $fmt(($paymentsByMethod ?? collect())->sum('total_due')) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="col-xl-4">

        {{-- Credits / Dettes --}}
        <div class="card border-0 shadow-sm mb-3 {{ $totalDueAmount > 0 ? 'border-start border-4 border-danger' : '' }}">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ti ti-file-invoice me-2"></i>{{ __('Credits & Dettes') }}</h5></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($dues as $d)
                        @php $remaining = (float)$d->amount_due - (float)$d->paid_amount; @endphp
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge {{ $d->status === 'paid' ? 'bg-success' : ($d->status === 'partial' ? 'bg-warning text-dark' : 'bg-danger') }} mb-1">{{ ucfirst($d->status) }}</span>
                                    <div class="text-muted" style="font-size:.85rem;">{{ $d->created_at->format('d/m/Y') }}</div>
                                    @if($d->due_date)
                                        <div class="text-muted" style="font-size:.8rem;">{{ __('Echeance') }}: {{ $d->due_date->format('d/m/Y') }}</div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">{{ $fmt($d->amount_due) }} {{ $currency }}</div>
                                    @if($d->paid_amount > 0)
                                        <div class="text-success" style="font-size:.85rem;">{{ __('Paye') }}: {{ $fmt($d->paid_amount) }}</div>
                                    @endif
                                    @if($remaining > 0)
                                        <div class="text-danger fw-bold" style="font-size:.85rem;">{{ __('Reste') }}: {{ $fmt($remaining) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="ti ti-check fs-3 text-success d-block mb-1"></i>{{ __('Aucune dette') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Resume compte --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary bg-opacity-10"><h5 class="card-title mb-0"><i class="ti ti-report-money me-2 text-primary"></i>{{ __('Resume') }}</h5></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">{{ __('Solde actuel') }}</td><td class="text-end fw-bold text-success">{{ $fmt($customer->wallet_balance) }} {{ $currency }}</td></tr>
                    <tr><td class="text-muted">{{ __('Credit disponible') }}</td><td class="text-end fw-bold text-info">{{ $fmt($customer->credit_limit) }} {{ $currency }}</td></tr>
                    <tr class="border-top"><td class="text-muted">{{ __('Disponible total') }}</td><td class="text-end fw-bold fs-5 text-primary">{{ $fmt((float)$customer->wallet_balance + (float)$customer->credit_limit) }} {{ $currency }}</td></tr>
                    <tr><td class="text-muted">{{ __('Total depose') }}</td><td class="text-end">{{ $fmt($totalDeposits) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Total debite') }}</td><td class="text-end">{{ $fmt($totalDebits) }}</td></tr>
                    <tr><td class="text-muted">{{ __('Total commandes regle') }}</td><td class="text-end">{{ $fmt($totalPaid) }}</td></tr>
                    @if(($totalOrderDue ?? 0) > 0)
                        <tr><td class="text-muted">{{ __('Reste a payer (commandes)') }}</td><td class="text-end fw-bold text-warning">{{ $fmt($totalOrderDue) }} {{ $currency }}</td></tr>
                    @endif
                    @if($totalDueAmount > 0)
                        <tr><td class="text-muted">{{ __('Dette credit') }}</td><td class="text-end fw-bold text-danger">{{ $fmt($totalDueAmount) }} {{ $currency }}</td></tr>
                    @endif
                    <tr class="border-top"><td class="fw-bold">{{ __('Total du global') }}</td><td class="text-end fw-bold fs-5 {{ (($totalOrderDue ?? 0) + $totalDueAmount) > 0 ? 'text-danger' : 'text-success' }}">{{ $fmt(($totalOrderDue ?? 0) + $totalDueAmount) }} {{ $currency }}</td></tr>
                </table>
            </div>
        </div>

    </div>
</div>

</x-dashboard::layouts.master>
