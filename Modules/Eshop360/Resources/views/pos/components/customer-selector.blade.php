{{-- POS Customer Selector --}}
@php $allowWalkin = $settings['allow_walkin_customer'] ?? true; @endphp
@if($activeContextLabel ?? null)
    <div class="alert alert-info py-1 px-3 mb-2 small">
        <i class="ti ti-tag me-1"></i>{{ __('Tarification') }}: <strong>{{ $activeContextLabel }}</strong>
    </div>
@endif

<div class="card mb-2 pos-customer-bar">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-user fs-5 text-primary"></i>
            <select id="pos-customer-select" class="form-select form-select-sm pos-select2-filter" data-placeholder="{{ $allowWalkin ? __('Client de passage') : __('Selectionner un client') }}" {{ !$allowWalkin ? 'required' : '' }}>
                @if($allowWalkin)
                    <option value=""></option>
                @else
                    <option value="" disabled {{ empty(old('customer_id', $settings['default_customer_id'] ?? '')) ? 'selected' : '' }}>{{ __('Selectionner un client') }}</option>
                @endif
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ (string) old('customer_id', $settings['default_customer_id'] ?? '') === (string) $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        @if(!$allowWalkin)
            <small class="text-danger mt-1 d-block" id="pos-customer-required-msg" style="display: none !important;"><i class="ti ti-alert-circle me-1"></i>{{ __('Client obligatoire') }}</small>
        @endif
    </div>
</div>
