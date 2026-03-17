<x-dashboard::layouts.master
    :title="__('Vente') . ($sale->order_number ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Vente')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $sale->order_number }}</h4>
            <h6>{{ $sale->created_at->format('d/m/Y H:i') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        @if(($sale->source ?? '') === 'pos')
            <a href="{{ route('eshop360.orders.receipt', [$instance->slug ?? '', $sale]) }}" class="btn btn-white border">
                <i class="ti ti-printer me-1"></i>{{ __('Recu') }}
            </a>
        @endif
        <a href="{{ route('eshop360.sales.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Numero</th><td>{{ $sale->order_number }}</td></tr>
                    <tr><th>Client</th><td>{{ $sale->customer->name ?? 'Client anonyme' }}</td></tr>
                    <tr><th>{{ __('Source') }}</th><td><span class="badge bg-info">{{ \Modules\Eshop360\Support\UiLabel::enum($sale->source) }}</span></td></tr>
                    <tr>
                        <th>{{ __('Statut') }}</th>
                        <td><span class="badge bg-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'cancelled' ? 'danger' : 'warning') }}">{{ \Modules\Eshop360\Support\UiLabel::enum($sale->status) }}</span></td>
                    </tr>
                    <tr>
                        <th>{{ __('Paiement') }}</th>
                        <td><span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'overdue' ? 'danger' : 'warning') }}">{{ \Modules\Eshop360\Support\UiLabel::enum($sale->payment_status) }}</span></td>
                    </tr>
                    <tr><th>{{ __('Methode') }}</th><td>{{ \Modules\Eshop360\Support\UiLabel::enum($sale->payment_method) }}</td></tr>
                    @if($sale->cashRegister)
                    <tr><th>Caisse</th><td>#{{ $sale->cashRegister->id }}{{ $sale->store ? ' · ' . $sale->store->name : '' }}</td></tr>
                    @endif
                    @if($sale->coupon_code)
                    <tr><th>Coupon</th><td><code>{{ $sale->coupon_code }}</code></td></tr>
                    @endif
                    @if($sale->notes)
                    <tr><th>Notes</th><td>{{ $sale->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Totaux') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Sous-total</th><td class="text-end">{{ number_format($sale->subtotal, 2) }}</td></tr>
                    <tr><th>Taxes</th><td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td></tr>
                    @if($sale->discount_amount > 0)
                    <tr><th>Remise</th><td class="text-end text-danger">-{{ number_format($sale->discount_amount, 2) }}</td></tr>
                    @endif
                    @if($sale->shipping_amount > 0)
                    <tr><th>Livraison</th><td class="text-end">{{ number_format($sale->shipping_amount, 2) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><th>Total</th><td class="text-end">{{ number_format($sale->total, 2) }}</td></tr>
                    <tr><th>Paye</th><td class="text-end text-success">{{ number_format($sale->paid_amount, 2) }}</td></tr>
                    @if($sale->due_amount > 0)
                    <tr><th>Reste du</th><td class="text-end text-danger">{{ number_format($sale->due_amount, 2) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Paiements') }}</h5></div>
            <div class="card-body">
                @forelse($sale->payments as $payment)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ \Modules\Eshop360\Support\UiLabel::enum($payment->method) }}</strong>
                            <span class="{{ $payment->amount < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($payment->amount, 2) }}
                            </span>
                        </div>
                        <div class="small text-muted">
                            {{ $payment->reference ?? __('Sans reference') }} · {{ \Modules\Eshop360\Support\UiLabel::enum($payment->status ?? 'completed') }}
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
            <div class="card-header"><h5>{{ __('Articles') }}</h5></div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr><th>{{ __('Produit') }}</th><th>{{ __('SKU') }}</th><th>{{ __('Prix unit.') }}</th><th>{{ __('Qte') }}</th><th>{{ __('Remise') }}</th><th>{{ __('Taxe') }}</th><th>{{ __('Total') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($sale->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td><code>{{ $item->sku }}</code></td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->discount ?? 0, 2) }}</td>
                            <td>{{ number_format($item->tax ?? 0, 2) }}</td>
                            <td class="fw-bold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">{{ __('Aucun article') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($sale->status !== 'refunded' && $sale->items->where('quantity', '>', 0)->isNotEmpty())
            <div class="card">
                <div class="card-header"><h5>{{ __('Traiter un retour') }}</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('eshop360.sales.returns.store', $instance->slug ?? '') }}">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $sale->id }}">

                        <div class="table-responsive mb-3">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Produit') }}</th>
                                        <th>{{ __('Qté vendue') }}</th>
                                        <th>{{ __('Qté retour') }}</th>
                                        <th>{{ __('Motif') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items->where('quantity', '>', 0)->values() as $index => $item)
                                        <tr>
                                            <td>
                                                {{ $item->product_name }}
                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td style="max-width: 140px;">
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm" min="1" max="{{ $item->quantity }}" value="1">
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $index }}][reason]" class="form-control form-control-sm" placeholder="Motif">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Montant rembourse') }}</label>
                                <input type="number" name="refund_amount" class="form-control" min="0" step="0.01" value="{{ number_format($sale->total, 2, '.', '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <input type="text" name="notes" class="form-control" placeholder="{{ __('Notes retour') }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-arrow-back-up me-1"></i>Valider le retour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
