<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="B-360">
    <meta name="robots" content="noindex, nofollow">
    <title>B360 — Navigation</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('/build/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ url('build/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('build/plugins/tabler-icons/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ url('build/css/animate.css') }}">
    <style>
        :root {
            --hm-bg: #f0f2f5;
            --hm-card-bg: #ffffff;
            --hm-text: #1e293b;
            --hm-text-muted: #64748b;
            --hm-border: #e2e8f0;
            --hm-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --hm-shadow-hover: 0 10px 25px rgba(0,0,0,.1), 0 4px 10px rgba(0,0,0,.06);
            --hm-radius: 16px;
            --hm-radius-sm: 12px;
            --hm-navbar-h: 64px;
        }

        * { box-sizing: border-box; }

        body.hm-body {
            margin: 0;
            padding: 0;
            background: var(--hm-bg);
            color: var(--hm-text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .hm-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--hm-navbar-h);
            background: var(--hm-card-bg);
            border-bottom: 1px solid var(--hm-border);
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
        }

        .hm-navbar-brand img {
            height: 32px;
        }

        .hm-navbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hm-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid var(--hm-border);
            background: var(--hm-card-bg);
            color: var(--hm-text);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s ease;
            cursor: pointer;
            white-space: nowrap;
        }

        .hm-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: var(--hm-text);
            text-decoration: none;
        }

        .hm-btn-icon {
            padding: 8px 10px;
        }

        .hm-btn i {
            font-size: 18px;
        }

        /* ── Breadcrumb ── */
        .hm-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            min-width: 0;
            margin-left: 8px;
            font-size: 14px;
            color: var(--hm-text-muted);
        }

        .hm-breadcrumb a {
            color: var(--hm-text-muted);
            text-decoration: none;
            transition: color .2s;
            white-space: nowrap;
        }

        .hm-breadcrumb a:hover {
            color: var(--hm-text);
        }

        .hm-breadcrumb .hm-bc-sep {
            color: #cbd5e1;
            font-size: 12px;
        }

        .hm-breadcrumb .hm-bc-current {
            color: var(--hm-text);
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Navbar user section ── */
        .hm-navbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
        }

        .hm-navbar-user .hm-user-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--hm-text);
        }

        /* ── Main content ── */
        .hm-main {
            padding-top: calc(var(--hm-navbar-h) + 32px);
            padding-bottom: 48px;
            min-height: 100vh;
        }

        .hm-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ── Page header ── */
        .hm-page-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .hm-page-header .hm-logo {
            margin-bottom: 16px;
        }

        .hm-page-header .hm-logo img {
            height: 48px;
        }

        .hm-page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--hm-text);
            margin: 0 0 8px;
        }

        .hm-page-header p {
            font-size: 15px;
            color: var(--hm-text-muted);
            margin: 0;
        }

        /* ── Card grid ── */
        .hm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .hm-grid-lg {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        /* ── Card ── */
        .hm-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
            background: var(--hm-card-bg);
            border-radius: var(--hm-radius);
            border: 1px solid var(--hm-border);
            box-shadow: var(--hm-shadow);
            text-decoration: none;
            color: var(--hm-text);
            transition: all .3s cubic-bezier(.4, 0, .2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            min-height: 160px;
        }

        .hm-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--hm-shadow-hover);
            border-color: transparent;
            text-decoration: none;
            color: var(--hm-text);
        }

        .hm-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--card-accent, #4f46e5);
            opacity: 0;
            transition: opacity .3s ease;
        }

        .hm-card:hover::before {
            opacity: 1;
        }

        .hm-card-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--hm-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 26px;
            color: #fff;
            background: var(--card-accent, #4f46e5);
            transition: transform .3s cubic-bezier(.4, 0, .2, 1);
        }

        .hm-card:hover .hm-card-icon {
            transform: scale(1.1);
        }

        .hm-card-title {
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            line-height: 1.4;
        }

        .hm-card-subtitle {
            font-size: 12px;
            color: var(--hm-text-muted);
            margin-top: 4px;
            text-align: center;
        }

        .hm-card-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            background: #f1f5f9;
            color: var(--hm-text-muted);
        }

        /* ── Channel card variant ── */
        .hm-card-channel {
            min-height: 200px;
            padding: 40px 28px;
        }

        .hm-card-channel .hm-card-icon {
            width: 64px;
            height: 64px;
            font-size: 30px;
        }

        .hm-card-channel .hm-card-title {
            font-size: 17px;
        }

        /* ── Action card variant (L3) ── */
        .hm-card-action {
            flex-direction: row;
            justify-content: flex-start;
            padding: 20px 24px;
            min-height: auto;
            gap: 16px;
        }

        .hm-card-action .hm-card-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            font-size: 20px;
            margin-bottom: 0;
        }

        .hm-card-action .hm-card-title {
            text-align: left;
            font-size: 14px;
        }

        /* ── Empty state ── */
        .hm-empty {
            text-align: center;
            padding: 64px 24px;
            color: var(--hm-text-muted);
        }

        .hm-empty i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            opacity: .4;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .hm-navbar {
                padding: 0 16px;
                gap: 10px;
            }

            .hm-breadcrumb {
                display: none;
            }

            .hm-container {
                padding: 0 16px;
            }

            .hm-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 12px;
            }

            .hm-card {
                padding: 24px 16px;
                min-height: 140px;
            }

            .hm-card-channel {
                min-height: 160px;
                padding: 28px 20px;
            }

            .hm-page-header h1 {
                font-size: 22px;
            }

            .hm-card-action {
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .hm-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .hm-card-icon {
                width: 44px;
                height: 44px;
                font-size: 22px;
            }
        }

        /* ── Animation on enter ── */
        .hm-card {
            animation: hmFadeUp .4s ease both;
        }

        @keyframes hmFadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hm-grid .hm-card:nth-child(1) { animation-delay: .02s; }
        .hm-grid .hm-card:nth-child(2) { animation-delay: .06s; }
        .hm-grid .hm-card:nth-child(3) { animation-delay: .10s; }
        .hm-grid .hm-card:nth-child(4) { animation-delay: .14s; }
        .hm-grid .hm-card:nth-child(5) { animation-delay: .18s; }
        .hm-grid .hm-card:nth-child(6) { animation-delay: .22s; }
        .hm-grid .hm-card:nth-child(7) { animation-delay: .26s; }
        .hm-grid .hm-card:nth-child(8) { animation-delay: .30s; }
        .hm-grid .hm-card:nth-child(9) { animation-delay: .34s; }
        .hm-grid .hm-card:nth-child(10) { animation-delay: .38s; }
        .hm-grid .hm-card:nth-child(11) { animation-delay: .42s; }
        .hm-grid .hm-card:nth-child(12) { animation-delay: .46s; }
        .hm-grid .hm-card:nth-child(13) { animation-delay: .50s; }
        .hm-grid .hm-card:nth-child(14) { animation-delay: .54s; }
        .hm-grid .hm-card:nth-child(15) { animation-delay: .58s; }
    </style>
</head>
<body class="hm-body">

    {{-- Navbar --}}
    <nav class="hm-navbar">
        <div class="hm-navbar-actions">
            @if(isset($backUrl))
                <a href="{{ $backUrl }}" class="hm-btn hm-btn-icon" title="Retour">
                    <i class="ti ti-arrow-left"></i>
                </a>
            @endif
            <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}" class="hm-btn hm-btn-icon" title="Accueil">
                <i class="ti ti-home"></i>
            </a>
        </div>

        <div class="hm-breadcrumb">
            @yield('breadcrumb')
        </div>

        <div class="hm-navbar-user">
            @auth
                <span class="hm-user-name">{{ auth()->user()->full_name ?? auth()->user()->name ?? '' }}</span>
                <a href="{{ route('instance.logout', $instance?->slug ?? '') }}"
                   class="hm-btn hm-btn-icon" title="Deconnexion"
                   style="color:#dc2626;border-color:#fecaca;">
                    <i class="ti ti-logout"></i>
                </a>
            @endauth
            <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}" class="hm-navbar-brand">
                <img src="{{ URL::asset('build/img/logo.svg') }}" alt="B360">
            </a>
        </div>
    </nav>

    {{-- Main content --}}
    <main class="hm-main">
        <div class="hm-container">
            @yield('content')
        </div>
    </main>

    <script src="{{ url('build/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ url('build/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
