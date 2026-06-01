{{-- POS Cart Sidebar --}}
@php $cartItemCount = collect($cart)->sum(fn ($item) => (int) ($item['quantity'] ?? 0)); @endphp

<div class="card mb-2">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h6 class="mb-0 fw-bold"><i class="ti ti-shopping-cart me-1"></i>{{ __('Panier') }}</h6>
            <span class="badge bg-primary rounded-pill" id="pos-cart-badge">{{ $cartItemCount }}</span>
        </div>
        <button type="button" class="btn btn-sm btn-link text-danger p-0" title="{{ __('Vider') }}" id="pos-cart-clear-btn" style="{{ empty($cart) ? 'display:none' : '' }}">
            <i class="ti ti-trash"></i>
        </button>
    </div>
    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;" id="pos-cart-body">
        @if(empty($cart))
            <div class="text-center text-muted py-4" id="pos-cart-empty">
                <i class="ti ti-shopping-cart-off fs-1 d-block mb-1"></i>
                <small>{{ __('Panier vide') }}</small>
            </div>
        @else
            <div class="list-group list-group-flush" id="pos-cart-list">
                @foreach($cart as $itemKey => $item)
                    @php
                        $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                        $quantity = (int) ($item['quantity'] ?? 0);
                        $lineDiscount = (float) ($item['line_discount'] ?? 0);
                        $lineTotal = (float) ($item['total'] ?? ($unitPrice * $quantity));
                    @endphp
                    <div class="list-group-item pos-cart-item px-3 py-2" data-item-key="{{ $itemKey }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1 me-2">
                                <div class="fw-semibold small pos-item-name">{{ $item['name'] ?? $item['product_name'] ?? 'Produit' }}</div>
                                <div class="text-muted pos-item-detail" style="font-size: .75rem;">{{ number_format($unitPrice, 0, ',', ' ') }} x {{ $quantity }}</div>
                            </div>
                            <div class="fw-bold small pos-item-total">{{ number_format($lineTotal, 0, ',', ' ') }}</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-1 py-0 pos-qty-btn" data-action="decrement" data-key="{{ $itemKey }}" data-qty="{{ max(1, $quantity - 1) }}" style="font-size:.7rem;">
                                    <i class="ti ti-minus"></i>
                                </button>
                                <span class="pos-quick-qty small fw-bold pos-item-qty">{{ $quantity }}</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-1 py-0 pos-qty-btn" data-action="increment" data-key="{{ $itemKey }}" data-qty="{{ $quantity + 1 }}" style="font-size:.7rem;">
                                    <i class="ti ti-plus"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div class="input-group input-group-sm" style="width: 80px;">
                                    <input type="number" class="form-control pos-line-discount" min="0" max="100" step="1" value="{{ (int) $lineDiscount }}" data-key="{{ $itemKey }}" title="{{ __('Remise %') }}" placeholder="%">
                                    <span class="input-group-text" style="font-size:.65rem;padding:.1rem .25rem;">%</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 pos-remove-btn" data-key="{{ $itemKey }}">
                                    <i class="ti ti-x" style="font-size:.8rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
