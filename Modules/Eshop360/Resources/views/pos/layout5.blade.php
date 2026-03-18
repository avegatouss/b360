<x-dashboard::layouts.master
    :title="__('POS') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('POS')">

@php
    $activeChannelId = (string) request('channel_id', $cartContext['channel_id'] ?? '');
    $activeContextLabel = $channels->firstWhere('id', $activeChannelId !== '' ? (int) $activeChannelId : ($cartContext['channel_id'] ?? null))?->name ?? null;
    $registerOpen = (bool) $currentRegister;
    $posRoute = 'eshop360.pos.layout5';
    $productColClass = 'col-4 col-md-3 col-lg-2';
@endphp

@include('eshop360::pos.components.styles')
<style>
    /* Layout 5: Full-width products on top, cart below */
    .pos-layout5-products { min-height: 50vh; }
    .pos-layout5-cart .pos-sidebar { position: static; max-height: none; display: flex; gap: 1rem; flex-wrap: wrap; }
    .pos-layout5-cart .pos-sidebar > .card { flex: 1 1 300px; }
</style>

@include('eshop360::pos.components.top-bar')

<div class="pos-wrapper">
    <div class="pos-layout5-products mb-3">
        @include('eshop360::pos.components.barcode-scanner')
        @include('eshop360::pos.components.filter-bar')
        @include('eshop360::pos.components.product-grid')
    </div>
    <div class="pos-layout5-cart">
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
