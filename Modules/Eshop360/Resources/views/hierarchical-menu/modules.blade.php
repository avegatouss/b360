@extends('eshop360::hierarchical-menu.layout', [
    'backUrl' => route('eshop360.nav.home', $instance?->slug ?? ''),
])

@section('breadcrumb')
    <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}">Accueil</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <span class="hm-bc-current">{{ $channel->name }}</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <h1>{{ $channel->name }}</h1>
        <p>Choisissez un module</p>
    </div>

    @if(!empty($moduleGroups))
        <div class="hm-grid hm-grid-lg">
            @foreach($moduleGroups as $key => $group)
                <a href="{{ route('eshop360.nav.actions', [$instance?->slug ?? '', $channel->slug, $key]) }}"
                   class="hm-card"
                   style="--card-accent: {{ $group['color'] }}">
                    <div class="hm-card-icon" style="background: {{ $group['color'] }}">
                        <i class="{{ $group['icon'] }}"></i>
                    </div>
                    <div class="hm-card-title">{{ $group['label'] }}</div>
                    <div class="hm-card-subtitle">{{ $group['count'] }} {{ $group['count'] > 1 ? 'éléments' : 'élément' }}</div>
                </a>
            @endforeach
        </div>
    @else
        <div class="hm-empty">
            <i class="ti ti-apps-off"></i>
            <p>Aucun module disponible pour ce canal.</p>
        </div>
    @endif
@endsection
