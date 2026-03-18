@extends('eshop360::channel-portal.layouts.master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ __('Nouvelle commande') }}</h4>
        <p class="text-muted mb-0">{{ __('Commander des produits du catalogue') }} &mdash; {{ $channel->name }}</p>
    </div>
    <a href="{{ route('eshop360.channel-portal.orders.index', [request()->route('slug'), $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
    </a>
</div>

<form method="POST" action="{{ route('eshop360.channel-portal.orders.store', [request()->route('slug'), $channel->slug ?? $channel->id]) }}" id="orderForm">
    @csrf

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Products --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">{{ __('Produits') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="productsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>{{ __('Produit') }}</th>
                                    <th class="text-end" style="width: 120px;">{{ __('Prix canal') }}</th>
                                    <th style="width: 120px;">{{ __('Quantité') }}</th>
                                    <th class="text-end" style="width: 120px;">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $idx => $product)
                                    @php
                                        $channelPrice = $product->pivot->sale_price ?? 0;
                                    @endphp
                                    <tr data-price="{{ $channelPrice }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input product-check"
                                                   data-idx="{{ $idx }}" value="{{ $product->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $product->name }}</span>
                                            @if($product->category)
                                                <br><small class="text-muted">{{ $product->category->name }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($channelPrice, 0, ',', ' ') }}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm qty-input"
                                                   data-idx="{{ $idx }}" min="1" value="1" disabled>
                                            <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $product->id }}" disabled>
                                            <input type="hidden" name="items[{{ $idx }}][quantity]" value="1" disabled>
                                        </td>
                                        <td class="text-end fw-medium line-total" data-idx="{{ $idx }}">0</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            {{ __('Aucun produit disponible pour ce canal.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">{{ __('Résumé') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Client') }}</label>
                        <select name="customer_id" class="form-select">
                            <option value="">{{ __('-- Aucun --') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3" maxlength="500">{{ old('notes') }}</textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('Articles sélectionnés') }}</span>
                        <span class="fw-bold" id="selectedCount">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">{{ __('Total') }}</span>
                        <span class="fw-bold fs-5 text-primary" id="orderTotal">0</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                        <i class="ti ti-check me-1"></i>{{ __('Passer la commande') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var checks = document.querySelectorAll('.product-check');
    var qtyInputs = document.querySelectorAll('.qty-input');

    function recalculate() {
        var total = 0;
        var count = 0;

        checks.forEach(function (cb) {
            var idx = cb.dataset.idx;
            var row = cb.closest('tr');
            var price = parseFloat(row.dataset.price) || 0;
            var qtyInput = row.querySelector('.qty-input');
            var hiddenPid = row.querySelector('input[name$="[product_id]"]');
            var hiddenQty = row.querySelector('input[name$="[quantity]"]');
            var lineCell = row.querySelector('.line-total');

            if (cb.checked) {
                var qty = parseInt(qtyInput.value) || 1;
                var lineTotal = price * qty;
                total += lineTotal;
                count++;
                lineCell.textContent = lineTotal.toLocaleString('fr-FR');
                qtyInput.disabled = false;
                hiddenPid.disabled = false;
                hiddenQty.disabled = false;
                hiddenQty.value = qty;
            } else {
                lineCell.textContent = '0';
                qtyInput.disabled = true;
                hiddenPid.disabled = true;
                hiddenQty.disabled = true;
            }
        });

        document.getElementById('selectedCount').textContent = count;
        document.getElementById('orderTotal').textContent = total.toLocaleString('fr-FR');
        document.getElementById('submitBtn').disabled = count === 0;
    }

    checks.forEach(function (cb) { cb.addEventListener('change', recalculate); });
    qtyInputs.forEach(function (inp) { inp.addEventListener('input', recalculate); });
});
</script>
@endsection
