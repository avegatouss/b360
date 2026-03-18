{{-- POS Customer Selector --}}
@if($activeContextLabel ?? null)
    <div class="alert alert-info py-1 px-3 mb-2 small">
        <i class="ti ti-tag me-1"></i>{{ __('Tarification') }}: <strong>{{ $activeContextLabel }}</strong>
    </div>
@endif

<div class="card mb-2 pos-customer-bar">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-user fs-5 text-primary"></i>
            <select id="pos-customer-select" class="form-select form-select-sm">
                <option value="">{{ __('Client de passage') }}</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ (string) old('customer_id', $settings['default_customer_id'] ?? '') === (string) $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
