<x-dashboard::layouts.master
    :title="__('Facture') . ' ' . ($invoice->invoice_number ?? '') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Facture')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
    $isPaid = $invoice->status === 'paid';
    $isOverdue = !$isPaid && $invoice->due_date && $invoice->due_date < now();
    $dueAmount = max(0, (float) $invoice->due_amount);
    $statusClass = match($invoice->status) {
        'paid' => 'success', 'overdue' => 'danger', 'draft' => 'secondary', 'partial' => 'warning', default => $isOverdue ? 'danger' : 'info',
    };
    $statusLabel = $isOverdue && !$isPaid ? __('En retard') : match($invoice->status) {
        'paid' => __('Payee'), 'draft' => __('Brouillon'), 'partial' => __('Partielle'), 'overdue' => __('En retard'), default => __('Impayee'),
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">{{ $invoice->invoice_number }}</h4>
        <p class="text-muted mb-0">{{ optional($invoice->created_at)->format('d/m/Y H:i') }}
            <span class="badge bg-{{ $statusClass }} ms-2">{{ $statusLabel }}</span>
            @if($isOverdue)<span class="badge bg-danger ms-1"><i class="ti ti-clock-exclamation me-1"></i>{{ __('Echeance depassee') }}</span>@endif
        </p>
    </div>
    <div class="d-flex gap-2">
        @include('eshop360::fne._sign-button', ['order' => $invoice->order ?? $invoice])
        <a href="{{ route('eshop360.invoices.pdf', [$slug, $invoice]) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-file-download me-1"></i>{{ __('PDF') }}</a>
        <form method="POST" action="{{ route('eshop360.invoices.send-email', [$slug, $invoice]) }}" class="d-inline">@csrf
            <button type="submit" class="btn btn-outline-info"><i class="ti ti-mail me-1"></i>{{ __('Envoyer') }}</button>
        </form>
        <a href="{{ route('eshop360.invoices.index', $slug) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

<div class="row g-3">
    <div class="col-md-4">
        {{-- Info --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-info-circle me-2"></i>{{ __('Informations') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Numero') }}</th><td class="fw-medium">{{ $invoice->invoice_number }}</td></tr>
                    <tr><th class="text-muted">{{ __('Client') }}</th><td>{{ $invoice->customer->name ?? __('Client anonyme') }}</td></tr>
                    @if($invoice->order)
                    <tr><th class="text-muted">{{ __('Commande') }}</th><td><a href="{{ route('eshop360.orders.show', [$slug, $invoice->order]) }}" class="text-decoration-none">{{ $invoice->order->order_number }}</a></td></tr>
                    @endif
                    <tr><th class="text-muted">{{ __('Statut') }}</th><td><span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></td></tr>
                    <tr><th class="text-muted">{{ __('Echeance') }}</th><td class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">{{ $invoice->due_date?->format('d/m/Y') ?? '—' }} @if($isOverdue)<i class="ti ti-alert-circle"></i>@endif</td></tr>
                    <tr><th class="text-muted">{{ __('Cree par') }}</th><td>{{ $invoice->creator->full_name ?? $invoice->creator->name ?? '—' }}</td></tr>
                    @if($invoice->notes)<tr><th class="text-muted">{{ __('Notes') }}</th><td>{{ $invoice->notes }}</td></tr>@endif
                </table>
            </div>
        </div>

        {{-- Totals --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-calculator me-2"></i>{{ __('Totaux') }}</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">{{ __('Sous-total') }}</th><td class="text-end fw-medium">{{ number_format($invoice->subtotal, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    <tr><th class="text-muted">{{ __('Taxes') }}</th><td class="text-end">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    @if($invoice->discount_amount > 0)
                    <tr><th class="text-muted">{{ __('Remise') }}</th><td class="text-end text-danger">-{{ number_format($invoice->discount_amount, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    @endif
                    <tr class="fw-bold border-top"><th>{{ __('Total') }}</th><td class="text-end fs-5">{{ number_format($invoice->total, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    <tr><th class="text-muted">{{ __('Paye') }}</th><td class="text-end text-success fw-bold">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    @if($dueAmount > 0)
                    <tr class="table-danger"><th class="text-danger">{{ __('Reste du') }}</th><td class="text-end text-danger fw-bold fs-5">{{ number_format($dueAmount, 0, ',', ' ') }} {{ $currency }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Payment Form --}}
        @if(!$isPaid && $dueAmount > 0)
        <div class="card border-0 shadow-sm border-start border-success border-3">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-cash me-2 text-success"></i>{{ __('Enregistrer un paiement') }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('eshop360.invoices.record-payment', [$slug, $invoice]) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('Montant') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control" min="1" max="{{ $dueAmount }}" step="1" value="{{ (int) $dueAmount }}" required>
                            <span class="input-group-text">{{ $currency }}</span>
                        </div>
                        <div class="text-muted mt-1">{{ __('Reste a payer') }}: <strong class="text-danger">{{ number_format($dueAmount, 0, ',', ' ') }} {{ $currency }}</strong></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Methode de paiement') }} <span class="text-danger">*</span></label>
                        <select name="method" class="form-select inv-show-select2" required>
                            <option value="cash">{{ __('Especes') }}</option>
                            <option value="card">{{ __('Carte bancaire') }}</option>
                            <option value="bank_transfer">{{ __('Virement bancaire') }}</option>
                            <option value="cheque">{{ __('Cheque') }}</option>
                            <option value="deposit">{{ __('Depot / Acompte') }}</option>
                            <option value="points">{{ __('Points fidelite') }}</option>
                            <option value="external">{{ __('Paiement externe') }}</option>
                            <option value="manual">{{ __('Manuel') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="{{ __('Reference, details...') }}">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="ti ti-cash me-1"></i>{{ __('Enregistrer le paiement') }}</button>
                        <button type="button" class="btn btn-outline-success" onclick="document.querySelector('[name=amount]').value={{ (int) $dueAmount }}">
                            <i class="ti ti-check me-1"></i>{{ __('Solder la facture') }} ({{ number_format($dueAmount, 0, ',', ' ') }} {{ $currency }})
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Payment History --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-history me-2"></i>{{ __('Historique des paiements') }} <span class="badge bg-primary ms-1">{{ $invoice->payments->count() }}</span></h6></div>
            <div class="card-body">
                @forelse($invoice->payments as $payment)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-capitalize">{{ str_replace('_', ' ', $payment->method) }}</span>
                                <span class="text-muted ms-2">{{ $payment->reference ?? '' }}</span>
                            </div>
                            <span class="fw-bold {{ $payment->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $payment->amount >= 0 ? '+' : '' }}{{ number_format($payment->amount, 0, ',', ' ') }} {{ $currency }}
                            </span>
                        </div>
                        <div class="text-muted mt-1">
                            {{ optional($payment->created_at)->format('d/m/Y H:i') }}
                            @if($payment->notes) — {{ $payment->notes }} @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('Aucun paiement enregistre.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="ti ti-list me-2"></i>{{ __('Lignes de facture') }} <span class="badge bg-primary ms-1">{{ $invoice->items->count() }}</span></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Qte') }}</th>
                                <th class="text-end">{{ __('Prix unit.') }}</th>
                                <th class="text-end">{{ __('Remise') }}</th>
                                <th class="text-end">{{ __('Taxe') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $item->description }}</div>
                                        @if($item->product)
                                            <span class="text-muted"><code>{{ $item->product->sku ?? '' }}</code></span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($item->discount ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($item->tax ?? 0, 0, ',', ' ') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->total, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">{{ __('Aucune ligne de facture.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Status update --}}
        @if(!$isPaid)
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-settings me-2"></i>{{ __('Actions') }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('eshop360.invoices.update', [$slug, $invoice]) }}" class="d-flex gap-2 flex-wrap align-items-center">
                    @csrf @method('PUT')
                    <select name="status" class="form-select inv-show-select2" style="max-width:200px;">
                        <option value="draft" @selected($invoice->status === 'draft')>{{ __('Brouillon') }}</option>
                        <option value="unpaid" @selected($invoice->status === 'unpaid')>{{ __('Impayee') }}</option>
                        <option value="sent" @selected($invoice->status === 'sent')>{{ __('Envoyee') }}</option>
                        <option value="overdue" @selected($invoice->status === 'overdue')>{{ __('En retard') }}</option>
                        <option value="cancelled" @selected($invoice->status === 'cancelled')>{{ __('Annulee') }}</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary"><i class="ti ti-check me-1"></i>{{ __('Changer le statut') }}</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.inv-show-select2').select2({ theme: 'bootstrap-5', width: '100%' });
});
</script>
@endpush

</x-dashboard::layouts.master>
