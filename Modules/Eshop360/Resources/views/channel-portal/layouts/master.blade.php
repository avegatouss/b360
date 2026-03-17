@php
    $channel = $resolvedChannel ?? $channel ?? null;
    $portalSettings = $channel?->portal_settings ?? [];
    $primaryColor = $portalSettings['primary_color'] ?? '#6366f1';
    $sidebarBg = $portalSettings['sidebar_bg'] ?? '#1e293b';
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $channel->name ?? __('Portail Canal') }} &mdash; {{ config('app.name', 'B360') }}</title>

    @php $brandFavicon = setting('branding.favicon'); @endphp
    <link rel="shortcut icon" type="image/x-icon" href="{{ $brandFavicon ? asset('storage/' . $brandFavicon) : asset('build/img/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/bootstrap.min.css') }}">
    <!-- Tabler Icons CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/tabler-icons/tabler-icons.min.css') }}">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/fontawesome/css/all.min.css') }}">
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/feather.css') }}">
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/style.css') }}">

    <style>
        :root {
            --cp-primary: __BLADE_BLOCK_11__;
            --cp-sidebar-bg: __BLADE_BLOCK_12__;
        }
        .cp-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: var(--cp-sidebar-bg);
            color: #cbd5e1;
            z-index: 1040;
            overflow-y: auto;
            transition: transform .3s ease;
        }
        .cp-sidebar .cp-brand {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .cp-sidebar .cp-brand h5 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .cp-sidebar .cp-brand small {
            color: #94a3b8;
            font-size: .75rem;
        }
        .cp-sidebar .cp-nav {
            list-style: none;
            padding: .75rem 0;
            margin: 0;
        }
        .cp-sidebar .cp-nav li a {
            display: flex;
            align-items: center;
            padding: .6rem 1.25rem;
            color: #cbd5e1;
            text-decoration: none;
            font-size: .9rem;
            transition: all .2s;
        }
        .cp-sidebar .cp-nav li a:hover,
        .cp-sidebar .cp-nav li a.active {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .cp-sidebar .cp-nav li a.active {
            border-left: 3px solid var(--cp-primary);
        }
        .cp-sidebar .cp-nav li a i {
            margin-right: .75rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .cp-topbar {
            margin-left: 260px;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .cp-topbar .cp-toggle-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .cp-content {
            margin-left: 260px;
            padding: 1.5rem;
            min-height: calc(100vh - 60px);
            background: #f8fafc;
        }
        .cp-user-info {
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .cp-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--cp-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem;
        }
        @media (max-width: 991.98px) {
            .cp-sidebar { transform: translateX(-100%); }
            .cp-sidebar.show { transform: translateX(0); }
            .cp-topbar { margin-left: 0; }
            .cp-content { margin-left: 0; }
            .cp-topbar .cp-toggle-btn { display: block; }
            .cp-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,.4);
                z-index: 1035;
            }
            .cp-overlay.show { display: block; }
        }
    </style>
</head>
<body>

{{-- Overlay for mobile --}}
<div class="cp-overlay" id="cpOverlay"></div>

{{-- SIDEBAR --}}
<aside class="cp-sidebar" id="cpSidebar">
    <div class="cp-brand">
        <h5>{{ $channel->name ?? __('Canal') }}</h5>
        <small>{{ __('Portail de distribution') }}</small>
    </div>
    <ul class="cp-nav">
        <li>
            <a href="{{ route('eshop360.channel-portal.dashboard', [$slug, $channel->slug ?? $channel->id]) }}"
               class="{{ request()->routeIs('eshop360.channel-portal.dashboard') ? 'active' : '' }}">
                <i class="ti ti-dashboard"></i> Tableau de bord
            </a>
        </li>
        <li>
            <a href="{{ route('eshop360.channel-portal.orders.index', [$slug, $channel->slug ?? $channel->id]) }}"
               class="{{ request()->routeIs('eshop360.channel-portal.orders.*') ? 'active' : '' }}">
                <i class="ti ti-shopping-cart"></i> Commandes
            </a>
        </li>
        <li>
            <a href="{{ route('eshop360.channel-portal.stock.index', [$slug, $channel->slug ?? $channel->id]) }}"
               class="{{ request()->routeIs('eshop360.channel-portal.stock.*') ? 'active' : '' }}">
                <i class="ti ti-package"></i> Stock
            </a>
        </li>
        <li>
            <a href="{{ route('eshop360.channel-portal.sales.index', [$slug, $channel->slug ?? $channel->id]) }}"
               class="{{ request()->routeIs('eshop360.channel-portal.sales.*') ? 'active' : '' }}">
                <i class="ti ti-receipt"></i> Ventes
            </a>
        </li>
        <li>
            <a href="{{ route('eshop360.channel-portal.customers.index', [$slug, $channel->slug ?? $channel->id]) }}"
               class="{{ request()->routeIs('eshop360.channel-portal.customers.*') ? 'active' : '' }}">
                <i class="ti ti-users"></i> Clients
            </a>
        </li>
        <li>
            <a href="{{ route('eshop360.channel-portal.margins.index', [$slug, $channel->slug ?? $channel->id]) }}"
               class="{{ request()->routeIs('eshop360.channel-portal.margins.*') ? 'active' : '' }}">
                <i class="ti ti-chart-bar"></i> Marges
            </a>
        </li>
    </ul>

    {{-- Back to main app --}}
    <div class="mt-auto" style="padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,.08);">
        <a href="{{ route('eshop360.channels.show', [$slug, $channel->id ?? '']) }}" class="text-muted small">
            <i class="ti ti-arrow-left me-1"></i> Retour gestion
        </a>
    </div>
</aside>

{{-- TOPBAR --}}
<div class="cp-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="cp-toggle-btn" id="cpToggleBtn">
            <i class="ti ti-menu-2"></i>
        </button>
        <span class="fw-medium d-none d-md-inline">{{ $channel->name ?? __('Canal') }}</span>
    </div>
    <div class="cp-user-info">
        @include('lang::components.language-switcher')
        @auth
        <div class="dropdown">
            <a href="javascript:void(0);" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                <div class="cp-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                </div>
                <span class="ms-2 d-none d-sm-inline text-dark">{{ auth()->user()->name ?? auth()->user()->email }}</span>
                <i class="ti ti-chevron-down ms-1 text-muted"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <form method="POST" action="{{ route('instance.logout', $slug) }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="ti ti-logout me-2"></i>{{ __('Se deconnecter') }}
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </div>
</div>

{{-- CONTENT --}}
<div class="cp-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<!-- jQuery -->
<script src="__BLADE_BLOCK_40__"></script>
<!-- Feather Icon JS -->
<script src="__BLADE_BLOCK_41__"></script>
<!-- Bootstrap Core JS -->
<script src="__BLADE_BLOCK_42__"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('cpSidebar');
    var overlay = document.getElementById('cpOverlay');
    var toggleBtn = document.getElementById('cpToggleBtn');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }
});
</script>

@yield('scripts')

</body>
</html>
