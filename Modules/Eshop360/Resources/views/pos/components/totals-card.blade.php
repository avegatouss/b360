{{-- POS Totals Card --}}
<div class="card mb-2">
    <div class="card-body py-2" id="pos-totals-card">
        <div class="d-flex justify-content-between small mb-1">
            <span class="text-muted">{{ __('Sous-total') }}</span>
            <span id="pos-subtotal">{{ number_format($totals['subtotal'], 2, '.', '') }}</span>
        </div>
        <div class="d-flex justify-content-between small mb-1" id="pos-tax-row" style="{{ $totals['tax'] > 0 ? '' : 'display:none' }}">
            <span class="text-muted">{{ __('Taxes') }}</span>
            <span id="pos-tax">{{ number_format($totals['tax'], 2, '.', '') }}</span>
        </div>
        <div class="d-flex justify-content-between small mb-1 text-danger" id="pos-discount-row" style="{{ $totals['discount'] > 0 ? '' : 'display:none' }}">
            <span>{{ __('Remise') }}</span>
            <span id="pos-discount">-{{ number_format($totals['discount'], 2, '.', '') }}</span>
        </div>
        <hr class="my-1">
        <div class="d-flex justify-content-between pos-total-row fw-bold">
            <span>{{ __('TOTAL') }}</span>
            <span class="text-primary" id="pos-total-display">{{ number_format($totals['total'], 2, '.', '') }}</span>
        </div>
    </div>
</div>
