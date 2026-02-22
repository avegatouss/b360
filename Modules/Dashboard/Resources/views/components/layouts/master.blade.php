<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'B360') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --sidebar-w: 240px;
            --header-h: 56px;
            --bg: #f1f5f9;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 10;
        }

        .sidebar-brand {
            padding: 0 1.25rem;
            height: var(--header-h);
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border);
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.04em;
            text-decoration: none;
        }

        .sidebar-instance {
            padding: 0.75rem 1.25rem;
            font-size: 0.75rem;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            transition: background .12s, color .12s;
        }

        .nav-link:hover, .nav-link.active {
            background: #eff6ff;
            color: var(--primary);
        }

        .sidebar-footer {
            padding: 0.75rem 1rem;
            border-top: 1px solid var(--border);
        }

        .sidebar-user {
            font-size: 0.8125rem;
            color: var(--muted);
            margin-bottom: 0.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: none;
            font-size: 0.8125rem;
            color: var(--muted);
            cursor: pointer;
            text-align: left;
            transition: background .12s, color .12s;
        }

        .logout-btn:hover { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

        /* Main */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            height: var(--header-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
        }

        .topbar-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }

        .content {
            flex: 1;
            padding: 1.5rem;
        }

        /* Cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
        }

        .stat-label {
            font-size: 0.8125rem;
            color: var(--muted);
            font-weight: 500;
            margin-bottom: 0.375rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
        }

        .stat-sub {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 0.25rem;
        }

        /* Section */
        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.75rem;
        }

        .info-row {
            display: flex;
            gap: 0.5rem;
            font-size: 0.875rem;
            padding: 0.375rem 0;
            border-bottom: 1px solid var(--border);
        }

        .info-row:last-child { border-bottom: none; }

        .info-key {
            color: var(--muted);
            min-width: 140px;
            font-weight: 500;
        }

        .info-val { color: var(--text); }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* Alert */
        .alert {
            border-radius: 8px;
            padding: 0.875rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    </style>
</head>
<body>

    {{-- Sidebar --}}
    <aside class="sidebar">
        <a href="{{ isset($instance) ? route('dashboard.instance', $instance->slug) : '/' }}" class="sidebar-brand">
            B360
        </a>

        @if(isset($instance))
            <div class="sidebar-instance">{{ $instance->name ?? $instance->slug }}</div>
        @endif

        <nav class="sidebar-nav">
            @if(isset($instance))
                <a href="{{ route('dashboard.instance', $instance->slug) }}"
                   class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                    Tableau de bord
                </a>
                <a href="{{ route('users.index', $instance->slug) }}"
                   class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    Utilisateurs
                </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            @auth
                <div class="sidebar-user">{{ auth()->user()->name ?? auth()->user()->email }}</div>
                <form method="POST"
                      action="{{ isset($instance) ? route('instance.logout', $instance->slug) : route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">Se déconnecter</button>
                </form>
            @endauth
        </div>
    </aside>

    {{-- Main --}}
    <div class="main">
        <header class="topbar">
            <h1 class="topbar-title">{{ $pageTitle ?? 'Tableau de bord' }}</h1>
        </header>

        <main class="content">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>

</body>
</html>
