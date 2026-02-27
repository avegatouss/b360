<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'B360') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('build/img/favicon.png') }}">

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
</head>
<body>

<div class="main-wrapper">

    {{-- ============================================================ --}}
    {{-- HEADER                                                       --}}
    {{-- ============================================================ --}}
    <div class="header">
        <div class="main-header">

            <!-- Logo -->
            <div class="header-left active">
                <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
                   class="logo logo-normal">
                    <img src="{{ asset('build/img/logo.svg') }}" alt="{{ config('app.name', 'B360') }}">
                </a>
                <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
                   class="logo logo-white">
                    <img src="{{ asset('build/img/logo-white.svg') }}" alt="{{ config('app.name', 'B360') }}">
                </a>
                <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
                   class="logo-small">
                    <img src="{{ asset('build/img/logo-small.png') }}" alt="{{ config('app.name', 'B360') }}">
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

                {{-- Instance badge --}}
                @if(isset($instance))
                <li class="nav-item dropdown has-arrow main-drop select-store-dropdown">
                    <a href="javascript:void(0);" class="nav-link select-store">
                        <span class="user-info">
                            <span class="user-detail">
                                <span class="user-name">{{ $instance->name ?? $instance->slug }}</span>
                            </span>
                        </span>
                    </a>
                </li>
                @endif

                <li class="nav-item nav-item-box">
                    <a href="javascript:void(0);" id="btnFullscreen">
                        <i class="ti ti-maximize"></i>
                    </a>
                </li>

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
                        <form method="POST"
                              action="{{ isset($instance) ? route('instance.logout', $instance->slug) : route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="dropdown-item logout pb-0 w-100 text-start border-0 bg-transparent">
                                <i class="ti ti-logout me-2"></i>Se déconnecter
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
                <img src="{{ asset('build/img/logo.svg') }}" alt="{{ config('app.name', 'B360') }}">
            </a>
            <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
               class="logo logo-white">
                <img src="{{ asset('build/img/logo-white.svg') }}" alt="{{ config('app.name', 'B360') }}">
            </a>
            <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}"
               class="logo-small">
                <img src="{{ asset('build/img/logo-small.png') }}" alt="{{ config('app.name', 'B360') }}">
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

                    {{-- Instance navigation --}}
                    @if(isset($instance))
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">{{ $instance->name ?? $instance->slug }}</h6>
                        <ul>
                            <li>
                                <a href="{{ route('dashboard.instance', $instance->slug) }}"
                                   class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                                    <i class="ti ti-layout-grid fs-16 me-2"></i>
                                    <span>Tableau de bord</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('users.index', $instance->slug) }}"
                                   class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="ti ti-users fs-16 me-2"></i>
                                    <span>Utilisateurs</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Administration (ROOT + super-admin uniquement) --}}
                    @if($instance->isRoot() && auth()->user()?->hasRole('super-admin'))
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Administration</h6>
                        <ul>
                            <li>
                                <a href="{{ route('instances.index', $instance->slug) }}"
                                   class="{{ request()->routeIs('instances.*') ? 'active' : '' }}">
                                    <i class="ti ti-building fs-16 me-2"></i>
                                    <span>Instances</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('modules.index', $instance->slug) }}"
                                   class="{{ request()->routeIs('modules.*') ? 'active' : '' }}">
                                    <i class="ti ti-puzzle fs-16 me-2"></i>
                                    <span>Modules</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    @endif

                    {{-- Account section --}}
                    <li class="submenu-open">
                        <h6 class="submenu-hdr">Compte</h6>
                        <ul>
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

<!-- jQuery -->
<script src="{{ asset('build/js/jquery-3.7.1.min.js') }}"></script>
<!-- Feather Icon JS -->
<script src="{{ asset('build/js/feather.min.js') }}"></script>
<!-- Slimscroll JS -->
<script src="{{ asset('build/js/jquery.slimscroll.min.js') }}"></script>
<!-- Bootstrap Core JS -->
<script src="{{ asset('build/js/bootstrap.bundle.min.js') }}"></script>
<!-- Theme JS -->
<script src="{{ asset('build/js/theme-colorpicker.js') }}"></script>
<!-- Custom JS -->
<script src="{{ asset('build/js/script.js') }}"></script>

</body>
</html>
