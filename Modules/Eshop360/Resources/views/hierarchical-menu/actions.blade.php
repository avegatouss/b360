@extends('eshop360::hierarchical-menu.layout', [
    'backUrl' => route('eshop360.nav.modules', [$instance?->slug ?? '', $channel->slug]),
])

@section('breadcrumb')
    <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}">Accueil</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <a href="{{ route('eshop360.nav.modules', [$instance?->slug ?? '', $channel->slug]) }}">{{ $channel->name }}</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <span class="hm-bc-current">{{ $module['label'] }}</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <h1>{{ $module['label'] }}</h1>
        <p>{{ $channel->name }} — Sélectionnez une action</p>
    </div>

    @if(!empty($module['children']))
        <div class="hm-grid">
            @foreach($module['children'] as $item)
                <a href="{{ $item->url($instance) }}"
                   class="hm-card hm-card-action"
                   style="--card-accent: {{ $module['color'] }}">
                    <div class="hm-card-icon" style="background: {{ $module['color'] }}">
                        <i class="{{ $item->icon ?? $module['icon'] }}"></i>
                    </div>
                    <div class="hm-card-title">{{ $item->label }}</div>
                </a>
            @endforeach
        </div>
    @else
        <div class="hm-empty">
            <i class="ti ti-mood-empty"></i>
            <p>Aucune action disponible pour ce module.</p>
        </div>
    @endif
@endsection
