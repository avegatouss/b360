<x-dashboard::layouts.master
    :title="__('POS') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('POS')">

@php
    $activeChannelId = (string) request('channel_id', $cartContext['channel_id'] ?? '');
    $activeContextLabel = $channels->firstWhere('id', $activeChannelId !== '' ? (int) $activeChannelId : ($cartContext['channel_id'] ?? null))?->name ?? null;
    $registerOpen = (bool) $currentRegister;
    $posRoute = 'eshop360.pos.layout4';
    $productColClass = 'col-6 col-md-4 col-lg-3';
@endphp

@include('eshop360::pos.components.styles')
<style>
    /* Layout 4: Sidebar on left, products on right */
    .pos-layout4-sidebar { order: -1; }
    @media (max-width: 1199px) { .pos-layout4-sidebar { order: 0; } }
</style>

@include('eshop360::pos.components.top-bar')

<div class="row g-3 pos-wrapper">
    <div class="col-xl-8">
        @include('eshop360::pos.components.barcode-scanner')
        @include('eshop360::pos.components.filter-bar')
        @include('eshop360::pos.components.product-grid')
    </div>
    <div class="col-xl-4 pos-layout4-sidebar">
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
