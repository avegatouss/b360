@extends('eshop360::hierarchical-menu.layout')

@section('breadcrumb')
    <span class="hm-bc-current">Accueil</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <div class="hm-logo">
            <img src="{{ URL::asset('build/img/logo.svg') }}" alt="B360">
        </div>
        <h1>Bienvenue sur B360</h1>
        <p>Sélectionnez un canal pour commencer</p>
    </div>

    @if($channels->isNotEmpty())
        <div class="hm-grid hm-grid-lg">
            @foreach($channels as $channel)
                <a href="{{ route('eshop360.nav.modules', [$instance?->slug ?? '', $channel->slug]) }}"
                   class="hm-card hm-card-channel"
                   style="--card-accent: {{ $channel->portal_settings['theme_color'] ?? '#4f46e5' }}">
                    <div class="hm-card-icon"
                         style="background: {{ $channel->portal_settings['theme_color'] ?? '#4f46e5' }}">
                        <i class="ti ti-building-store"></i>
                    </div>
                    <div class="hm-card-title">{{ $channel->name }}</div>
                    @if($channel->code)
                        <div class="hm-card-subtitle">{{ $channel->code }}</div>
                    @endif
                </a>
            @endforeach

            {{-- Administration tile — visible for hub admins only --}}
            @if(app(\Modules\Eshop360\Services\ChannelAccessService::class)->isHubAdmin(auth()->user()))
                <a href="{{ route('eshop360.nav.admin', [$instance?->slug ?? '']) }}"
                   class="hm-card hm-card-channel"
                   style="--card-accent: #475569">
                    <div class="hm-card-icon" style="background: #475569">
                        <i class="ti ti-settings-2"></i>
                    </div>
                    <div class="hm-card-title">Administration</div>
                    <div class="hm-card-subtitle">Paramètres & gestion</div>
                </a>
            @endif
        </div>
    @else
        <div class="hm-empty">
            <i class="ti ti-building-store"></i>
            <p>Aucun canal de distribution actif.<br>Configurez vos canaux dans les paramètres.</p>
        </div>
    @endif
@endsection
