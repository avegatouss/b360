<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($channel->name ?? __('Shop')) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --shop-primary: __BLADE_BLOCK_3__;
        }
        body { background: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; }
        .shop-navbar { background: var(--shop-primary); }
        .shop-navbar .navbar-brand,
        .shop-navbar .nav-link { color: #fff !important; }
        .shop-navbar .nav-link:hover { opacity: .85; }
        .shop-footer { background: #343a40; color: #adb5bd; margin-top: auto; }
        .btn-shop { background: var(--shop-primary); color: #fff; border: none; }
        .btn-shop:hover { opacity: .9; color: #fff; }
        .badge-cart { position: relative; top: -8px; font-size: .65rem; }
        .product-card { transition: box-shadow .2s; }
        .product-card:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.1); }
    </style>
</head>
<body>

{{-- Top Navigation --}}
<nav class="navbar navbar-expand-lg shop-navbar py-3">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
            @if(($channel->portal_settings['logo_url'] ?? null))
                <img src="{{ $channel->portal_settings['logo_url'] }}" alt="{{ $channel->name }}" height="32" class="me-2">
            @endif
            {{ $channel->name }}
        </a>
        <button class="navbar-toggler border-light" type="button" data-bs-toggle="collapse" data-bs-target="#shopNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="shopNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('eshop360.channel-shop.catalog', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
                        {{ __('Catalog') }}
                    </a>
                </li>
                @include('lang::components.language-switcher')
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('eshop360.channel-shop.cart', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
                            {{ __('Cart') }}
                            @if(count($cart ?? []) > 0)
                                <span class="badge bg-light text-dark badge-cart">{{ count($cart) }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('eshop360.channel-shop.orders', [$instance->slug ?? '', $channel->slug ?? $channel->id]) }}">
                            {{ __('My Orders') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-white-50">{{ auth()->user()->name }}</span>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{-- Flash Messages --}}
<div class="container mt-3">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>

{{-- Page Content --}}
<main class="container py-4 flex-fill">
    {{ $slot ?? '' }}
    @yield('content')
</main>

{{-- Footer --}}
<footer class="shop-footer py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-white">{{ $channel->name }}</h6>
                @if($channel->description)
                    <p class="small mb-0">{{ $channel->description }}</p>
                @endif
            </div>
            <div class="col-md-6 text-md-end">
                <p class="small mb-0">&copy; {{ date('Y') }} {{ $channel->name }}. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
