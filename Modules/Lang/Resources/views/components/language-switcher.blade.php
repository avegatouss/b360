{{-- @php
$localeManager = app(\Modules\Lang\Services\LocaleManager::class);
$currentLocale = $localeManager->current();
$supported = $localeManager->supported();
$labels = $localeManager->labels();
$flags = config('lang.flags', []);
@endphp

@if(count($supported) > 1)
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="badge bg-light text-dark">{{ $flags[$currentLocale] ?? strtoupper($currentLocale) }}</span>
        {{ $labels[$currentLocale] ?? $currentLocale }}
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach($supported as $loc)
        <li>
            <a class="dropdown-item {{ $loc === $currentLocale ? 'active' : '' }}" href="{{ url('/lang/' . $loc) }}">
                <span class="badge bg-light text-dark me-1">{{ $flags[$loc] ?? strtoupper($loc) }}</span>
                {{ $labels[$loc] ?? $loc }}
            </a>
        </li>
        @endforeach
    </ul>
</li>
@endif --}}

@php
$localeManager = app(\Modules\Lang\Services\LocaleManager::class);
$currentLocale = $localeManager->current();
$supported = $localeManager->supported();
$labels = $localeManager->labels();

$flagImages = [
'en' => asset('assets/img/flags/us.svg'),
'fr' => asset('assets/img/flags/fr.svg'),
];
@endphp

@if(count($supported) > 1)
<li class="nav-item dropdown has-arrow flag-nav me-2">
    <a class="btn btn-menubar" data-bs-toggle="dropdown" href="javascript:void(0);" role="button">
        <img src="{{ $flagImages[$currentLocale] ?? $flagImages['en'] }}" alt="{{ $currentLocale }}" class="img-fluid">
    </a>

    <ul class="dropdown-menu p-2 dropdown-menu-end">
        @foreach($supported as $loc)
        <li>
            <a href="{{ url('/lang/' . $loc) }}" class="dropdown-item {{ $loc === $currentLocale ? 'active' : '' }}">
                <img src="{{ $flagImages[$loc] ?? '' }}" alt="{{ $loc }}" class="me-2" width="20">

                {{ $labels[$loc] ?? strtoupper($loc) }}
            </a>
        </li>
        @endforeach
    </ul>
</li>
@endif
