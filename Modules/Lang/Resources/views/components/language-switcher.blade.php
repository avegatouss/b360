@php
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
                <a class="dropdown-item {{ $loc === $currentLocale ? 'active' : '' }}"
                   href="{{ url('/lang/' . $loc) }}">
                    <span class="badge bg-light text-dark me-1">{{ $flags[$loc] ?? strtoupper($loc) }}</span>
                    {{ $labels[$loc] ?? $loc }}
                </a>
            </li>
        @endforeach
    </ul>
</li>
@endif
