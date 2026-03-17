<x-dashboard::layouts.master
    :title="__('Facture') . ($invoice->invoice_number ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Facture')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $invoice->invoice_number }}</h4>
            <h6>{{ optional($invoice->created_at)->format('d/m/Y H:i') }}</h6>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.invoices.pdf', [$instance->slug ?? '', $invoice]) }}" class="btn btn-white border">
            <i class="ti ti-file-download me-1"></i>PDF
        </a>
        <form method="POST" action="{{ route('eshop360.invoices.send-email', [$instance->slug ?? '', $invoice]) }}">
            @csrf
            <button type="submit" class="btn btn-white border">
                <i class="ti ti-mail me-1"></i>Envoyer
            </button>
        </form>
        <a href="{{ route('eshop360.invoices.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>Numero</th><td>{{ $invoice->invoice_number }}</td></tr>
                    <tr><th>Client</th><td>{{ $invoice->customer->name ?? 'Client anonyme' }}</td></tr>
                    <tr><th>Commande</th><td>{{ $invoice->order->order_number ?? '---' }}</td></tr>
                    <tr>
                        <th>{{ __('Statut') }}</th>
                        <td>
                            @php
                                $statusClass = match($invoice->status) {
                                    'paid' => 'success',
                                    'overdue', 'cancelled' => 'danger',
                                    'draft' => 'secondary',
                                    default => 'warning',
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
                        </td>
                    </tr>
                    <tr><th>Echeance</th><td>{{ $invoice->due_date?->format('d/m/Y') ?? '---' }}</td></tr>
                    <tr><th>Cree par</th><td>{{ $invoice->creator->full_name ?? $invoice->creator->name ?? '---' }}</td></tr>
                    @if($invoice->notes)
                        <tr><th>Notes</th><td>{{ $invoice->notes }}</td></tr>
                    @endif
                    @if($invoice->terms)
                        <tr><th>Conditions</th><td>{{ $invoice->terms }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Totaux') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th>Sous-total</th><td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                    <tr><th>Taxes</th><td class="text-end">{{ number_format($invoice->tax_amount, 2) }}</td></tr>
                    @if($invoice->discount_amount > 0)
                        <tr><th>Remise</th><td class="text-end text-danger">-{{ number_format($invoice->discount_amount, 2) }}</td></tr>
                    @endif
                    @if(($invoice->shipping_amount ?? 0) > 0)
                        <tr><th>Livraison</th><td class="text-end">{{ number_format($invoice->shipping_amount, 2) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><th>Total</th><td class="text-end">{{ number_format($invoice->total, 2) }}</td></tr>
                    <tr><th>Paye</th><td class="text-end text-success">{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                    <tr><th>Reste du</th><td class="text-end text-danger">{{ number_format($invoice->due_amount, 2) }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Paiements') }}</h5></div>
            <div class="card-body">
                @forelse($invoice->payments as $payment)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</strong>
                            <span>{{ number_format($payment->amount, 2) }}</span>
                        </div>
                        <div class="small text-muted">
                            {{ $payment->reference ?? 'Sans reference' }} · {{ optional($payment->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('Aucun paiement enregistre.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>{{ __('Lignes de facture') }}</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Qté') }}</th>
                                <th class="text-end">{{ __('PU') }}</th>
                                <th class="text-end">{{ __('Remise') }}</th>
                                <th class="text-end">{{ __('Taxe') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->description }}</div>
                                        @if($item->product)
                                            <div class="small text-muted">{{ $item->product->sku ?? '---' }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->discount ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->tax ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ __('Aucune ligne de facture.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
