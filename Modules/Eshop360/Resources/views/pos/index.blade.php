<x-dashboard::layouts.master
    :title="'POS — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="POS">

@php
    $cartItemCount = collect($cart)->sum(fn ($item) => (int) ($item['quantity'] ?? 0));
    $activeChannelId = (string) request('channel_id', $cartContext['channel_id'] ?? '');
    $activeIsCodifarm = request()->boolean('is_codifarm') || (bool) ($cartContext['is_codifarm'] ?? false);
    $activeContextLabel = $activeIsCodifarm
        ? 'Mode CODIFARM'
        : ($channels->firstWhere('id', $activeChannelId !== '' ? (int) $activeChannelId : ($cartContext['channel_id'] ?? null))?->name ?? null);
@endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Terminal POS</h4>
            <h6>Panier, encaissement et finalisation de vente</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.checkout.index', $instance->slug ?? '') }}" class="btn btn-white border {{ empty($cart) ? 'disabled' : '' }}">
            <i class="ti ti-receipt me-1"></i>Checkout detaille
        </a>
        <a href="{{ route('eshop360.pos.orders', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-list-details me-1"></i>Commandes POS
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                {{-- Zone scan barcode hardware --}}
                <div class="input-group mb-3">
                    <span class="input-group-text bg-primary text-white"><i class="ti ti-barcode"></i></span>
                    <input type="text"
                           id="pos-barcode-input"
                           class="form-control form-control-lg"
                           placeholder="Scanner un code-barres ou saisir SKU + Entrée..."
                           autocomplete="off"
                           autofocus>
                    <button class="btn btn-outline-secondary" type="button" id="pos-barcode-btn">
                        <i class="ti ti-search"></i>
                    </button>
                </div>

                <form method="GET" action="{{ route('eshop360.pos.index', $instance->slug ?? '') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, SKU ou code-barres">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Categorie</label>
                        <select name="category_id" class="form-select">
                            <option value="">Toutes</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Marque</label>
                        <select name="brand_id" class="form-select">
                            <option value="">Toutes</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Canal tarifaire</label>
                        <select name="channel_id" class="form-select">
                            <option value="">Prix standard</option>
                            @foreach($channels as $channel)
                                <option value="{{ $channel->id }}" {{ $activeChannelId === (string) $channel->id ? 'selected' : '' }}>
                                    {{ $channel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-4 pt-2">
                            <input type="checkbox" name="is_codifarm" value="1" class="form-check-input" id="pos-is-codifarm" {{ $activeIsCodifarm ? 'checked' : '' }}>
                            <label class="form-check-label" for="pos-is-codifarm">CODIFARM</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3">
            @forelse($products as $product)
                @php
                    $availableQty = (int) $product->stocks->sum('quantity');
                @endphp
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div>
                                    <div class="small text-muted">{{ $product->category->name ?? 'Sans categorie' }}</div>
                                    <h5 class="mb-1">{{ $product->name }}</h5>
                                    <div class="small text-muted">{{ $product->sku ?? '---' }}</div>
                                </div>
                                @php
                    $alertQty = $product->alert_quantity ?? 5;
                    $stockBadge = $availableQty <= 0
                        ? 'bg-danger-subtle text-danger'
                        : ($availableQty <= $alertQty ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success');
                @endphp
                                <span class="badge {{ $stockBadge }}">
                                    Stock {{ $availableQty }}
                                </span>
                            </div>

                            <div class="mb-3 text-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 140px;">
                                @else
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="height: 140px;">
                                        <i class="ti ti-photo fs-1"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    <div class="fw-bold fs-5">{{ number_format($product->price, 2) }}</div>
                                    @if(($product->tax_rate ?? 0) > 0)
                                        <div class="small text-muted">TVA {{ number_format($product->tax_rate, 2) }}%</div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('eshop360.cart.add', $instance->slug ?? '') }}" class="d-flex gap-2 align-items-center">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    @if($activeChannelId !== '' && !$activeIsCodifarm)
                                        <input type="hidden" name="channel_id" value="{{ $activeChannelId }}">
                                    @endif
                                    @if($activeIsCodifarm)
                                        <input type="hidden" name="is_codifarm" value="1">
                                    @endif
                                    <input type="number" name="quantity" class="form-control form-control-sm" min="1" value="1" style="width: 78px;">
                                    <button type="submit" class="btn btn-primary btn-sm" {{ $availableQty <= 0 ? 'disabled' : '' }}>
                                        <i class="ti ti-plus me-1"></i>Ajouter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            Aucun produit disponible pour ce filtre.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($products->hasPages())
            <div class="mt-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Session caisse</h5></div>
            <div class="card-body">
                @if($currentRegister)
                    <div class="border rounded p-3 mb-3 bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">Caisse ouverte</div>
                                <div class="small text-muted">{{ optional($currentRegister->opened_at)->format('d/m/Y H:i') }}</div>
                            </div>
                            <span class="badge bg-success">Ouverte</span>
                        </div>
                        <div class="small text-muted mb-1">Point de vente</div>
                        <div class="mb-2">{{ $currentRegister->store->name ?? 'Non affecte' }}</div>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="text-muted">Fond initial</div>
                                <div class="fw-semibold">{{ number_format($currentRegister->opening_amount, 2) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Theorique cash</div>
                                <div class="fw-semibold">{{ number_format($currentRegisterExpected ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('eshop360.pos.registers.close', [$instance->slug ?? '', $currentRegister]) }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Montant en caisse</label>
                            <input type="number" name="closing_amount" class="form-control" min="0" step="0.01" value="{{ old('closing_amount', $currentRegisterExpected ?? 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Observation de cloture">
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="ti ti-lock me-1"></i>Cloturer la caisse
                            </button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning py-2">
                        Aucune caisse ouverte. Les ventes POS restent possibles, mais sans rapprochement caisse.
                    </div>

                    <form method="POST" action="{{ route('eshop360.pos.registers.open', $instance->slug ?? '') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Point de vente</label>
                            <select name="store_id" class="form-select">
                                <option value="">Aucun</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fond initial</label>
                            <input type="number" name="opening_amount" class="form-control" min="0" step="0.01" value="{{ old('opening_amount', 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Debut de session caisse">
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-lock-open me-1"></i>Ouvrir la caisse
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Panier courant</h5>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-dark">{{ $cartItemCount }} article(s)</span>
                    @if(!empty($cart))
                        <form method="POST" action="{{ route('eshop360.cart.clear', $instance->slug ?? '') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Vider</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($activeContextLabel)
                    <div class="alert alert-secondary py-2">
                        Tarification active: <strong>{{ $activeContextLabel }}</strong>
                    </div>
                @endif
                @if(empty($cart))
                    <div class="text-center text-muted py-4">
                        <div class="fs-1 mb-2"><i class="ti ti-shopping-cart"></i></div>
                        Aucun produit dans le panier.
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($cart as $itemKey => $item)
                            @php
                                $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                                $quantity = (int) ($item['quantity'] ?? 0);
                                $lineTotal = (float) ($item['total'] ?? ($unitPrice * $quantity));
                                $lineDiscount = 0.0;
                                if (isset($item['original_price']) && (float) $item['original_price'] > $unitPrice) {
                                    $lineDiscount = ((float) $item['original_price'] - $unitPrice) * $quantity;
                                } elseif (isset($item['discount'])) {
                                    $lineDiscount = (float) $item['discount'];
                                }
                            @endphp
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $item['name'] ?? $item['product_name'] ?? 'Produit' }}</div>
                                        <div class="small text-muted">{{ $item['sku'] ?? '---' }}</div>
                                        <div class="small text-muted">PU {{ number_format($unitPrice, 2) }}</div>
                                        @if($lineDiscount > 0)
                                            <div class="small text-danger">Remise ligne -{{ number_format($lineDiscount, 2) }}</div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ number_format($lineTotal, 2) }}</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between gap-2 mt-3">
                                    <form method="POST" action="{{ route('eshop360.cart.update', [$instance->slug ?? '', $itemKey]) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="quantity" class="form-control form-control-sm" min="1" value="{{ $quantity }}" style="width: 78px;">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Maj</button>
                                    </form>
                                    <form method="POST" action="{{ route('eshop360.cart.remove', [$instance->slug ?? '', $itemKey]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Retirer</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Mises en attente</h5>
                <span class="badge bg-secondary">{{ $holdings->count() }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('eshop360.pos.holdings.store', $instance->slug ?? '') }}" class="row g-3 mb-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Client</label>
                        <select name="customer_id" class="form-select" {{ empty($cart) ? 'disabled' : '' }}>
                            <option value="">Client comptoir</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Ex: attente pharmacien" {{ empty($cart) ? 'disabled' : '' }}>
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-outline-secondary" {{ empty($cart) ? 'disabled' : '' }}>
                            <i class="ti ti-player-pause me-1"></i>Mettre le panier en attente
                        </button>
                    </div>
                </form>

                @if($holdings->isEmpty())
                    <p class="text-muted mb-0">Aucune mise en attente active.</p>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($holdings as $holding)
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $holding->reference }}</div>
                                        <div class="small text-muted">{{ $holding->customer->name ?? 'Client comptoir' }}</div>
                                        <div class="small text-muted">{{ $holding->items_count }} ligne(s) · {{ optional($holding->created_at)->format('d/m/Y H:i') }}</div>
                                        @if($holding->held_coupon)
                                            <div class="small text-info">Coupon {{ $holding->held_coupon['code'] ?? 'actif' }}</div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ number_format($holding->total, 2) }}</div>
                                        <form method="POST" action="{{ route('eshop360.pos.holdings.resume', [$instance->slug ?? '', $holding]) }}" class="mt-2">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Reprendre</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Coupon et resume</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('eshop360.cart.coupon', $instance->slug ?? '') }}" class="d-flex gap-2 mb-3">
                    @csrf
                    <input type="text" name="code" class="form-control" placeholder="Code coupon" value="{{ old('code', $coupon['code'] ?? '') }}">
                    <button type="submit" class="btn btn-outline-secondary">Appliquer</button>
                </form>

                @if($coupon)
                    <div class="alert alert-info py-2">
                        Coupon actif: <strong>{{ $coupon['code'] }}</strong>
                    </div>
                @endif

                <table class="table table-borderless mb-0">
                    <tr>
                        <th>Sous-total</th>
                        <td class="text-end">{{ number_format($totals['subtotal'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Taxes</th>
                        <td class="text-end">{{ number_format($totals['tax'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Remise</th>
                        <td class="text-end text-danger">-{{ number_format($totals['discount'], 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <th class="fw-bold">Total</th>
                        <td class="text-end fw-bold">{{ number_format($totals['total'], 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Encaissement direct</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('eshop360.sales.store', $instance->slug ?? '') }}">
                    @csrf
                    <input type="hidden" name="source" value="pos">
                    <input type="hidden" name="coupon_code" value="{{ $coupon['code'] ?? '' }}">
                    <input type="hidden" name="discount_amount" value="{{ $totals['discount'] }}">
                    @if(($cartContext['channel_id'] ?? null) !== null && !($cartContext['is_codifarm'] ?? false))
                        <input type="hidden" name="channel_id" value="{{ $cartContext['channel_id'] }}">
                    @endif
                    @if($cartContext['is_codifarm'] ?? false)
                        <input type="hidden" name="is_codifarm" value="1">
                    @endif

                    @foreach(array_values($cart) as $index => $item)
                        @php
                            $hiddenUnitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                            $hiddenQuantity = (int) ($item['quantity'] ?? 0);
                            $hiddenOriginalPrice = (float) ($item['original_price'] ?? $hiddenUnitPrice);
                            $usesOriginalPrice = $hiddenOriginalPrice > $hiddenUnitPrice;
                        @endphp
                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['product_id'] }}">
                        <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $hiddenQuantity }}">
                        <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $hiddenUnitPrice }}">
                        <input type="hidden" name="items[{{ $index }}][original_price]" value="{{ $hiddenOriginalPrice }}">
                        @if(!$usesOriginalPrice && isset($item['discount']))
                            <input type="hidden" name="items[{{ $index }}][discount]" value="{{ (float) $item['discount'] }}">
                        @endif
                    @endforeach

                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="customer_id" class="form-select">
                            <option value="">Client comptoir</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (string) old('customer_id', $settings['default_customer_id'] ?? '') === (string) $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Paiement</label>
                        <select name="payment_method" class="form-select">
                            @foreach($paymentMethods as $value => $label)
                                <option value="{{ $value }}" {{ old('payment_method', array_key_first($paymentMethods)) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Montant recu</label>
                            <input type="number" name="paid_amount" class="form-control" min="0" step="0.01" value="{{ old('paid_amount', $totals['total']) }}" {{ empty($cart) ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Livraison</label>
                            <input type="number" name="shipping_amount" class="form-control" min="0" step="0.01" value="{{ old('shipping_amount', 0) }}" {{ empty($cart) ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" {{ empty($cart) ? 'disabled' : '' }}>{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" {{ empty($cart) ? 'disabled' : '' }}>
                            <i class="ti ti-credit-card me-1"></i>Valider la vente
                        </button>
                        <a href="{{ route('eshop360.checkout.index', $instance->slug ?? '') }}" class="btn btn-white border {{ empty($cart) ? 'disabled' : '' }}">
                            Passer par le checkout complet
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const instanceSlug = @json($instance->slug ?? '');
    const searchUrl    = '/i/' + instanceSlug + '/products/search';
    const cartAddUrl   = @json(route('eshop360.cart.add', $instance->slug ?? ''));
    const csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const barcodeInput = document.getElementById('pos-barcode-input');
    const barcodeBtn   = document.getElementById('pos-barcode-btn');

    if (!barcodeInput) return;

    async function searchAndAdd(query) {
        query = query.trim();
        if (!query) return;

        try {
            const resp = await fetch(searchUrl + '?barcode=' + encodeURIComponent(query));
            const data = await resp.json();

            if (!data.product) {
                barcodeInput.classList.add('is-invalid');
                setTimeout(() => barcodeInput.classList.remove('is-invalid'), 1500);
                return;
            }

            // Add to cart via form POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = cartAddUrl;
            form.style.display = 'none';

            const fields = { _token: csrfToken, product_id: data.product.id, quantity: 1 };
            for (const [k, v] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.name = k;
                input.value = v;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        } catch (e) {
            console.error('Barcode search error', e);
        }
    }

    barcodeInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value;
            this.value = '';
            searchAndAdd(val);
        }
    });

    barcodeBtn.addEventListener('click', function () {
        const val = barcodeInput.value;
        barcodeInput.value = '';
        searchAndAdd(val);
    });

    // Hardware scanner: rapid keypresses while page doesn't have a focused input elsewhere
    let buffer = '';
    let timer  = null;
    document.addEventListener('keypress', function (e) {
        if (document.activeElement === barcodeInput) return; // let the input handle it
        if (e.key === 'Enter') {
            clearTimeout(timer);
            if (buffer.length > 2) {
                searchAndAdd(buffer);
            }
            buffer = '';
            return;
        }
        buffer += e.key;
        clearTimeout(timer);
        timer = setTimeout(() => { buffer = ''; }, 100);
    });
})();
</script>

</x-dashboard::layouts.master>
