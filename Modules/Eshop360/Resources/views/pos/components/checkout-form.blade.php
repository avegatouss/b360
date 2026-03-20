{{-- POS Checkout Form --}}
@php
    $registerRequired = !empty($settings['register_required'] ?? false);
    $registerBlocked = $registerRequired && !$registerOpen;
    $customerAccountEnabled = !empty($settings['customer_account_enabled'] ?? false);
@endphp
<div class="card mb-2">
    <div class="card-body py-2">
        @if($registerBlocked)
            <div class="alert alert-danger py-2 mb-2 text-center">
                <i class="ti ti-lock me-1"></i>{{ __('Ouvrez une caisse pour valider des ventes') }}
            </div>
        @endif

        {{-- Wallet balance indicator (hidden by default, shown via JS when wallet selected + customer chosen) --}}
        @if($customerAccountEnabled)
        <div id="pos-wallet-info" class="alert alert-info py-1 px-3 mb-2 d-none" style="font-size: .85rem;">
            <i class="ti ti-wallet me-1"></i>
            <span>{{ __('Solde') }}: </span><strong id="pos-wallet-balance">0</strong>
            <span class="ms-2 text-muted">|</span>
            <span class="ms-2">{{ __('Credit') }}: </span><strong id="pos-wallet-credit">0</strong>
        </div>
        @endif

        <form method="POST" action="{{ route('eshop360.sales.store', $instance->slug ?? '') }}" id="pos-checkout-form">
            @csrf
            <input type="hidden" name="source" value="pos">
            <input type="hidden" name="coupon_code" id="pos-checkout-coupon" value="{{ $coupon['code'] ?? '' }}">
            <input type="hidden" name="discount_amount" id="pos-checkout-discount" value="{{ $totals['discount'] }}">
            <input type="hidden" name="customer_id" id="pos-checkout-customer" value="{{ old('customer_id', $settings['default_customer_id'] ?? '') }}">
            @if(($cartContext['channel_id'] ?? null) !== null)
                <input type="hidden" name="channel_id" value="{{ $cartContext['channel_id'] }}">
            @endif

            <div id="pos-checkout-items">
                @foreach(array_values($cart) as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['product_id'] }}">
                    @if(!empty($item['variation_id']))
                        <input type="hidden" name="items[{{ $index }}][variation_id]" value="{{ $item['variation_id'] }}">
                    @endif
                    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ (int) ($item['quantity'] ?? 0) }}">
                    <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ (float) ($item['unit_price'] ?? $item['price'] ?? 0) }}">
                    <input type="hidden" name="items[{{ $index }}][original_price]" value="{{ (float) ($item['original_price'] ?? $item['unit_price'] ?? $item['price'] ?? 0) }}">
                    <input type="hidden" name="items[{{ $index }}][line_discount]" value="{{ (float) ($item['line_discount'] ?? 0) }}">
                @endforeach
            </div>

            <div class="row g-2 mb-2">
                <div class="col-7">
                    <select name="payment_method" class="form-select form-select-sm pos-select2-checkout" id="pos-payment-method" data-placeholder="{{ __('Moyen de paiement') }}" {{ $registerBlocked ? 'disabled' : '' }}>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ __('Recu') }}</span>
                        <input type="number" name="paid_amount" id="pos-paid-amount" class="form-control" min="0" step="1" value="{{ (int) $totals['total'] }}" {{ (empty($cart) || $registerBlocked) ? 'disabled' : '' }}>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ __('Rendu') }}</span>
                        <input type="text" id="pos-change-display" class="form-control fw-bold" value="0" readonly>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-1">
                <button type="submit" class="btn btn-primary btn-lg" {{ (empty($cart) || $registerBlocked) ? 'disabled' : '' }} id="pos-validate-btn">
                    <i class="ti ti-check me-1"></i>{{ __('Valider la vente') }}
                    <span class="ms-2 fw-bold" id="pos-validate-total">{{ number_format($totals['total'], 0, ',', ' ') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
