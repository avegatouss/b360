@extends('eshop360::hierarchical-menu.layout', [
    'backUrl' => route('eshop360.nav.home', $instance?->slug ?? ''),
])

@section('breadcrumb')
    <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}">Accueil</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <span class="hm-bc-current">Administration</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <h1>Administration</h1>
        <p>Gestion de l'instance et paramètres</p>
    </div>

    @if(!empty($adminSections))
        <div class="hm-grid hm-grid-lg">
            @foreach($adminSections as $section)
                @if($section['count'] > 0)
                    <a href="{{ route('eshop360.nav.admin.section', [$instance?->slug ?? '', $section['key']]) }}"
                       class="hm-card"
                       style="--card-accent: {{ $section['color'] }}">
                        <div class="hm-card-icon" style="background: {{ $section['color'] }}">
                            <i class="{{ $section['icon'] }}"></i>
                        </div>
                        <div class="hm-card-title">{{ $section['label'] }}</div>
                        <div class="hm-card-subtitle">{{ $section['count'] }} {{ $section['count'] > 1 ? 'éléments' : 'élément' }}</div>
                    </a>
                @elseif($section['route'])
                    <a href="{{ route($section['route'], $instance?->slug ?? '') }}"
                       class="hm-card"
                       style="--card-accent: {{ $section['color'] }}">
                        <div class="hm-card-icon" style="background: {{ $section['color'] }}">
                            <i class="{{ $section['icon'] }}"></i>
                        </div>
                        <div class="hm-card-title">{{ $section['label'] }}</div>
                    </a>
                @endif
            @endforeach
        </div>
    @else
        <div class="hm-empty">
            <i class="ti ti-settings-off"></i>
            <p>Aucune section d'administration disponible.</p>
        </div>
    @endif
@endsection
