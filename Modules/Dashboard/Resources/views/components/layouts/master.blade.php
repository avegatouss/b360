<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'B360') }}</title>

    <!-- Favicon -->
    @php $brandFavicon = setting('branding.favicon'); @endphp
    <link rel="shortcut icon" type="image/x-icon" href="{{ $brandFavicon ? asset('storage/' . $brandFavicon) : asset('build/img/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/bootstrap.min.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/select2/css/select2.min.css') }}">

    <!-- Tabler Icons CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('build/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build/plugins/fontawesome/css/all.min.css') }}">

    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/feather.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('build/css/style.css') }}">

    <!-- Theme CSS -->
    @php
        $activeTheme = session('theme', 'default');
        if ($activeTheme === 'default') {
            $activeTheme = null; // no extra CSS needed for default
        }
        if (auth()->check() && function_exists('setting') && !session()->has('theme')) {
            $userTheme = setting('user.theme_' . auth()->id());
            if ($userTheme && $userTheme !== 'default') {
                $activeTheme = $userTheme;
                session(['theme' => $userTheme]);
            }
        }
    @endphp
    @if($activeTheme && file_exists(resource_path("css/themes/{$activeTheme}.css")))
        <style>{!! file_get_contents(resource_path("css/themes/{$activeTheme}.css")) !!}</style>
    @endif
</head>
<body>

<div class="main-wrapper">

    {{-- ============================================================ --}}
    {{-- HEADER                                                       --}}
    {{-- ============================================================ --}}
    <div class="header">
        <div class="main-header">

            <!-- Logo -->
            @php
                $brandLogo = setting('branding.logo');
                $brandLogoDark = setting('branding.logo_dark');
                $brandName = setting('branding.platform_name', config('app.name', 'B360'));
            @endphp
            <div class="header-left active">
                <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
                   class="logo logo-normal">
                    <img src="{{ $brandLogo ? asset('storage/' . $brandLogo) : asset('build/img/logo.svg') }}" alt="{{ $brandName }}">
                </a>
                <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
                   class="logo logo-white">
                    <img src="{{ $brandLogoDark ? asset('storage/' . $brandLogoDark) : asset('build/img/logo-white.svg') }}" alt="{{ $brandName }}">
                </a>
                <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
                   class="logo-small">
                    <img src="{{ $brandLogo ? asset('storage/' . $brandLogo) : asset('build/img/logo-small.png') }}" alt="{{ $brandName }}">
                </a>
            </div>
            <!-- /Logo -->

            <a id="mobile_btn" class="mobile_btn" href="#sidebar">
                <span class="bar-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </a>

            <!-- Header Menu -->
            <ul class="nav user-menu">

                {{-- Instance badge / switcher --}}
                @if(isset($instance))
                @php
                    $isSuperAdmin = \Modules\Core\Support\TeamContext::isSuperAdmin(auth()->user());
                    $switchableInstances = $isSuperAdmin
                        ? \App\Instances\Instance::on('system')->where('is_active', true)->orderBy('name')->get()
                        : collect();
                @endphp
                <li class="nav-item dropdown has-arrow main-drop select-store-dropdown">
                    @if($isSuperAdmin && $switchableInstances->count() > 1)
                    <a href="javascript:void(0);" class="nav-link select-store dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="user-info">
                            <span class="user-detail">
                                <span class="user-name">{{ $instance->name ?? $instance->slug }}</span>
                            </span>
                            <span class="ms-1"><i class="ti ti-chevron-down fs-12"></i></span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="max-height: 300px; overflow-y: auto;">
                        @foreach($switchableInstances as $inst)
                        <a href="{{ route('dashboard.instance', $inst->slug) }}"
                           class="dropdown-item {{ $inst->id === $instance->id ? 'active' : '' }}">
                            <i class="ti ti-building me-2"></i>{{ $inst->name ?? $inst->slug }}
                            @if($inst->slug === 'root')
                                <span class="badge bg-danger ms-2">Root</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                    @else
                    <a href="javascript:void(0);" class="nav-link select-store">
                        <span class="user-info">
                            <span class="user-detail">
                                <span class="user-name">{{ $instance->name ?? $instance->slug }}</span>
                            </span>
                        </span>
                    </a>
                    @endif
                </li>
                @endif

                <li class="nav-item nav-item-box">
                    <a href="javascript:void(0);" id="btnFullscreen">
                        <i class="ti ti-maximize"></i>
                    </a>
                </li>

                {{-- Notification bell --}}
                @auth
                @if(isset($instance))
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                    $latestNotifications = auth()->user()->notifications()->latest()->take(5)->get();
                @endphp
                <li class="nav-item dropdown nav-item-box">
                    <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-bell"></i>
                        <span class="badge rounded-pill bg-danger badge-notification" id="notification-count"
                              style="{{ $unreadCount > 0 ? '' : 'display:none' }}">{{ $unreadCount }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width:360px;max-height:450px;overflow-y:auto;">
                        <div class="d-flex align-items-center justify-content-between p-3 pb-2 border-bottom">
                            <h6 class="fw-semibold mb-0">Notifications</h6>
                            @if($unreadCount > 0)
                            <form method="POST" action="{{ route('eshop360.notifications.mark-all-read', $instance->slug) }}" id="mark-all-read-form">
                                @csrf
                                <a href="javascript:void(0);" class="text-primary fs-12" onclick="document.getElementById('mark-all-read-form').submit();">
                                    Tout marquer comme lu
                                </a>
                            </form>
                            @endif
                        </div>
                        <div class="p-0">
                            @forelse($latestNotifications as $notif)
                            @php
                                $nd = $notif->data;
                                $isUnread = is_null($notif->read_at);
                                $typeBorder = match($nd['type'] ?? 'info') {
                                    'danger'  => 'border-danger',
                                    'warning' => 'border-warning',
                                    'success' => 'border-success',
                                    default   => 'border-info',
                                };
                            @endphp
                            <form method="POST" action="{{ route('eshop360.notifications.mark-read', [$instance->slug, $notif->id]) }}">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-start p-3 {{ $isUnread ? 'bg-light border-start border-3 ' . $typeBorder : '' }}" style="white-space:normal;">
                                    <span class="flex-shrink-0 me-2">
                                        <i class="{{ $nd['icon'] ?? 'ti ti-bell' }} fs-20"></i>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-block fw-semibold fs-13">{{ $nd['title'] ?? 'Notification' }}</span>
                                        <span class="d-block text-muted fs-12 text-truncate" style="max-width:250px;">{{ $nd['message'] ?? '' }}</span>
                                        <span class="d-block text-muted fs-11 mt-1">{{ $notif->created_at->diffForHumans() }}</span>
                                    </span>
                                </button>
                            </form>
                            @empty
                            <div class="text-center py-4">
                                <i class="ti ti-bell-off fs-24 text-muted"></i>
                                <p class="text-muted fs-12 mb-0 mt-1">Aucune notification</p>
                            </div>
                            @endforelse
                        </div>
                        <div class="border-top p-2 text-center">
                            <a href="{{ route('eshop360.notifications.index', $instance->slug) }}" class="text-primary fs-12">
                                Voir toutes les notifications
                            </a>
                        </div>
                    </div>
                </li>
                @endif
                @endauth

                {{-- Language switcher --}}
                @include('lang::components.language-switcher')

                {{-- User dropdown --}}
                <li class="nav-item dropdown has-arrow main-drop profile-nav">
                    <a href="javascript:void(0);" class="nav-link userset" data-bs-toggle="dropdown">
                        <span class="user-info p-0">
                            <span class="user-letter">
                                @auth
                                <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                                      style="width:36px;height:36px;font-size:14px;line-height:1;">
                                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                                </span>
                                @endauth
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu menu-drop-user">
                        @auth
                        <div class="profileset d-flex align-items-center">
                            <div>
                                <h6 class="fw-medium">{{ auth()->user()->name ?? auth()->user()->email }}</h6>
                                <p>{{ auth()->user()->roles->first()?->name ?? 'Utilisateur' }}</p>
                            </div>
                        </div>
                        <hr class="my-2">
                        <form method="POST" action="{{ route('lockscreen.lock') }}" class="d-inline">
                            @csrf
                            <button type="submit"
                                    class="dropdown-item w-100 text-start border-0 bg-transparent">
                                <i class="ti ti-lock me-2"></i>Verrouiller l'ecran
                            </button>
                        </form>
                        <form method="POST"
                              action="{{ isset($instance) ? route('instance.logout', $instance->slug) : route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="dropdown-item logout pb-0 w-100 text-start border-0 bg-transparent">
                                <i class="ti ti-logout me-2"></i>Se deconnecter
                            </button>
                        </form>
                        @endauth
                    </div>
                </li>

            </ul>
            <!-- /Header Menu -->

            <!-- Mobile Menu -->
            <div class="dropdown mobile-user-menu">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @auth
                    <form method="POST"
                          action="{{ isset($instance) ? route('instance.logout', $instance->slug) : route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="dropdown-item w-100 text-start border-0 bg-transparent">
                            Se déconnecter
                        </button>
                    </form>
                    @endauth
                </div>
            </div>
            <!-- /Mobile Menu -->

        </div>
    </div>
    {{-- ============================================================ --}}
    {{-- /HEADER                                                      --}}
    {{-- ============================================================ --}}


    {{-- ============================================================ --}}
    {{-- SIDEBAR                                                      --}}
    {{-- ============================================================ --}}
    <div class="sidebar" id="sidebar">

        <!-- Logo -->
        <div class="sidebar-logo active">
            <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
               class="logo logo-normal">
                <img src="{{ $brandLogo ? asset('storage/' . $brandLogo) : asset('build/img/logo.svg') }}" alt="{{ $brandName }}">
            </a>
            <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
               class="logo logo-white">
                <img src="{{ $brandLogoDark ? asset('storage/' . $brandLogoDark) : asset('build/img/logo-white.svg') }}" alt="{{ $brandName }}">
            </a>
            <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
               class="logo-small">
                <img src="{{ $brandLogo ? asset('storage/' . $brandLogo) : asset('build/img/logo-small.png') }}" alt="{{ $brandName }}">
            </a>
            <a id="toggle_btn" href="javascript:void(0);">
                <i data-feather="chevrons-left" class="feather-16"></i>
            </a>
        </div>
        <!-- /Logo -->

        <!-- Sidebar User Profile -->
        <div class="sidebar-header p-3 pb-0 pt-2">
            <div class="text-center rounded bg-light p-2 mb-4 sidebar-profile d-flex align-items-center">
                @auth
                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold flex-shrink-0"
                     style="width:36px;height:36px;font-size:14px;line-height:1;">
                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                </div>
                <div class="text-start sidebar-profile-info ms-2">
                    <h6 class="fs-12 fw-normal mb-1">
                        {{ auth()->user()->name ?? auth()->user()->email }}
                    </h6>
                    <p class="fs-10">
                        {{ auth()->user()->roles->first()?->name ?? 'Utilisateur' }}
                    </p>
                </div>
                @endauth
            </div>
        </div>
        <!-- /Sidebar User Profile -->

        <div class="sidebar-inner slimscroll">
            <div id="sidebar-menu" class="sidebar-menu">
                <ul>
                    {{-- Dynamic menu from hooks --}}
                    <x-dashboard::sidebar :instance="$instance ?? null" />

                    {{-- Account section (always visible) --}}
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Compte</h6>
                        <ul>
                            <li>
                                <form method="POST"
                                      action="{{ route('lockscreen.lock') }}"
                                      id="sidebar-lock-form">
                                    @csrf
                                    <a href="javascript:void(0);"
                                       onclick="document.getElementById('sidebar-lock-form').submit();">
                                        <i class="ti ti-lock fs-16 me-2"></i>
                                        <span>Verrouiller</span>
                                    </a>
                                </form>
                            </li>
                            <li>
                                <form method="POST"
                                      action="{{ isset($instance) ? route('instance.logout', $instance->slug) : route('logout') }}"
                                      id="sidebar-logout-form">
                                    @csrf
                                    <a href="javascript:void(0);"
                                       onclick="document.getElementById('sidebar-logout-form').submit();">
                                        <i class="ti ti-logout fs-16 me-2"></i>
                                        <span>Se déconnecter</span>
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    {{-- ============================================================ --}}
    {{-- /SIDEBAR                                                     --}}
    {{-- ============================================================ --}}


    {{-- ============================================================ --}}
    {{-- PAGE WRAPPER                                                 --}}
    {{-- ============================================================ --}}
    <div class="page-wrapper">
        <div class="content">

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Page header --}}
            @if(isset($pageTitle))
            <div class="page-header">
                <div class="page-title">
                    <h4>{{ $pageTitle }}</h4>
                    @if(isset($instance))
                        <h6>{{ $instance->name ?? $instance->slug }}</h6>
                    @endif
                </div>
            </div>
            @endif

            {{ $slot }}

        </div>
    </div>
    {{-- ============================================================ --}}
    {{-- /PAGE WRAPPER                                                --}}
    {{-- ============================================================ --}}

</div>
<!-- /Main Wrapper -->
@php
    $scriptVersion = file_exists(public_path('build/js/script.js')) ? filemtime(public_path('build/js/script.js')) : time();
    $themeColorpickerVersion = file_exists(public_path('build/js/theme-colorpicker.js')) ? filemtime(public_path('build/js/theme-colorpicker.js')) : $scriptVersion;
    $select2Version = file_exists(public_path('build/plugins/select2/js/select2.min.js')) ? filemtime(public_path('build/plugins/select2/js/select2.min.js')) : $scriptVersion;
@endphp

<!-- jQuery -->
<script src="{{ asset('build/js/jquery-3.7.1.min.js') }}"></script>
<!-- Feather Icon JS -->
<script src="{{ asset('build/js/feather.min.js') }}"></script>
<!-- Slimscroll JS -->
<script src="{{ asset('build/js/jquery.slimscroll.min.js') }}"></script>
<!-- Bootstrap Core JS -->
<script src="{{ asset('build/js/bootstrap.bundle.min.js') }}"></script>
<!-- Select2 JS -->
<script src="{{ asset('build/plugins/select2/js/select2.min.js') }}?v={{ $select2Version }}"></script>
@include('layout.partials.select2-config')
<!-- Theme JS -->
<script src="{{ asset('build/js/theme-colorpicker.js') }}?v={{ $themeColorpickerVersion }}"></script>
<!-- Custom JS -->
<script src="{{ asset('build/js/script.js') }}?v={{ $scriptVersion }}"></script>

<!-- Notification polling -->
@auth
@if(isset($instance))
<script>
(function() {
    setInterval(function() {
        fetch('/i/{{ $instance->slug }}/notifications/unread-count', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var badge = document.getElementById('notification-count');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? '' : 'none';
            }
        })
        .catch(function() {});
    }, 30000);
})();
</script>
@endif
@endauth

<!-- Auto-lock after inactivity -->
@auth
<script>
(function() {
    let lockTimeout;
    const LOCK_MINUTES = {{ config('auth.auto_lock_minutes', 30) }};
    function resetLockTimer() {
        clearTimeout(lockTimeout);
        if (LOCK_MINUTES > 0) {
            lockTimeout = setTimeout(function() {
                fetch('/lockscreen/lock', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(function() {
                    window.location.href = '/lockscreen';
                });
            }, LOCK_MINUTES * 60 * 1000);
        }
    }
    ['mousemove', 'keypress', 'click', 'scroll'].forEach(function(e) {
        document.addEventListener(e, resetLockTimer);
    });
    resetLockTimer();
})();
</script>
@endauth

</body>
</html>
