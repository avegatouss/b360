<x-dashboard::layouts.master
    :title="__('Checkout') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Checkout')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Checkout POS') }}</h4>
            <h6>{{ __('Validation du panier, paiement et creation de commande') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.pos.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour au POS
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('eshop360.checkout.process', $instance->slug ?? '') }}">
            @csrf
            @if(($cartContext['channel_id'] ?? null) !== null)
                <input type="hidden" name="channel_id" value="{{ $cartContext['channel_id'] }}">
            @endif
            @if($cartContext['is_codifarm'] ?? false)
                <input type="hidden" name="is_codifarm" value="1">
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Client et paiement') }}</h5>
                </div>
                <div class="card-body">
                    @if(($cartContext['is_codifarm'] ?? false) || $contextChannel)
                        <div class="alert alert-secondary py-2">
                            Tarification active:
                            <strong>{{ ($cartContext['is_codifarm'] ?? false) ? 'Mode CODIFARM' : ($contextChannel->name ?? 'Canal') }}</strong>
                        </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Client existant') }}</label>
                            <select name="customer_id" class="form-select">
                                <option value="">{{ __('Client comptoir') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('Methode de paiement') }}</label>
                            <select name="payment_method" id="paymentMethod" class="form-select" required>
                                @php($methods = ['cash' => 'Especes', 'card' => 'Carte', 'cheque' => 'Cheque', 'paypal' => 'Paypal', 'bank_transfer' => 'Virement', 'wallet' => 'Portefeuille client', 'points' => 'Points', 'deposit' => 'Depot', 'gift_card' => 'Carte cadeau', 'external' => 'Externe'])
                                @foreach($methods as $value => $label)
                                    <option value="__BLADE_BLOCK_8__" __BLADE_BLOCK_9__>__BLADE_BLOCK_10__</option>
                                @endforeach
                            </select>
                            @error('payment_method') <small class="text-danger">__BLADE_BLOCK_11__</small> @enderror
                        </div>

                        __BLADE_BLOCK_12__
                        <div class="col-md-6" id="giftCardSection" style="display:none;">
                            <label class="form-label">Code carte cadeau</label>
                            <input type="text" name="gift_card_code" id="giftCardCode" class="form-control text-uppercase"
                                   placeholder="Ex: ABCD1234EFGH" value="__BLADE_BLOCK_13__"
                                   style="letter-spacing:.1em;">
                            @error('gift_card_code') <small class="text-danger">__BLADE_BLOCK_14__</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nom du client</label>
                            <input type="text" name="customer_name" class="form-control" value="__BLADE_BLOCK_15__" placeholder="Utilise pour creer un client rapide">
                            @error('customer_name') <small class="text-danger">__BLADE_BLOCK_16__</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telephone</label>
                            <input type="text" name="customer_phone" class="form-control" value="__BLADE_BLOCK_17__">
                            @error('customer_phone') <small class="text-danger">__BLADE_BLOCK_18__</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control" value="__BLADE_BLOCK_19__">
                            @error('customer_email') <small class="text-danger">__BLADE_BLOCK_20__</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Montant recu</label>
                            <input type="number" name="paid_amount" class="form-control" min="0" step="0.01" value="__BLADE_BLOCK_21__" required>
                            @error('paid_amount') <small class="text-danger">__BLADE_BLOCK_22__</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Livraison</label>
                            <input type="number" name="shipping_amount" class="form-control" min="0" step="0.01" value="__BLADE_BLOCK_23__">
                            @error('shipping_amount') <small class="text-danger">__BLADE_BLOCK_24__</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="customer_address" class="form-control" value="__BLADE_BLOCK_25__">
                            @error('customer_address') <small class="text-danger">__BLADE_BLOCK_26__</small> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">__BLADE_BLOCK_27__</textarea>
                            @error('notes') <small class="text-danger">__BLADE_BLOCK_28__</small> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-credit-card me-1"></i>Finaliser la commande
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Panier</h5>
                <span class="badge bg-light text-dark">__BLADE_BLOCK_29__ ligne(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th class="text-end">Qté</th>
                                <th class="text-end">PU</th>
                                <th class="text-end">Total HT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart as $item)
                                @php
                                    $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                                    $quantity = (int) ($item['quantity'] ?? 0);
                                    $lineTotal = (float) ($item['total'] ?? ($unitPrice * $quantity));
                                    $lineDiscount = 0;
                                    if (isset($item['original_price']) && (float) $item['original_price'] > $unitPrice) {
                                        $lineDiscount = ((float) $item['original_price'] - $unitPrice) * $quantity;
                                    } elseif (isset($item['discount'])) {
                                        $lineDiscount = (float) $item['discount'];
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item['name'] ?? $item['product_name'] ?? 'Produit' }}</div>
                                        <div class="text-muted small">{{ $item['sku'] ?? '---' }}</div>
                                        @if($lineDiscount > 0)
                                            <div class="text-danger small">Remise ligne: -{{ number_format($lineDiscount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $quantity }}</td>
                                    <td class="text-end">{{ number_format($unitPrice, 2) }}</td>
                                    <td class="text-end">{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Resume') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Sous-total') }}</span>
                    <strong>{{ number_format($totals['subtotal'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Taxes') }}</span>
                    <strong>{{ number_format($totals['tax'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Remise') }}</span>
                    <strong class="text-danger">-{{ number_format($totals['discount'], 2) }}</strong>
                </div>
                @if($coupon)
                    <div class="alert alert-info py-2">
                        Coupon actif: <strong>{{ $coupon['code'] }}</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between border-top pt-3">
                    <span class="fw-bold">{{ __('Total hors livraison') }}</span>
                    <strong>{{ number_format($totals['total'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const sel = document.getElementById('paymentMethod');
    const gcSection = document.getElementById('giftCardSection');
    if (!sel || !gcSection) return;

    function toggle() {
        gcSection.style.display = sel.value === 'gift_card' ? '' : 'none';
    }
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>

</x-dashboard::layouts.master>
