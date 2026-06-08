<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title ?? config('app.name', 'B360') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="B360 - Business Management Software">
    <meta name="keywords" content="business management, invoicing, inventory, CRM, accounting, billing, reporting">
    <meta name="author" content="B360 Team">

    <!-- Favicon -->
    @php $brandFavicon = setting('branding.favicon'); @endphp
    <link rel="shortcut icon" type="image/x-icon"
        href="{{ $brandFavicon ? asset('storage/' . $brandFavicon) : asset('assets/img/favicon.png') }}">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-touch-icon.png') }}">

    <!-- Theme Script js -->
    <script src="{{ asset('assets/js/theme-script.js') }}"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Daterangepikcer CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Simplebar CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/simplebar/simplebar.min.css') }}">

    <!-- Iconsax CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/iconsax.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    @stack('styles')

</head>

<body>

    <!-- Begin Wrapper -->
    <div class="main-wrapper">

        <!-- Topbar Start -->
        <div class="header">
            <div class="main-header">

                <!-- Logo -->
                <div class="header-left">
                    <a href="index.html" class="logo">
                        <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo">
                    </a>
                    <a href="index.html" class="dark-logo">
                        <img src="{{ asset('assets/img/logo-white.svg') }}" alt="Logo">
                    </a>
                </div>

                <!-- Sidebar Menu Toggle Button -->
                <a id="mobile_btn" class="mobile_btn" href="#sidebar">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>

                <div class="header-user">
                    <div class="nav user-menu nav-list">
                        <div class="me-auto d-flex align-items-center" id="header-search">

                            <!-- Add -->
                            <div class="dropdown me-3">
                                <a class="btn btn-primary bg-gradient btn-xs btn-icon rounded-circle d-flex align-items-center justify-content-center"
                                    data-bs-toggle="dropdown" href="javascript:void(0);" role="button">
                                    <i class="isax isax-add text-white"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-start p-2">
                                    <li>
                                        <a href="add-invoice.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document-text-1 me-2"></i>Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="expenses.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-money-send me-2"></i>Expense
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-credit-notes.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-money-add me-2"></i>Credit Notes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-debit-notes.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-money-recive me-2"></i>Debit Notes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-purchases-orders.html"
                                            class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document me-2"></i>Purchase Order
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-quotation.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document-download me-2"></i>Quotation
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-delivery-challan.html"
                                            class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document-forward me-2"></i>Delivery Challan
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Breadcrumb -->
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-divide mb-0">
                                    <li class="breadcrumb-item d-flex align-items-center"><a href="index.html"><i
                                                class="isax isax-home-2 me-1"></i>Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                </ol>
                            </nav>

                        </div>

                        <div class="d-flex align-items-center">

                            <!-- Search -->
                            <div class="input-icon-end position-relative me-2">
                                <input type="text" class="form-control" placeholder="Search">
                                <span class="input-icon-addon">
                                    <i class="isax isax-search-normal"></i>
                                </span>
                            </div>
                            <!-- /Search -->

                            <!-- Language Dropdown -->
                            {{-- <div class="nav-item dropdown has-arrow flag-nav me-2">
                                <a class="btn btn-menubar" data-bs-toggle="dropdown" href="javascript:void(0);"
                                    role="button">
                                    <img src="assets/img/flags/us.svg" alt="Language" class="img-fluid">
                                </a>
                                <ul class="dropdown-menu p-2">

                                    <!-- item-->
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <img src="assets/img/flags/us.svg" alt="flag" class="me-2">English
                                        </a>
                                    </li>

                                    <!-- item-->
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <img src="assets/img/flags/de.svg" alt="flag" class="me-2">German
                                        </a>
                                    </li>

                                    <!-- item-->
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <img src="assets/img/flags/fr.svg" alt="flag" class="me-2">French
                                        </a>
                                    </li>

                                    <!-- item-->
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            <img src="assets/img/flags/ae.svg" alt="flag" class="me-2">Arabic
                                        </a>
                                    </li>

                                </ul>
                            </div> --}}

                            @include('lang::components.language-switcher')

                            <!-- Notification -->
                            <div class="notification_item me-2">
                                <a href="#" class="btn btn-menubar position-relative" id="notification_popup"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <i class="isax isax-notification-bing5"></i>
                                    <span class="position-absolute badge bg-success border border-white"></span>
                                </a>
                                <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg"
                                    style="min-height: 300px;">

                                    <div class="p-2 border-bottom">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                            </div>
                                            <div class="col-auto">
                                                <div class="dropdown">
                                                    <a href="#" class="dropdown-toggle drop-arrow-none link-dark"
                                                        data-bs-toggle="dropdown" data-bs-offset="0,15"
                                                        aria-expanded="false">
                                                        <i class="isax isax-setting-2 fs-16 text-body align-middle"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                                class="ti ti-bell-check me-1"></i>Mark as Read</a>
                                                        <!-- item-->
                                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                                class="ti ti-trash me-1"></i>Delete All</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notification Dropdown -->
                                    <div class="notification-body position-relative z-2 rounded-0" data-simplebar>

                                        <!-- Item-->
                                        <div class="dropdown-item notification-item py-2 text-wrap border-bottom"
                                            id="notification-1">
                                            <div class="d-flex">
                                                <div class="me-2 position-relative flex-shrink-0">
                                                    <img src="assets/img/profiles/avatar-05.jpg"
                                                        class="avatar-md rounded-circle" alt="User Img">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 fw-semibold text-dark">John Smith</p>
                                                    <p class="mb-1 text-wrap fs-14">
                                                        A <span class="fw-semibold">new sale</span> has been recorded.
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fs-12"><i class="isax isax-clock me-1"></i>4 min
                                                            ago</span>
                                                        <div
                                                            class="notification-action d-flex align-items-center float-end gap-2">
                                                            <a href="javascript:void(0);"
                                                                class="notification-read rounded-circle bg-info"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Make as Read"
                                                                aria-label="Make as Read"></a>
                                                            <button class="btn rounded-circle text-danger p-0"
                                                                data-dismissible="#notification-1">
                                                                <i class="isax isax-close-circle fs-12"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Item-->
                                        <div class="dropdown-item notification-item py-2 text-wrap border-bottom"
                                            id="notification-2">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm me-2">
                                                        <span
                                                            class="avatar-title bg-soft-info text-info fs-18 rounded-circle">
                                                            D
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 fw-semibold text-dark">Donoghue Susan</p>
                                                    <p class="mb-0 text-wrap fs-14">
                                                        Switched to a lower-tier package
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fs-12"><i class="isax isax-clock me-1"></i>4 min
                                                            ago</span>
                                                        <div
                                                            class="notification-action d-flex align-items-center float-end gap-2">
                                                            <a href="javascript:void(0);"
                                                                class="notification-read rounded-circle bg-info"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Make as Read"
                                                                aria-label="Make as Read"></a>
                                                            <button class="btn rounded-circle text-danger p-0"
                                                                data-dismissible="#notification-2">
                                                                <i class="isax isax-close-circle fs-12"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Item-->
                                        <div class="dropdown-item notification-item py-2 text-wrap border-bottom"
                                            id="notification-3">
                                            <div class="d-flex">
                                                <div class="me-2 position-relative flex-shrink-0">
                                                    <img src="assets/img/profiles/avatar-03.jpg"
                                                        class="avatar-md rounded-circle" alt="User Img">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 fw-semibold text-dark">Robert Fox </p>
                                                    <p class="mb-1 text-wrap fs-14">
                                                        Completed payment for <span class="fw-semibold">#INV00025</span>
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fs-12"><i class="isax isax-clock me-1"></i>4 min
                                                            ago</span>
                                                        <div
                                                            class="notification-action d-flex align-items-center float-end gap-2">
                                                            <a href="javascript:void(0);"
                                                                class="notification-read rounded-circle bg-info"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Make as Read"
                                                                aria-label="Make as Read"></a>
                                                            <button class="btn rounded-circle text-danger p-0"
                                                                data-dismissible="#notification-3">
                                                                <i class="isax isax-close-circle fs-12"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Item-->
                                        <div class="dropdown-item notification-item py-2 text-wrap border-bottom"
                                            id="notification-4">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm me-2">
                                                        <span
                                                            class="avatar-title bg-soft-warning text-warning fs-18 rounded-circle">
                                                            <i class="isax isax-message"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 text-wrap fs-14">You have received <span
                                                            class="fw-semibold">20</span> new messages in the
                                                        conversation</p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fs-12"><i class="isax isax-clock me-1"></i>3 min
                                                            ago</span>
                                                        <div
                                                            class="notification-action d-flex align-items-center float-end gap-2">
                                                            <a href="javascript:void(0);"
                                                                class="notification-read rounded-circle bg-info"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Make as Read"
                                                                aria-label="Make as Read"></a>
                                                            <button class="btn rounded-circle text-danger p-0"
                                                                data-dismissible="#notification-4">
                                                                <i class="isax isax-close-circle fs-12"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Item-->
                                        <div class="dropdown-item notification-item py-2 text-wrap border-bottom"
                                            id="notification-5">
                                            <div class="d-flex">
                                                <div class="me-2 position-relative flex-shrink-0">
                                                    <img src="assets/img/profiles/avatar-17.jpg"
                                                        class="avatar-md rounded-circle" alt="User Img">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 fw-semibold text-dark">Charlotte Brown</p>
                                                    <p class="mb-1 text-wrap fs-14">
                                                        New invoice generated <span class="fw-semibold">
                                                            #INV00028</span>
                                                    </p>
                                                    <div class="mb-1">
                                                        <a class="badge bg-success p-2 py-1 me-1" href="#">Approve</a>
                                                        <a class="badge bg-danger p-2 py-1" href="#">Deny</a>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fs-12"><i class="isax isax-clock me-1"></i>45 min
                                                            ago</span>
                                                        <div
                                                            class="notification-action d-flex align-items-center float-end gap-2">
                                                            <a href="javascript:void(0);"
                                                                class="notification-read rounded-circle bg-info"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Make as Read"
                                                                aria-label="Make as Read"></a>
                                                            <button class="btn rounded-circle text-danger p-0"
                                                                data-dismissible="#notification-5">
                                                                <i class="isax isax-close-circle fs-12"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- View All-->
                                    <div class="p-2 rounded-bottom border-top text-center">
                                        <a href="notifications.html" class="text-center fw-medium fs-14 mb-0">
                                            View All
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <!-- Light/Dark Mode Button -->
                            <div class="me-2 theme-item">
                                <a href="javascript:void(0);" id="dark-mode-toggle"
                                    class="theme-toggle btn btn-menubar">
                                    <i class="isax isax-moon"></i>
                                </a>
                                <a href="javascript:void(0);" id="light-mode-toggle"
                                    class="theme-toggle btn btn-menubar">
                                    <i class="isax isax-sun-1"></i>
                                </a>
                            </div>

                            @auth
                            <!-- User Dropdown -->
                            <div class="dropdown profile-dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <span class="avatar online">
                                        <img src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" alt="Img"
                                            class="img-fluid rounded-circle">
                                    </span>
                                </a>
                                <div class="dropdown-menu p-2">
                                    <div class="d-flex align-items-center bg-light rounded-1 p-2 mb-2">
                                        <span class="avatar avatar-lg me-2">
                                            <img src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" alt="img"
                                                class="rounded-circle">
                                        </span>
                                        <div>
                                            <h6 class="fs-14 fw-medium mb-1">{{ auth()->user()->name }}</h6>
                                            <p class="fs-13">{{ auth()->user()->roles->first()?->name ?? 'Utilisateur'
                                                }}</p>
                                        </div>
                                    </div>

                                    <!-- Item-->
                                    {{-- <a class="dropdown-item d-flex align-items-center"
                                        href="account-settings.html">
                                        <i class="isax isax-profile-circle me-2"></i>Profile Settings
                                    </a> --}}

                                    <!-- Item-->
                                    {{-- <a class="dropdown-item d-flex align-items-center"
                                        href="inventory-report.html">
                                        <i class="isax isax-document-text me-2"></i>Reports
                                    </a> --}}

                                    <!-- Item-->
                                    {{-- <div
                                        class="form-check form-switch form-check-reverse d-flex align-items-center justify-content-between dropdown-item mb-0">
                                        <label class="form-check-label" for="notify"><i
                                                class="isax isax-notification me-2"></i>Notifications</label>
                                        <input class="form-check-input" type="checkbox" role="switch" id="notify">
                                    </div> --}}

                                    {{-- ajouter veroullage --}}

                                    {{-- <div
                                        class="form-check form-switch form-check-reverse d-flex align-items-center justify-content-between dropdown-item mb-0">
                                        <label class="form-check-label" for="notify"><i
                                                class="isax isax-lock me-2"></i>Verouiller</label>

                                    </div> --}}

                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                        onclick="document.getElementById('lock-form').submit();">
                                        <i class="isax isax-lock me-2"></i>Verrouiller l'ecran
                                    </a>

                                    <form action="{{ route('lockscreen.lock') }}" id="lock-form" method="POST">
                                        @csrf
                                    </form>


                                    <hr class="dropdown-divider my-2">

                                    <!-- Item-->
                                    <a class="dropdown-item logout d-flex align-items-center" href="javascript:void(0);"
                                        onclick="document.getElementById('logout-form').submit();">
                                        <i class="isax isax-logout me-2"></i>Se deconnecter
                                    </a>

                                    <form
                                        action="{{ isset($instance) ? route('instance.logout', $instance->slug) : route('logout') }}"
                                        id="logout-form" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                            @endauth
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div class="dropdown mobile-user-menu profile-dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <span class="avatar avatar-md online">
                            <img src="assets/img/profiles/avatar-01.jpg" alt="Img" class="img-fluid rounded-circle">
                        </span>
                    </a>
                    <div class="dropdown-menu p-2 mt-0">
                        <a class="dropdown-item d-flex align-items-center" href="profile.html">
                            <i class="isax isax-profile-circle me-2"></i>Profile Settings
                        </a>
                        <a class="dropdown-item d-flex align-items-center" href="report-preview.html">
                            <i class="isax isax-document-text me-2"></i>Reports
                        </a>
                        <a class="dropdown-item d-flex align-items-center" href="account-settings.html">
                            <i class="isax isax-setting me-2"></i>Settings
                        </a>
                        <a class="dropdown-item logout d-flex align-items-center" href="login.html">
                            <i class="isax isax-logout me-2"></i>Signout
                        </a>
                    </div>
                </div>
                <!-- /Mobile Menu -->

            </div>
        </div>
        <!-- Topbar End -->

        <!-- Sidenav Menu Start -->
        <div class="two-col-sidebar" id="two-col-sidebar">
            <div class="twocol-mini">

                <!-- Add -->
                <div class="dropdown">
                    <a class="btn btn-primary bg-gradient btn-sm btn-icon rounded-circle d-flex align-items-center justify-content-center"
                        data-bs-toggle="dropdown" href="javascript:void(0);" role="button" data-bs-display="static"
                        data-bs-reference="parent">
                        <i class="isax isax-add"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-start">
                        <li>
                            <a href="add-invoice.html" class="dropdown-item d-flex align-items-center">
                                <i class="isax isax-document-text-1 me-2"></i>Invoice
                            </a>
                        </li>
                        <li>
                            <a href="expenses.html" class="dropdown-item d-flex align-items-center">
                                <i class="isax isax-money-send me-2"></i>Expense
                            </a>
                        </li>
                        <li>
                            <a href="add-credit-notes.html" class="dropdown-item d-flex align-items-center">
                                <i class="isax isax-money-add me-2"></i>Credit Notes
                            </a>
                        </li>
                        <li>
                            <a href="add-debit-notes.html" class="dropdown-item d-flex align-items-center">
                                <i class="isax isax-money-recive me-2"></i>Debit Notes
                            </a>
                        </li>
                        <li>
                            <a href="add-purchases-orders.html" class="dropdown-item d-flex align-items-center">
                                <i class="isax isax-document me-2"></i>Purchase Order
                            </a>
                        </li>
                        <li>
                            <a href="add-quotation.html" class="dropdown-item d-flex align-items-center">
                                <i class="isax isax-document-download me-2"></i>Quotation
                            </a>
                        </li>
                        <li>
                            <a href="add-delivery-challan.html" class="dropdown-item d-flex align-items-center">
                                <i class="isax isax-document-forward me-2"></i>Delivery Challan
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- /Add -->

                <ul class="menu-list">
                    <li>
                        <a href="account-settings.html" data-bs-toggle="tooltip" data-bs-placement="right"
                            data-bs-title="Settings"><i class="isax isax-setting-25"></i></a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                            data-bs-title="Documentation"><i class="isax isax-document-normal4"></i></a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                            data-bs-title="Changelog"><i class="isax isax-cloud-change5"></i></a>
                    </li>
                    <li>
                        <a href="#"><i class="isax isax-login-15"></i></a>
                    </li>
                </ul>
            </div>

            <div class="sidebar" id="sidebar-two">

                <!-- Start Logo -->
                <div class="sidebar-logo">
                    <a href="index.html" class="logo logo-normal">
                        <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo">
                    </a>
                    <a href="index.html" class="logo-small">
                        <img src="{{ asset('assets/img/logo-small.svg') }}" alt="Logo">
                    </a>
                    <a href="index.html" class="dark-logo">
                        <img src="{{ asset('assets/img/logo-white.svg') }}" alt="Logo">
                    </a>
                    <a href="index.html" class="dark-small">
                        <img src="{{ asset('assets/img/logo-small-white.svg') }}" alt="Logo">
                    </a>

                    <!-- Sidebar Hover Menu Toggle Button -->
                    <a id="toggle_btn" href="javascript:void(0);">
                        <i class="isax isax-menu-1"></i>
                    </a>
                </div>
                <!-- End Logo -->

                <!-- Search -->
                <div class="sidebar-search">
                    <div class="input-icon-end position-relative">
                        <input type="text" class="form-control" placeholder="Search">
                        <span class="input-icon-addon">
                            <i class="isax isax-search-normal"></i>
                        </span>
                    </div>
                </div>
                <!-- /Search -->

                <!--- Sidebar Menu -->
                {{-- <div class="sidebar-inner" data-simplebar>
                    <div id="sidebar-menu" class="sidebar-menu">
                        <ul>
                            <li class="menu-title"><span>Main</span></li>
                            <li>
                                <ul>
                                    <li class="submenu">
                                        <a href="javascript:void(0);" class="active subdrop">
                                            <i class="isax isax-element-45"></i><span>Dashboard</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            <li><a href="index.html" class="active">Admin Dashboard</a></li>
                                            <li><a href="admin-dashboard-2.html">Admin Dashboard 2</a></li>
                                            <li><a href="admin-dashboard-3.html">Admin Dashboard 3</a></li>
                                            <li><a href="financial-overview-dashboard.html">Financial Overview</a></li>
                                            <li><a href="sales-analytics.html">Sales Analytics</a></li>
                                            <li><a href="customer-dashboard.html">Customer Dashboard</a></li>
                                            <li><a href="super-admin-dashboard.html">Super Admin</a></li>
                                        </ul>
                                    </li>
                                    <li class="submenu">
                                        <a href="javascript:void(0);">
                                            <i class="isax isax-shapes5"></i><span>Super Admin</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            <li><a href="super-admin-dashboard.html">Dashboard</a></li>
                                            <li><a href="companies.html">Companies</a></li>
                                            <li><a href="subscriptions.html">Subscriptions</a></li>
                                            <li><a href="packages.html">Packages</a></li>
                                            <li><a href="domain.html">Domain</a></li>
                                            <li><a href="purchase-transaction.html">Purchase Transaction</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="sass-landing/index.html">
                                            <i class="isax isax-note-215"></i><span>Frontend</span>
                                        </a>
                                    </li>
                                    <li class="submenu">
                                        <a href="javascript:void(0);">
                                            <i class="isax isax-category-25"></i><span>Applications</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <ul>
                                            <li><a href="chat.html">Chat</a></li>
                                            <li class="submenu submenu-two">
                                                <a href="voice-call.html">Calls<span
                                                        class="menu-arrow inside-submenu"></span></a>
                                                <ul>
                                                    <li><a href="voice-call.html">Voice Call</a></li>
                                                    <li><a href="video-call.html">Video Call</a></li>
                                                    <li><a href="outgoing-call.html">Outgoing Call</a></li>
                                                    <li><a href="incoming-call.html">Incoming Call</a></li>
                                                    <li><a href="call-history.html">Call History</a></li>
                                                </ul>
                                            </li>
                                            <li><a href="calendar.html">Calendar</a></li>
                                            <li><a href="email.html">Email</a></li>
                                            <li><a href="todo.html">To Do</a></li>
                                            <li><a href="notes.html">Notes</a></li>
                                            <li><a href="social-feed.html">Social Feed</a></li>
                                            <li><a href="file-manager.html">File Manager</a></li>
                                            <li><a href="kanban-view.html">Kanban</a></li>
                                            <li><a href="contacts.html">Contacts</a></li>
                                            <li><a href="invoice.html">Invoices</a></li>
                                            <li><a href="search-list.html">Search List</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <div class="sidebar-footer">
                            <div class="trial-item bg-white text-center border">
                                <div class="bg-light p-3 text-center upgrade-image">
                                    <img src="assets/img/icons/upgrade2.svg" alt="img">
                                </div>
                                <div class="p-2">
                                    <h6 class="fs-14 fw-semibold mb-1">Upgrade to More</h6>
                                    <p class="fs-13 mb-2">Subscribe to get more with Premium Features</p>
                                    <a href="plans-billings.html"
                                        class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center"><i
                                            class="isax isax-crown5 me-1"></i>Upgrade</a>
                                </div>
                                <a href="javascript:void(0);" class="close-icon fs-6"><i class="fa-solid fa-x"></i></a>
                            </div>
                            <ul class="menu-list">
                                <li>
                                    <a href="account-settings.html" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Settings"><i class="isax isax-setting-25"></i></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Documentation"><i class="isax isax-document-normal4"></i></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Changelog"><i class="isax isax-cloud-change5"></i></a>
                                </li>
                                <li>
                                    <a href="login.html" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Login"><i class="isax isax-login-15"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> --}}
                <x-dashboard::sidebar :instance="$instance ?? null" />
                <!--- Sidebar Menu -->
            </div>
        </div>
        <!-- Sidenav Menu End -->

        <!-- Horizontal Menu -->
        {{-- <div class="sidebar sidebar-horizontal" id="horizontal-menu">
            <div class="sidebar-menu">
                <div class="main-menu">
                    <ul class="nav-menu">
                        <li class="menu-title">
                            <span>Main</span>
                        </li>
                        <li class="submenu">
                            <a href="#" class="active">
                                <i class="isax isax-menu"></i><span>Main</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="index.html" class="active">Admin Dashboard</a></li>
                                <li><a href="admin-dashboard-2.html">Admin Dashboard 2</a></li>
                                <li><a href="admin-dashboard-3.html">Admin Dashboard 3</a></li>
                                <li><a href="financial-overview-dashboard.html">Financial Overview</a></li>
                                <li><a href="sales-analytics.html">Sales Analytics</a></li>
                                <li><a href="customer-dashboard.html">Customer Dashboard</a></li>
                                <li><a href="super-admin-dashboard.html">Super Admin</a></li>
                            </ul>
                        </li>
                        <li class="submenu submenu-two">
                            <a href="javascript:void(0);">
                                <i class="isax isax-fatrows5"></i><span>Layout</span>
                                <span class="menu-arrow inside-submenu"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="layout-default.html">
                                        <i class="isax isax-row-horizontal5"></i><span>Default</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="layout-single.html">
                                        <i class="isax isax-grid-95"></i><span>Single</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="layout-mini.html">
                                        <i class="isax isax-grid-45"></i><span>Mini</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="layout-horizontal.html">
                                        <i class="isax isax-grid-75"></i><span>Horizontal</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="layout-transparent.html">
                                        <i class="isax isax-grid-25"></i><span>Transparent</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="layout-without-header.html">
                                        <i class="isax isax-slider-vertical-15"></i><span>Without Header</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="layout-rtl.html">
                                        <i class="isax isax-sidebar-left5"></i><span>RTL</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="layout-dark.html">
                                        <i class="isax isax-moon5"></i><span>Dark</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#">
                                <i class="isax isax-money-recive5"></i><span>Sales & Invoicing</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-profile-2user5"></i><span>Customers</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="customers.html">Customers</a></li>
                                        <li><a href="customer-details.html">Customer Details</a></li>
                                        <li><a href="customer-statements.html">Customer Statements</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="quotations.html">
                                        <i class="isax isax-strongbox5"></i><span>Quotations</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="proforma-invoices.html">
                                        <i class="isax isax-lifebuoy5"></i><span>Proforma Invoices</span>
                                    </a>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-receipt-item5"></i><span>Invoices</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="invoices.html">Invoices</a></li>
                                        <li><a href="add-invoice.html">Create Invoice</a></li>
                                        <li><a href="invoice-details.html">Invoice Details</a></li>
                                        <li><a href="invoice-templates.html">Invoice Templates</a></li>
                                        <li><a href="recurring-invoices.html">Recurring Invoices</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="delivery-challans.html">
                                        <i class="isax isax-bookmark-25"></i><span>Delivery Challans</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="credit-notes.html">
                                        <i class="isax isax-note5"></i><span>Credit Notes</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#">
                                <i class="isax isax-box5"></i><span>Inventory & Purchases</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-box5"></i><span>Product / Services</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="products.html">Products</a></li>
                                        <li><a href="services.html">Services</a></li>
                                        <li><a href="category.html">Categories</a></li>
                                        <li><a href="units.html">Units</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="inventory.html">
                                        <i class="isax isax-lifebuoy5"></i><span>Inventory</span>
                                    </a>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-profile-2user5"></i><span>Vendors</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="suppliers.html">Suppliers</a></li>
                                        <li><a href="bills.html">Bills</a></li>
                                        <li><a href="supplier-payments.html">Bill Payments</a></li>
                                    </ul>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-bag-tick-25"></i><span>Purchases</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="purchase-orders.html">Purchase Orders</a></li>
                                        <li><a href="purchases.html">Purchases</a></li>
                                        <li><a href="debit-notes.html">Debit Notes</a></li>
                                    </ul>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-main-component5"></i><span>Manage / Operations</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-profile-2user5"></i><span>Manage Users</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="users.html">Users</a></li>
                                                <li><a href="roles-permissions.html">Roles & Permissions</a></li>
                                                <li><a href="delete-account-request.html">Delete Account Request</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="contact-messages.html">
                                                <i class="isax isax-messages-25"></i><span>Contact Messages</span>
                                            </a>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-ticket-25"></i><span>Tickets</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="tickets.html">Ticket Lists</a></li>
                                                <li><a href="ticket-kanban.html">Ticket Kanban</a></li>
                                                <li><a href="ticket-details.html">Ticket Details</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="audit-log.html">
                                                <i class="isax isax-messages-25"></i><span>Audit Logs</span>
                                            </a>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-star-15"></i><span>Membership</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="membership-plans.html">Membership Plans</a></li>
                                                <li><a href="membership-addons.html">Membership Addons</a></li>
                                                <li><a href="subscribers.html">Subscribers</a></li>
                                                <li><a href="membership-transactions.html">Transactions</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#">
                                <i class="isax isax-document-text5"></i><span>Finance & Accounts</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-money-send5"></i><span>Expenses</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="expenses.html">All Expenses</a></li>
                                        <li><a href="expense-categories.html">Expense Categories</a></li>
                                        <li><a href="recurring-expenses.html">Recurring Expenses</a></li>
                                        <li><a href="expenses-vendor.html">Vendor Expenses</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="incomes.html">
                                        <i class="isax isax-money-recive5"></i><span>Incomes</span>
                                    </a>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-money-tick5"></i><span>Payments</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="payments.html">Payments Received</a></li>
                                        <li><a href="online-payments.html">Online Payments</a></li>
                                        <li><a href="refunds.html">Refunds</a></li>
                                    </ul>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-card-tick-15"></i><span>Banking</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="bank-accounts.html">Bank Accounts</a></li>
                                        <li><a href="bank-transactions.html">Bank Transactions</a></li>
                                        <li><a href="bank-reconciliation.html">Bank Reconciliation</a></li>
                                        <li><a href="money-transfer.html">Money Transfer</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="transactions.html">
                                        <i class="isax isax-moneys5"></i><span>Transactions</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#">
                                <i class="isax isax-tag-user5"></i><span>Administration</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-chart-35"></i><span>Reports</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Sales Reports<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="sales-report.html">Sales</a></li>
                                                <li><a href="sales-orders.html">Sales Orders</a></li>
                                                <li><a href="sales-returns.html">Sales Return</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Purchase Reports<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="purchases-report.html">Purchases</a></li>
                                                <li><a href="purchase-orders-report.html">Purchase Orders</a></li>
                                                <li><a href="purchase-return-report.html">Purchase Returns</a></li>
                                                <li><a href="quotation-report.html">Quotation</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Inventory Reports<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="inventory-report.html">Inventory Reports</a></li>
                                                <li><a href="low-stock.html">Low Stock</a></li>
                                                <li><a href="stock-history.html">Stock History</a></li>
                                                <li><a href="stock-summary.html">Stock Summary</a></li>
                                                <li><a href="best-seller.html">Best Seller</a></li>
                                                <li><a href="sold-stock.html">Sold Stock</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Finance Reports<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="payment-summary.html">Payment Summary</a></li>
                                                <li><a href="tax-report.html">Taxes</a></li>
                                                <li><a href="expense-report.html">Expense Reports</a></li>
                                                <li><a href="income-report.html">Income Reports</a></li>
                                                <li><a href="profit-loss-report.html">Profit & Loss</a></li>
                                                <li><a href="balance-sheet.html">Balance Sheet</a></li>
                                                <li><a href="trial-balance.html">Trial Balance</a></li>
                                                <li><a href="cash-flow.html">Cash Flow</a></li>
                                                <li><a href="account-statement.html">Account Statement</a></li>
                                                <li><a href="annual-report.html">Annual Report</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">User Reports<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li>
                                                    <a href="customers-report.html">Customers</a>
                                                </li>
                                                <li>
                                                    <a href="customer-due-report.html">Customer Due Report</a>
                                                </li>
                                                <li>
                                                    <a href="supplier-report.html">Supplier</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Customer Reports<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li>
                                                    <a href="report-builder.html">Report Builder</a>
                                                </li>
                                                <li>
                                                    <a href="saved-reports.html">Saved Reports</a>
                                                </li>
                                            </ul>
                                        </li>

                                    </ul>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-setting-25"></i><span>Settings</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">General Settings<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="company-settings.html">Company Settings</a></li>
                                                <li><a href="account-settings.html">Account Settings</a></li>
                                                <li><a href="plans-billings.html">Plans & Billing</a></li>
                                                <li><a href="notifications-settings.html">Notifications</a></li>
                                                <li><a href="integrations-settings.html">Integrations</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Website Settings<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="localization-settings.html">Localization</a></li>
                                                <li><a href="prefixes-settings.html">Prefixes</a></li>
                                                <li><a href="preference-settings.html">Preference</a></li>
                                                <li><a href="seo-setup.html">SEO Setup</a></li>
                                                <li><a href="language-settings.html">Language</a></li>
                                                <li><a href="maintenance-mode.html">Maintenance Mode</a></li>
                                                <li><a href="authentication-settings.html">Authentication</a></li>
                                                <li><a href="ai-configuration.html">AI Configuration</a></li>
                                                <li><a href="appearance-settings.html">Appearance</a></li>
                                                <li><a href="plugin-manager.html">Plugin Manager</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">App Settings<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="invoice-settings.html">Invoice Settings</a></li>
                                                <li><a href="invoice-templates-settings.html">Invoice Templates</a></li>
                                                <li><a href="esignatures.html">eSignatures</a></li>
                                                <li><a href="barcode-settings.html">Barcode</a></li>
                                                <li><a href="thermal-printer.html">Thermal Printer</a></li>
                                                <li><a href="custom-fields.html">Custom Fields</a></li>
                                                <li><a href="sass-settings.html">SaaS Settings</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">System Settings<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li><a href="email-settings.html">Email Settings</a></li>
                                                <li><a href="email-templates.html">Email Templates</a></li>
                                                <li><a href="sms-gateways.html">SMS Gateways</a></li>
                                                <li><a href="gdpr-cookies.html">GDPR Cookies</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Finance Settings<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li>
                                                    <a href="payment-methods.html">Payment Methods</a>
                                                </li>
                                                <li>
                                                    <a href="bank-accounts.html">Bank Accounts</a>
                                                </li>
                                                <li>
                                                    <a href="tax-rates.html">Tax Rates</a>
                                                </li>
                                                <li>
                                                    <a href="tax-groups.html">Tax Groups</a>
                                                </li>
                                                <li>
                                                    <a href="currencies.html">Currencies</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="submenu submenu-two">
                                            <a href="javascript:void(0);">Other Settings<span
                                                    class="menu-arrow"></span></a>
                                            <ul>
                                                <li>
                                                    <a href="custom-css.html">Custom CSS</a>
                                                </li>
                                                <li>
                                                    <a href="custom-js.html">Custom JS</a>
                                                </li>
                                                <li>
                                                    <a href="sitemap.html">Sitemap</a>
                                                </li>
                                                <li>
                                                    <a href="api-keys.html">API Keys</a>
                                                </li>
                                                <li>
                                                    <a href="webhooks.html">Webhooks</a>
                                                </li>
                                                <li>
                                                    <a href="storage.html">Storage</a>
                                                </li>
                                                <li>
                                                    <a href="cronjob.html">Cronjob</a>
                                                </li>
                                                <li>
                                                    <a href="clear-cache.html">Clear Cache</a>
                                                </li>
                                                <li>
                                                    <a href="system-backup.html">System Backup</a>
                                                </li>
                                                <li>
                                                    <a href="database-backup.html">Database Backup</a>
                                                </li>
                                                <li>
                                                    <a href="system-update.html">System Update</a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="submenu submenu-three">
                            <a href="#">
                                <i class="isax isax-menu-15"></i><span>CMS</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-book5"></i><span>Content</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li>
                                            <a href="pages.html">
                                                <i class="isax isax-archive-book5"></i><span>Pages</span>
                                            </a>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-book5"></i><span>Blogs</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="blogs-list.html">Blog List</a></li>
                                                <li><a href="blogs.html">Blog Grid</a></li>
                                                <li><a href="blog-categories.html">Categories</a></li>
                                                <li><a href="blog-tags.html">Tags</a></li>
                                                <li><a href="blog-comments.html">Comments</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-map5"></i><span>Locations</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="countries.html">Countries</a></li>
                                                <li><a href="states.html">States</a></li>
                                                <li><a href="cities.html">Cities</a></li>
                                            </ul>
                                        </li>
                                    </ul>

                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-document-15"></i><span>Pages</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li>
                                            <a href="profile.html">
                                                <i class="isax isax-profile-tick5"></i><span>Profile</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="starter.html">
                                                <i class="isax isax-document-favorite5"></i><span>Starter Page</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="gallery.html">
                                                <i class="isax isax-image5"></i><span>Gallery</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="testimonials.html">
                                                <i class="isax isax-messages-15"></i><span>Testimonials</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="faq.html">
                                                <i class="isax isax-message-question5"></i><span>FAQ’S</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="pricing.html">
                                                <i class="isax isax-money-45"></i><span>Pricing</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="timeline.html">
                                                <i class="isax isax-timer-pause5"></i><span>Timeline</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="coming-soon.html">
                                                <i class="isax isax-security-time5"></i><span>Coming Soon</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="under-maintenance.html">
                                                <i class="isax isax-paintbucket5"></i><span>Under Maintenance</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="under-construction.html">
                                                <i class="isax isax-forward-item5"></i><span>Under Construction</span>
                                            </a>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-document-text5"></i><span>Legal Pages</span><span
                                                    class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="privacy-policy.html">Privacy Policy</a></li>
                                                <li><a href="terms-condition.html">Terms & Conditions</a></li>
                                                <li><a href="refund-policy.html">Refund Policy</a></li>
                                                <li><a href="cookie-policy.html">Cookie Policy</a></li>
                                                <li><a href="license-agreement.html">License Agreement</a></li>
                                                <li><a href="compliance-notices.html">Compliance Notices</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-device-message5"></i><span>Error Pages</span><span
                                                    class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="error-404.html">Error 404</a></li>
                                                <li><a href="error-500.html">Error 500</a></li>
                                            </ul>
                                        </li>
                                    </ul>

                                </li>
                            </ul>

                        </li>
                        <li class="submenu submenu-three">
                            <a href="#">
                                <i class="isax isax-color-swatch5"></i><span>Others</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-chart-35"></i><span>Authentication</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li>
                                            <a href="login.html">
                                                <i class="isax isax-login-15"></i><span>Login</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="register.html">
                                                <i class="isax isax-lock-15"></i><span>Register</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="forgot-password.html">
                                                <i class="isax isax-password-check5"></i><span>Forgot Password</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="reset-password.html">
                                                <i class="isax isax-refresh-right-square5"></i><span>Reset
                                                    Password</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="email-verification.html">
                                                <i class="isax isax-sms-tracking5"></i><span>Email Verification</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="two-step-verification.html">
                                                <i class="isax isax-security5"></i><span>2 Step Verification</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="lock-screen.html">
                                                <i class="isax isax-lock-circle5"></i><span>Lock Screen</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-buildings5"></i><span>UI Pages</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-designtools5"></i><span>Base UI</span><span
                                                    class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="ui-accordion.html">Accordion</a></li>
                                                <li><a href="ui-alerts.html">Alerts</a></li>
                                                <li><a href="ui-avatar.html">Avatar</a></li>
                                                <li><a href="ui-badges.html">Badges</a></li>
                                                <li><a href="ui-breadcrumb.html">Breadcrumb</a></li>
                                                <li><a href="ui-buttons.html">Buttons</a></li>
                                                <li><a href="ui-buttons-group.html">Button Group</a></li>
                                                <li><a href="ui-cards.html">Card</a></li>
                                                <li><a href="ui-carousel.html">Carousel</a></li>
                                                <li><a href="ui-collapse.html">Collapse</a></li>
                                                <li><a href="ui-dropdowns.html">Dropdowns</a></li>
                                                <li><a href="ui-ratio.html">Ratio</a></li>
                                                <li><a href="ui-grid.html">Grid</a></li>
                                                <li><a href="ui-images.html">Images</a></li>
                                                <li><a href="ui-links.html">Links</a></li>
                                                <li><a href="ui-list-group.html">List Group</a></li>
                                                <li><a href="ui-modals.html">Modals</a></li>
                                                <li><a href="ui-offcanvas.html">Offcanvas</a></li>
                                                <li><a href="ui-pagination.html">Pagination</a></li>
                                                <li><a href="ui-placeholders.html">Placeholders</a></li>
                                                <li><a href="ui-popovers.html">Popovers</a></li>
                                                <li><a href="ui-progress.html">Progress</a></li>
                                                <li><a href="ui-scrollspy.html">Scrollspy</a></li>
                                                <li><a href="ui-spinner.html">Spinner</a></li>
                                                <li><a href="ui-nav-tabs.html">Tabs</a></li>
                                                <li><a href="ui-toasts.html">Toasts</a></li>
                                                <li><a href="ui-tooltips.html">Tooltips</a></li>
                                                <li><a href="ui-typography.html">Typography</a></li>
                                                <li><a href="ui-utilities.html">Utilities</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-pen-tool5"></i><span>Advanced UI</span><span
                                                    class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="extended-dragula.html">Dragula</a></li>
                                                <li><a href="ui-clipboard.html">Clipboard</a></li>
                                                <li><a href="ui-rangeslider.html">Range Slider</a></li>
                                                <li><a href="ui-sweetalerts.html">Sweet Alerts</a></li>
                                                <li><a href="ui-lightbox.html">Lightbox</a></li>
                                                <li><a href="ui-counter.html">Counter</a></li>
                                                <li><a href="ui-scrollbar.html">Scrollbar</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-password-check5"></i><span>Forms</span><span
                                                    class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li class="submenu submenu-two">
                                                    <a href="javascript:void(0);">Form Elements<span
                                                            class="menu-arrow inside-submenu"></span></a>
                                                    <ul>
                                                        <li><a href="form-basic-inputs.html">Basic Inputs</a></li>
                                                        <li><a href="form-checkbox-radios.html">Checkbox & Radios</a>
                                                        </li>
                                                        <li><a href="form-input-groups.html">Input Groups</a></li>
                                                        <li><a href="form-grid-gutters.html">Grid & Gutters</a></li>
                                                        <li><a href="form-mask.html">Input Masks</a></li>
                                                        <li><a href="form-fileupload.html">File Uploads</a></li>
                                                        <li><a href="form-elements.html">Form Elements</a></li>
                                                    </ul>
                                                </li>
                                                <li class="submenu submenu-two">
                                                    <a href="javascript:void(0);">Layouts<span
                                                            class="menu-arrow inside-submenu"></span></a>
                                                    <ul>
                                                        <li><a href="form-horizontal.html">Horizontal Form</a></li>
                                                        <li><a href="form-vertical.html">Vertical Form</a></li>
                                                        <li><a href="form-floating-labels.html">Floating Labels</a></li>
                                                    </ul>
                                                </li>
                                                <li><a href="form-validation.html">Form Validation</a></li>
                                                <li><a href="form-select2.html">Select2</a></li>
                                                <li><a href="form-wizard.html">Form Wizard</a></li>
                                                <li><a href="form-pickers.html">Form Picker</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-grid-75"></i><span>Tables</span><span
                                                    class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="tables-basic.html">Basic Tables </a></li>
                                                <li><a href="data-tables.html">Data Table </a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-designtools5"></i>
                                                <span>Charts</span><span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="chart-apex.html">Apex Charts</a></li>
                                                <li><a href="chart-c3.html">Chart C3</a></li>
                                                <li><a href="chart-js.html">Chart Js</a></li>
                                                <li><a href="chart-morris.html">Morris Charts</a></li>
                                                <li><a href="chart-flot.html">Flot Charts</a></li>
                                                <li><a href="chart-peity.html">Peity Charts</a></li>
                                            </ul>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-shapes-15"></i>
                                                <span>Icons</span><span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="icon-fontawesome.html">Fontawesome Icons</a></li>
                                                <li><a href="icon-tabler.html">Tabler Icons</a></li>
                                                <li><a href="icon-bootstrap.html">Bootstrap Icons</a></li>
                                                <li><a href="icon-remix.html">Remix Icons</a></li>
                                                <li><a href="icon-feather.html">Feather Icons</a></li>
                                                <li><a href="icon-ionic.html">Ionic Icons</a></li>
                                                <li><a href="icon-material.html">Material Icons</a></li>
                                                <li><a href="icon-pe7.html">Pe7 Icons</a></li>
                                                <li><a href="icon-simpleline.html">Simpleline Icons</a></li>
                                                <li><a href="icon-themify.html">Themify Icons</a></li>
                                                <li><a href="icon-weather.html">Weather Icons</a></li>
                                                <li><a href="icon-typicon.html">Typicon Icons</a></li>
                                                <li><a href="icon-flag.html">Flag Icons</a></li>
                                            </ul>
                                        </li>

                                    </ul>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);">
                                        <i class="isax isax-setting-25"></i><span>Help</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li>
                                            <a href="https://kanakku.dreamstechnologies.com/documentation/html.html"><i
                                                    class="isax isax-document-code-25"></i><span>Documentation</span></a>
                                        </li>
                                        <li>
                                            <a
                                                href="https://kanakku.dreamstechnologies.com/documentation/changelog.html"><i
                                                    class="isax isax-programming-arrows5"></i><span>Changelog</span><span
                                                    class="badge bg-primary ms-2 badge-sm text-white fs-12 fw-medium">v2.1.3</span></a>
                                        </li>
                                        <li class="submenu">
                                            <a href="javascript:void(0);">
                                                <i class="isax isax-layer5"></i><span>Multi Level</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <ul>
                                                <li><a href="javascript:void(0);">Multilevel 1</a></li>
                                                <li class="submenu submenu-two">
                                                    <a href="javascript:void(0);">Multilevel 2<span
                                                            class="menu-arrow inside-submenu"></span></a>
                                                    <ul>
                                                        <li><a href="javascript:void(0);">Multilevel 2.1</a></li>
                                                        <li class="submenu submenu-two submenu-three">
                                                            <a href="javascript:void(0);">Multilevel 2.2<span
                                                                    class="menu-arrow inside-submenu inside-submenu-two"></span></a>
                                                            <ul>
                                                                <li><a href="javascript:void(0);">Multilevel 2.2.1</a>
                                                                </li>
                                                                <li><a href="javascript:void(0);">Multilevel 2.2.2</a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li><a href="javascript:void(0);">Multilevel 3</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div> --}}
        <!-- /Horizontal Menu -->

        <!-- ========================
			Start Page Content
		========================= -->

        <div class="page-wrapper">

            <!-- Start Content -->
            <div class="content">

                <!-- Start Breadcrumb -->
                {{-- <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <div>
                        <h6>Dashboard</h6>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-1">
                            <p class="fs-12 fw-medium text-dark mb-0">Customers</p>
                            <div class="avatar-list-stacked avatar-group-sm">
                                <span class="avatar avatar-rounded">
                                    <img class="border border-white" src="assets/img/profiles/avatar-14.jpg" alt="img">
                                </span>
                                <span class="avatar avatar-rounded">
                                    <img class="border border-white" src="assets/img/profiles/avatar-39.jpg" alt="img">
                                </span>
                                <span class="avatar avatar-rounded">
                                    <img class="border border-white" src="assets/img/profiles/avatar-36.jpg" alt="img">
                                </span>
                                <span class="avatar avatar-rounded">
                                    <img class="border border-white" src="assets/img/profiles/avatar-35.jpg" alt="img">
                                </span>
                                <a class="avatar bg-dark text-white fs-16 avatar-rounded" href="javascript:void(0);">
                                    +
                                </a>
                            </div>
                        </div>
                        <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                            <span class="reportrange-picker-field text-dark">16 Apr 25 - 16 Apr 25</span>
                            <i class="isax isax-calendar text-dark fs-14 ms-2"></i>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="btn btn-outline-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="isax isax-export-1 me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Download as PDF</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Download as Excel</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a class="btn btn-primary d-flex align-items-center justify-content-center dropdown-toggle"
                                data-bs-toggle="dropdown" href="javascript:void(0);" role="button">
                                Create New
                            </a>
                            <ul class="dropdown-menu dropdown-menu-start">
                                <li>
                                    <a href="add-invoice.html" class="dropdown-item d-flex align-items-center">
                                        <i class="isax isax-document-text-1 me-2"></i>Invoice
                                    </a>
                                </li>
                                <li>
                                    <a href="expenses.html" class="dropdown-item d-flex align-items-center">
                                        <i class="isax isax-money-send me-2"></i>Expense
                                    </a>
                                </li>
                                <li>
                                    <a href="add-credit-notes.html" class="dropdown-item d-flex align-items-center">
                                        <i class="isax isax-money-add me-2"></i>Credit Notes
                                    </a>
                                </li>
                                <li>
                                    <a href="add-debit-notes.html" class="dropdown-item d-flex align-items-center">
                                        <i class="isax isax-money-recive me-2"></i>Debit Notes
                                    </a>
                                </li>
                                <li>
                                    <a href="add-purchases-orders.html" class="dropdown-item d-flex align-items-center">
                                        <i class="isax isax-document me-2"></i>Purchase Order
                                    </a>
                                </li>
                                <li>
                                    <a href="add-quotation.html" class="dropdown-item d-flex align-items-center">
                                        <i class="isax isax-document-download me-2"></i>Quotation
                                    </a>
                                </li>
                                <li>
                                    <a href="add-delivery-challan.html" class="dropdown-item d-flex align-items-center">
                                        <i class="isax isax-document-forward me-2"></i>Delivery Challan
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> --}}
                <!-- End Breadcrumb -->

                <!-- start row -->

                <!-- end row -->

                {{ $slot }}

            </div>
            <!-- End Content -->

            <!-- Start Footer -->
            <div class="footer d-sm-flex align-items-center justify-content-between bg-white py-2 px-4">
                <p class="text-dark mb-0">&copy; {{ date('Y') }} <a href="javascript:void(0);"
                        class="link-primary">Kanakku</a>,
                    All Rights Reserved</p>
                <p class="text-dark">Version : v2.1.3</p>
            </div>
            <!-- End Footer -->

        </div>

        <!-- ========================
			End Page Content
		========================= -->

    </div>
    <!-- End Wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Daterangepikcer JS -->
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>

    <!-- Simplebar JS -->
    <script src="{{ asset('assets/plugins/simplebar/simplebar.min.js') }}"></script>

    <!-- Datetimepicker JS -->
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/apexchart/chart-data.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('assets/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/chartjs/chart-data.js') }}"></script>

    <!-- Datatable JS -->
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/script.js') }}"></script>

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
                const LOCK_MINUTES = {{ setting('security.lockscreen_timeout', 30) }};
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

    @stack('scripts')

</html>
