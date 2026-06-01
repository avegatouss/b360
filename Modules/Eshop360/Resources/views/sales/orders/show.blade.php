<x-dashboard::layouts.master
    :title="__('Commande') . ($order->order_number ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Detail Commande')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $order->order_number }}</h4>
            <h6>{{ $order->created_at->format('d/m/Y H:i') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        @include('eshop360::fne._sign-button', ['order' => $order])
        <button class="btn btn-white border" data-bs-toggle="modal" data-bs-target="#receipt-modal">
            <i class="ti ti-printer me-1"></i>{{ __('Recu') }}
        </button>
        <a href="{{ route('eshop360.orders.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>{{ __('Informations') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Numero</th><td>{{ $order->order_number }}</td></tr>
                    <tr><th>Client</th><td>{{ $order->customer->name ?? 'Client anonyme' }}</td></tr>
                    <tr><th>{{ __('Source') }}</th><td><span class="badge bg-info">{{ \Modules\Eshop360\Support\UiLabel::enum($order->source) }}</span></td></tr>
                    <tr>
                        <th>{{ __('Statut') }}</th>
                        <td><span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ \Modules\Eshop360\Support\UiLabel::enum($order->status) }}</span></td>
                    </tr>
                    <tr>
                        <th>{{ __('Paiement') }}</th>
                        <td><span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'overdue' ? 'danger' : 'warning') }}">{{ \Modules\Eshop360\Support\UiLabel::enum($order->payment_status) }}</span></td>
                    </tr>
                    <tr><th>{{ __('Methode') }}</th><td>{{ \Modules\Eshop360\Support\UiLabel::enum($order->payment_method) }}</td></tr>
                    @if($order->cashRegister)
                    <tr><th>Caisse</th><td>#{{ $order->cashRegister->id }}{{ $order->store ? ' · ' . $order->store->name : '' }}</td></tr>
                    @endif
                    @if($order->holding)
                    <tr><th>Holding</th><td>{{ $order->holding->reference }}</td></tr>
                    @endif
                    @if($order->coupon_code)
                    <tr><th>Coupon</th><td><code>{{ $order->coupon_code }}</code></td></tr>
                    @endif
                    @if($order->notes)
                    <tr><th>Notes</th><td>{{ $order->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5>{{ __('Totaux') }}</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th>Sous-total</th><td class="text-end">{{ number_format($order->subtotal, 2) }}</td></tr>
                    <tr><th>Taxes</th><td class="text-end">{{ number_format($order->tax_amount, 2) }}</td></tr>
                    @if($order->discount_amount > 0)
                    <tr><th>Remise</th><td class="text-end text-danger">-{{ number_format($order->discount_amount, 2) }}</td></tr>
                    @endif
                    @if($order->shipping_amount > 0)
                    <tr><th>Livraison</th><td class="text-end">{{ number_format($order->shipping_amount, 2) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><th>Total</th><td class="text-end">{{ number_format($order->total, 2) }}</td></tr>
                    <tr><th>Paye</th><td class="text-end text-success">{{ number_format($order->paid_amount, 2) }}</td></tr>
                    @if($order->due_amount > 0)
                    <tr><th>Reste du</th><td class="text-end text-danger">{{ number_format($order->due_amount, 2) }}</td></tr>
                    @endif
                </table>
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
                        @forelse($order->items as $item)
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
    </div>
</div>

{{-- Receipt Modal --}}
<div class="modal fade" id="receipt-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="ti ti-receipt me-1"></i>{{ __('Recu de commande') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="max-height:70vh;overflow-y:auto;">
                @include('eshop360::pdf.order-receipt-inline', ['order' => $order])
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">{{ __('Fermer') }}</button>
                <a href="{{ route('eshop360.orders.receipt', [$instance->slug ?? '', $order]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="ti ti-printer me-1"></i>{{ __('Imprimer PDF') }}
                </a>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
