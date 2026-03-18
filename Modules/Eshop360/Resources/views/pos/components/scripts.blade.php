{{-- POS Shared JavaScript --}}
<script>
(function () {
    'use strict';

    var instanceSlug = @json($instance->slug ?? '');
    var searchUrl    = '/i/' + instanceSlug + '/products/search';
    var cartAddUrl   = @json(route('eshop360.cart.add', $instance->slug ?? ''));
    var cartClearUrl = @json(route('eshop360.cart.clear', $instance->slug ?? ''));
    var cartCouponUrl = @json(route('eshop360.cart.coupon', $instance->slug ?? ''));
    @php
        $cartUpdateBaseUrl = route('eshop360.cart.update', [$instance->slug ?? '', '__KEY__']);
        $cartRemoveBaseUrl = route('eshop360.cart.remove', [$instance->slug ?? '', '__KEY__']);
    @endphp
    var cartUpdateBaseUrl = @json($cartUpdateBaseUrl);
    var cartRemoveBaseUrl = @json($cartRemoveBaseUrl);
    var csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    var activeChannelId = @json($activeChannelId ?? '');
    var currentTotal = {{ (float) ($totals['total'] ?? 0) }};

    var barcodeInput   = document.getElementById('pos-barcode-input');
    var barcodeBtn     = document.getElementById('pos-barcode-btn');
    var cartBadge      = document.getElementById('pos-cart-badge');
    var cartBody       = document.getElementById('pos-cart-body');
    var cartClearBtn   = document.getElementById('pos-cart-clear-btn');
    var couponInput    = document.getElementById('pos-coupon-input');
    var couponBtn      = document.getElementById('pos-coupon-btn');
    var couponStatus   = document.getElementById('pos-coupon-status');
    var subtotalEl     = document.getElementById('pos-subtotal');
    var taxEl          = document.getElementById('pos-tax');
    var taxRow         = document.getElementById('pos-tax-row');
    var discountEl     = document.getElementById('pos-discount');
    var discountRow    = document.getElementById('pos-discount-row');
    var totalDisplay   = document.getElementById('pos-total-display');
    var validateBtn    = document.getElementById('pos-validate-btn');
    var validateTotal  = document.getElementById('pos-validate-total');
    var paidInput      = document.getElementById('pos-paid-amount');
    var changeDisplay  = document.getElementById('pos-change-display');
    var checkoutItems  = document.getElementById('pos-checkout-items');
    var checkoutCoupon = document.getElementById('pos-checkout-coupon');
    var checkoutDiscount = document.getElementById('pos-checkout-discount');
    var checkoutCustomer = document.getElementById('pos-checkout-customer');
    var customerSelect = document.getElementById('pos-customer-select');
    var toastEl        = document.getElementById('pos-toast');

    function fmt(n) { return Math.round(n).toLocaleString('fr-FR'); }

    var toastTimer = null;
    function showToast(msg, type) {
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.className = 'pos-toast show ' + (type || 'success');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2500);
    }

    function ajaxCart(url, method, body, onSuccess) {
        var opts = {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        if (body) opts.body = JSON.stringify(body);
        fetch(url, opts)
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok) { showToast(result.data.message || 'Erreur', 'error'); return; }
                if (onSuccess) onSuccess(result.data);
            })
            .catch(function () { showToast('Erreur de connexion', 'error'); });
    }

    function updateCartUI(data) {
        var items = data.items || [];
        var count = 0;
        for (var i = 0; i < items.length; i++) count += (items[i].quantity || 0);

        if (cartBadge) cartBadge.textContent = String(count);

        var subtotal = data.subtotal || 0, tax = data.tax || 0, discount = data.discount || 0, total = data.total || 0;
        currentTotal = total;

        if (subtotalEl) subtotalEl.textContent = fmt(subtotal);
        if (taxRow) { taxRow.style.display = tax > 0 ? '' : 'none'; if (taxEl) taxEl.textContent = fmt(tax); }
        if (discountRow) { discountRow.style.display = discount > 0 ? '' : 'none'; if (discountEl) discountEl.textContent = '-' + fmt(discount); }
        if (totalDisplay) totalDisplay.textContent = fmt(total);
        if (validateTotal) validateTotal.textContent = fmt(total);
        if (paidInput) { paidInput.value = Math.round(total); paidInput.disabled = items.length === 0; }
        updateChange();
        if (validateBtn) validateBtn.disabled = items.length === 0;
        if (cartClearBtn) cartClearBtn.style.display = items.length > 0 ? '' : 'none';
        if (checkoutDiscount) checkoutDiscount.value = discount;
        if (data.coupon && checkoutCoupon) checkoutCoupon.value = data.coupon.code || '';

        rebuildCartList(items);
        rebuildCheckoutItems(items);
    }

    function rebuildCartList(items) {
        if (!cartBody) return;
        while (cartBody.firstChild) cartBody.removeChild(cartBody.firstChild);

        if (items.length === 0) {
            var emptyDiv = document.createElement('div');
            emptyDiv.className = 'text-center text-muted py-4';
            var icon = document.createElement('i'); icon.className = 'ti ti-shopping-cart-off fs-1 d-block mb-1'; emptyDiv.appendChild(icon);
            var small = document.createElement('small'); small.textContent = @json(__('Panier vide')); emptyDiv.appendChild(small);
            cartBody.appendChild(emptyDiv);
            return;
        }

        var listGroup = document.createElement('div');
        listGroup.className = 'list-group list-group-flush';

        for (var i = 0; i < items.length; i++) {
            var item = items[i], key = String(item.product_id);
            var unitPrice = parseFloat(item.unit_price) || 0, qty = parseInt(item.quantity) || 0;
            var lineDiscount = parseFloat(item.line_discount) || 0, lineTotal = parseFloat(item.total) || 0;

            var li = document.createElement('div');
            li.className = 'list-group-item pos-cart-item px-3 py-2';
            li.setAttribute('data-item-key', key);

            var topRow = document.createElement('div'); topRow.className = 'd-flex justify-content-between align-items-start';
            var infoDiv = document.createElement('div'); infoDiv.className = 'flex-grow-1 me-2';
            var nameDiv = document.createElement('div'); nameDiv.className = 'fw-semibold small pos-item-name'; nameDiv.textContent = item.name || 'Produit'; infoDiv.appendChild(nameDiv);
            var detailDiv = document.createElement('div'); detailDiv.className = 'text-muted pos-item-detail'; detailDiv.style.fontSize = '.75rem';
            detailDiv.textContent = fmt(unitPrice) + ' x ' + qty + (lineDiscount > 0 ? ' (-' + lineDiscount + '%)' : ''); infoDiv.appendChild(detailDiv);
            topRow.appendChild(infoDiv);
            var totalDiv = document.createElement('div'); totalDiv.className = 'fw-bold small pos-item-total'; totalDiv.textContent = fmt(lineTotal); topRow.appendChild(totalDiv);
            li.appendChild(topRow);

            var bottomRow = document.createElement('div'); bottomRow.className = 'd-flex justify-content-between align-items-center mt-1';
            var qtyControls = document.createElement('div'); qtyControls.className = 'd-flex align-items-center gap-1';

            var minusBtn = document.createElement('button'); minusBtn.type = 'button'; minusBtn.className = 'btn btn-outline-secondary btn-sm px-1 py-0 pos-qty-btn'; minusBtn.style.fontSize = '.7rem';
            minusBtn.setAttribute('data-action', 'decrement'); minusBtn.setAttribute('data-key', key); minusBtn.setAttribute('data-qty', String(Math.max(1, qty - 1)));
            var mi = document.createElement('i'); mi.className = 'ti ti-minus'; minusBtn.appendChild(mi); qtyControls.appendChild(minusBtn);

            var qtySpan = document.createElement('span'); qtySpan.className = 'pos-quick-qty small fw-bold pos-item-qty'; qtySpan.textContent = String(qty); qtyControls.appendChild(qtySpan);

            var plusBtn = document.createElement('button'); plusBtn.type = 'button'; plusBtn.className = 'btn btn-outline-secondary btn-sm px-1 py-0 pos-qty-btn'; plusBtn.style.fontSize = '.7rem';
            plusBtn.setAttribute('data-action', 'increment'); plusBtn.setAttribute('data-key', key); plusBtn.setAttribute('data-qty', String(qty + 1));
            var pi = document.createElement('i'); pi.className = 'ti ti-plus'; plusBtn.appendChild(pi); qtyControls.appendChild(plusBtn);
            bottomRow.appendChild(qtyControls);

            var rightControls = document.createElement('div'); rightControls.className = 'd-flex align-items-center gap-1';
            var discountGroup = document.createElement('div'); discountGroup.className = 'input-group input-group-sm'; discountGroup.style.width = '80px';
            var discountInput = document.createElement('input'); discountInput.type = 'number'; discountInput.className = 'form-control pos-line-discount';
            discountInput.min = '0'; discountInput.max = '100'; discountInput.step = '1'; discountInput.value = String(Math.round(lineDiscount));
            discountInput.setAttribute('data-key', key); discountInput.placeholder = '%'; discountGroup.appendChild(discountInput);
            var discountAddon = document.createElement('span'); discountAddon.className = 'input-group-text'; discountAddon.style.fontSize = '.65rem'; discountAddon.style.padding = '.1rem .25rem'; discountAddon.textContent = '%'; discountGroup.appendChild(discountAddon);
            rightControls.appendChild(discountGroup);

            var removeBtn = document.createElement('button'); removeBtn.type = 'button'; removeBtn.className = 'btn btn-sm btn-link text-danger p-0 pos-remove-btn'; removeBtn.setAttribute('data-key', key);
            var ri = document.createElement('i'); ri.className = 'ti ti-x'; ri.style.fontSize = '.8rem'; removeBtn.appendChild(ri); rightControls.appendChild(removeBtn);
            bottomRow.appendChild(rightControls);
            li.appendChild(bottomRow);
            listGroup.appendChild(li);
        }
        cartBody.appendChild(listGroup);
    }

    function rebuildCheckoutItems(items) {
        if (!checkoutItems) return;
        while (checkoutItems.firstChild) checkoutItems.removeChild(checkoutItems.firstChild);
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var fields = { 'product_id': item.product_id, 'quantity': item.quantity, 'unit_price': item.unit_price, 'original_price': item.original_price || item.unit_price, 'line_discount': item.line_discount || 0 };
            for (var field in fields) {
                var input = document.createElement('input'); input.type = 'hidden';
                input.name = 'items[' + i + '][' + field + ']'; input.value = String(fields[field]);
                checkoutItems.appendChild(input);
            }
        }
    }

    function cartUpdateUrl(key) { return cartUpdateBaseUrl.replace('__KEY__', encodeURIComponent(key)); }
    function cartRemoveUrl(key) { return cartRemoveBaseUrl.replace('__KEY__', encodeURIComponent(key)); }

    function addToCart(productId, variationId) {
        var body = { product_id: productId, quantity: 1 };
        if (variationId) body.variation_id = variationId;
        if (activeChannelId !== '') body.channel_id = activeChannelId;
        ajaxCart(cartAddUrl, 'POST', body, function (data) {
            showToast(data.message || @json(__('Produit ajoute')), 'success');
            updateCartUI(data);
        });
    }

    // Product variations data (injected from server)
    @php
        $variationsData = [];
        foreach (($products ?? collect()) as $p) {
            if ($p->relationLoaded('variations') && $p->variations->where('is_active', true)->isNotEmpty()) {
                $variationsData[$p->id] = $p->variations->where('is_active', true)->map(function ($v) use ($p) {
                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'price' => (float) ($v->price ?? $p->price),
                        'sku' => $v->sku ?: $p->sku,
                        'values_label' => $v->values_label,
                    ];
                })->values()->toArray();
            }
        }
    @endphp
    var productVariations = @json($variationsData);

    function showVariationModal(productId, productName) {
        var variations = productVariations[productId] || [];
        if (!variations.length) { addToCart(productId); return; }

        var modal = document.getElementById('variationModal');
        var title = document.getElementById('variationModalTitle');
        var body = document.getElementById('variationModalBody');

        title.textContent = productName;
        while (body.firstChild) body.removeChild(body.firstChild);

        // Option: add without variation
        var noVarBtn = document.createElement('button');
        noVarBtn.type = 'button';
        noVarBtn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
        noVarBtn.textContent = @json(__('Standard (sans variante)'));
        noVarBtn.addEventListener('click', function () {
            addToCart(productId);
            bootstrap.Modal.getInstance(modal).hide();
        });
        body.appendChild(noVarBtn);

        for (var i = 0; i < variations.length; i++) {
            (function (v) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

                var nameSpan = document.createElement('span');
                nameSpan.className = 'fw-medium';
                nameSpan.textContent = v.name;
                if (v.values_label && v.values_label !== v.name) {
                    var small = document.createElement('small');
                    small.className = 'text-muted ms-2';
                    small.textContent = v.values_label;
                    nameSpan.appendChild(small);
                }
                btn.appendChild(nameSpan);

                var priceSpan = document.createElement('span');
                priceSpan.className = 'badge bg-primary';
                priceSpan.textContent = Math.round(v.price).toLocaleString('fr-FR');
                btn.appendChild(priceSpan);

                btn.addEventListener('click', function () {
                    addToCart(productId, v.id);
                    bootstrap.Modal.getInstance(modal).hide();
                });
                body.appendChild(btn);
            })(variations[i]);
        }

        new bootstrap.Modal(modal).show();
    }

    document.querySelectorAll('.pos-product-card:not(.out-of-stock)').forEach(function (card) {
        card.addEventListener('click', function () {
            if (card.classList.contains('adding')) return;
            var productId = card.getAttribute('data-product-id');
            var productName = card.getAttribute('data-product-name');
            if (!productId) return;

            if (card.hasAttribute('data-has-variations')) {
                showVariationModal(parseInt(productId), productName);
            } else {
                card.classList.add('adding');
                addToCart(productId);
                setTimeout(function () { card.classList.remove('adding'); }, 600);
            }
        });
    });

    if (cartBody) cartBody.addEventListener('click', function (e) {
        var target = e.target.closest('.pos-qty-btn');
        if (target) {
            var key = target.getAttribute('data-key'), qty = parseInt(target.getAttribute('data-qty'));
            var itemEl = target.closest('.pos-cart-item');
            var di = itemEl ? itemEl.querySelector('.pos-line-discount') : null;
            ajaxCart(cartUpdateUrl(key), 'PUT', { quantity: qty, line_discount: di ? parseFloat(di.value) || 0 : 0 }, function (data) { updateCartUI(data); });
            return;
        }
        var removeTarget = e.target.closest('.pos-remove-btn');
        if (removeTarget) {
            ajaxCart(cartRemoveUrl(removeTarget.getAttribute('data-key')), 'DELETE', null, function (data) { showToast(data.message || @json(__('Produit retire')), 'success'); updateCartUI(data); });
        }
    });

    var discountDebounce = {};
    if (cartBody) cartBody.addEventListener('change', function (e) {
        if (!e.target.classList.contains('pos-line-discount')) return;
        var input = e.target, key = input.getAttribute('data-key');
        var discount = Math.min(100, Math.max(0, parseFloat(input.value) || 0));
        input.value = Math.round(discount);
        var itemEl = input.closest('.pos-cart-item'), qtyEl = itemEl ? itemEl.querySelector('.pos-item-qty') : null;
        var qty = qtyEl ? parseInt(qtyEl.textContent) || 1 : 1;
        clearTimeout(discountDebounce[key]);
        discountDebounce[key] = setTimeout(function () {
            ajaxCart(cartUpdateUrl(key), 'PUT', { quantity: qty, line_discount: discount }, function (data) { showToast(@json(__('Remise appliquee')), 'success'); updateCartUI(data); });
        }, 400);
    });

    if (cartClearBtn) cartClearBtn.addEventListener('click', function () {
        ajaxCart(cartClearUrl, 'DELETE', null, function (data) { showToast(data.message || @json(__('Panier vide')), 'success'); updateCartUI(data); });
    });

    if (couponBtn) couponBtn.addEventListener('click', function () {
        var code = couponInput.value.trim();
        if (!code) return;
        ajaxCart(cartCouponUrl, 'POST', { code: code }, function (data) {
            showToast(data.message || @json(__('Coupon applique')), 'success');
            while (couponStatus.firstChild) couponStatus.removeChild(couponStatus.firstChild);
            if (data.coupon) {
                couponStatus.classList.remove('d-none');
                var ci = document.createElement('i'); ci.className = 'ti ti-check me-1'; couponStatus.appendChild(ci);
                couponStatus.appendChild(document.createTextNode(data.coupon.code + ' ' + @json(__('applique'))));
            }
            if (data.subtotal !== undefined) {
                if (subtotalEl) subtotalEl.textContent = fmt(data.subtotal);
                if (taxRow) { var t = data.tax || 0; taxRow.style.display = t > 0 ? '' : 'none'; if (taxEl) taxEl.textContent = fmt(t); }
                var d = data.discount || 0;
                if (discountRow) { discountRow.style.display = d > 0 ? '' : 'none'; if (discountEl) discountEl.textContent = '-' + fmt(d); }
                currentTotal = data.total || 0;
                if (totalDisplay) totalDisplay.textContent = fmt(currentTotal);
                if (validateTotal) validateTotal.textContent = fmt(currentTotal);
                if (checkoutDiscount) checkoutDiscount.value = d;
                if (checkoutCoupon) checkoutCoupon.value = data.coupon ? data.coupon.code : '';
                if (paidInput) paidInput.value = Math.round(currentTotal);
                updateChange();
            }
        });
    });

    if (couponInput) couponInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); couponBtn.click(); } });
    if (customerSelect) customerSelect.addEventListener('change', function () { if (checkoutCustomer) checkoutCustomer.value = this.value; });
    if (checkoutCustomer && customerSelect) checkoutCustomer.value = customerSelect.value;

    function searchAndAdd(query) {
        query = (query || '').trim();
        if (!query) return;
        fetch(searchUrl + '?barcode=' + encodeURIComponent(query))
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || !data.product) {
                    if (barcodeInput) { barcodeInput.classList.add('is-invalid'); setTimeout(function () { barcodeInput.classList.remove('is-invalid'); }, 2000); }
                    return;
                }
                addToCart(data.product.id);
            })
            .catch(function () { if (barcodeInput) { barcodeInput.classList.add('is-invalid'); setTimeout(function () { barcodeInput.classList.remove('is-invalid'); }, 1500); } });
    }

    if (barcodeInput) barcodeInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); var v = this.value; this.value = ''; searchAndAdd(v); } });
    if (barcodeBtn) barcodeBtn.addEventListener('click', function () { var v = barcodeInput.value; barcodeInput.value = ''; searchAndAdd(v); });

    var buffer = '', timer = null;
    document.addEventListener('keypress', function (e) {
        if (document.activeElement && ['INPUT','TEXTAREA','SELECT'].indexOf(document.activeElement.tagName) >= 0) return;
        if (e.key === 'Enter') { clearTimeout(timer); if (buffer.length > 2) searchAndAdd(buffer); buffer = ''; return; }
        buffer += e.key; clearTimeout(timer); timer = setTimeout(function () { buffer = ''; }, 150);
    });

    function updateChange() {
        if (!paidInput || !changeDisplay) return;
        var change = (parseFloat(paidInput.value) || 0) - currentTotal;
        changeDisplay.value = Math.max(0, Math.round(change)).toLocaleString('fr-FR');
        changeDisplay.style.color = change >= 0 ? '#198754' : '#dc3545';
    }
    if (paidInput) { paidInput.addEventListener('input', updateChange); updateChange(); }

    document.querySelectorAll('.toast.show').forEach(function (toast) { setTimeout(function () { toast.classList.remove('show'); }, 4000); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'F2') { e.preventDefault(); if (validateBtn && !validateBtn.disabled) validateBtn.click(); }
        if (e.key === 'Escape' && barcodeInput) { e.preventDefault(); barcodeInput.focus(); barcodeInput.select(); }
    });
})();
</script>
