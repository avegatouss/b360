<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <title>B360 • Installation</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Installation de la plateforme B360">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('/build/img/favicon.png') }}">

    {{-- Head DreamPOS (CSS globaux uniquement) --}}
    @include('layout.partials.head')
</head>

<body class="account-page bg-light">

    {{-- Loader simple --}}
    @component('components.loader')
    @endcomponent

    <!-- Installer Wrapper -->
    <div class="main-wrapper d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-7 col-md-9 col-sm-12">

                    {{-- Card Installer --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">

                            {{-- Logo --}}
                            <div class="text-center mb-4">
                                <img src="{{ asset('/build/img/logo.svg') }}"
                                     alt="B360"
                                     height="50">
                                <h4 class="mt-3 fw-bold">Installation B360</h4>
                                <p class="text-muted mb-0">
                                    Configuration initiale de la plateforme
                                </p>
                            </div>

                            {{-- Contenu Installer --}}
                            @yield('content')

                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="text-center mt-3 text-muted small">
                        © {{ date('Y') }} B360 — Tous droits réservés par KHOGA
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Scripts DreamPOS (JS globaux uniquement) --}}
    @include('layout.partials.footer-scripts')
    @stack('scripts')
</body>
</html>
