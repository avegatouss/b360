<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Configuration eShop') — {{ $instance->name ?? 'B360' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --wizard-accent: #4f46e5; }
        body { background: #f1f5f9; font-family: 'Inter', system-ui, sans-serif; min-height: 100vh; }
        .wizard-container { max-width: 800px; margin: 0 auto; padding: 2rem 1rem; }
        .wizard-header { text-align: center; margin-bottom: 2rem; }
        .wizard-header img { height: 40px; margin-bottom: 1rem; }
        .wizard-steps { display: flex; justify-content: center; gap: 0; margin-bottom: 2rem; }
        .wizard-step { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; color: #94a3b8; }
        .wizard-step.active { color: var(--wizard-accent); font-weight: 600; }
        .wizard-step.done { color: #10b981; }
        .wizard-step-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; border: 2px solid currentColor; }
        .wizard-step.active .wizard-step-num { background: var(--wizard-accent); color: #fff; border-color: var(--wizard-accent); }
        .wizard-step.done .wizard-step-num { background: #10b981; color: #fff; border-color: #10b981; }
        .wizard-sep { width: 40px; height: 2px; background: #e2e8f0; align-self: center; }
        .wizard-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; }
        .wizard-footer { display: flex; justify-content: space-between; margin-top: 1.5rem; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <img src="{{ URL::asset('build/img/logo.svg') }}" alt="B360">
            <h4 class="fw-bold">Configuration de votre espace eShop</h4>
        </div>

        <div class="wizard-steps">
            <div class="wizard-step @if($step >= 1) {{ $step == 1 ? 'active' : 'done' }} @endif">
                <span class="wizard-step-num">@if($step > 1)<i class="ti ti-check"></i>@else 1 @endif</span>
                <span>Hub central</span>
            </div>
            <div class="wizard-sep"></div>
            <div class="wizard-step @if($step >= 2) {{ $step == 2 ? 'active' : 'done' }} @endif">
                <span class="wizard-step-num">@if($step > 2)<i class="ti ti-check"></i>@else 2 @endif</span>
                <span>Canaux</span>
            </div>
            <div class="wizard-sep"></div>
            <div class="wizard-step @if($step >= 3) {{ $step == 3 ? 'active' : 'done' }} @endif">
                <span class="wizard-step-num">3</span>
                <span>Paramètres</span>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="wizard-card">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
