@extends('eshop360::hierarchical-menu.layout', [
    'backUrl' => route('eshop360.nav.admin', $instance?->slug ?? ''),
])

@section('breadcrumb')
    <a href="{{ route('eshop360.nav.home', $instance?->slug ?? '') }}">Accueil</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <a href="{{ route('eshop360.nav.admin', $instance?->slug ?? '') }}">Administration</a>
    <span class="hm-bc-sep"><i class="ti ti-chevron-right"></i></span>
    <span class="hm-bc-current">{{ $sectionData['label'] }}</span>
@endsection

@section('content')
    <div class="hm-page-header">
        <h1>{{ $sectionData['label'] }}</h1>
    </div>

    @if(!empty($sectionData['children']))
        <div class="hm-grid">
            @foreach($sectionData['children'] as $item)
                @if($item->route)
                    <a href="{{ route($item->route, $instance?->slug ?? '') }}"
                       class="hm-card hm-card-action"
                       style="--card-accent: {{ $sectionData['color'] }}">
                        <div class="hm-card-icon" style="background: {{ $sectionData['color'] }}">
                            <i class="{{ $item->icon ?? 'ti ti-arrow-right' }}"></i>
                        </div>
                        <div class="hm-card-title">{{ $item->label }}</div>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
@endsection
