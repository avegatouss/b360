<x-dashboard::layouts.master
    :title="__('POS') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('POS')">

@php
    $activeChannelId = (string) request('channel_id', $cartContext['channel_id'] ?? '');
    $activeContextLabel = $channels->firstWhere('id', $activeChannelId !== '' ? (int) $activeChannelId : ($cartContext['channel_id'] ?? null))?->name ?? null;
    $registerOpen = (bool) $currentRegister;
    $instanceId = $instance->id ?? 0;
@endphp

@include('eshop360::pos.components.styles')

@include('eshop360::pos.components.top-bar')

<div class="row g-3 pos-wrapper">
    {{-- LEFT: Products --}}
    <div class="col-xl-8">
        @include('eshop360::pos.components.barcode-scanner')
        @include('eshop360::pos.components.filter-bar')
        @include('eshop360::pos.components.product-grid')
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="col-xl-4">
        <div class="pos-sidebar">
            @include('eshop360::pos.components.register-bar')
            @include('eshop360::pos.components.customer-selector')
            @include('eshop360::pos.components.cart-sidebar')
            @include('eshop360::pos.components.coupon-form')
            @include('eshop360::pos.components.totals-card')
            @include('eshop360::pos.components.checkout-form')
            @include('eshop360::pos.components.holdings-panel')
        </div>
    </div>
</div>

@include('eshop360::pos.components.toasts')
@include('eshop360::pos.components.scripts')

</x-dashboard::layouts.master>
