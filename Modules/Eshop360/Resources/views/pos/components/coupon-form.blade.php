{{-- POS Coupon Form --}}
<div class="card mb-2">
    <div class="card-body py-2">
        <div class="d-flex gap-2" id="pos-coupon-form">
            <input type="text" id="pos-coupon-input" class="form-control form-control-sm" placeholder="{{ __('Code coupon') }}" value="{{ $coupon['code'] ?? '' }}">
            <button type="button" class="btn btn-sm btn-outline-primary" id="pos-coupon-btn"><i class="ti ti-tag"></i></button>
        </div>
        <div id="pos-coupon-status" class="{{ $coupon ? '' : 'd-none' }} mt-1 small text-success">
            @if($coupon)
                <i class="ti ti-check me-1"></i>{{ __('Coupon actif') }}: {{ $coupon['code'] }}
            @endif
        </div>
    </div>
</div>
