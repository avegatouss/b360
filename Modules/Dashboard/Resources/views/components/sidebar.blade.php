@php
    $groupLabels = [
        'main' => $instance->name ?? $instance->slug ?? 'Navigation',
        'admin' => 'Administration',
    ];
@endphp

@if(isset($instance))
    @foreach($menuGroups as $groupKey => $items)
    <li class="submenu-open">
        <h6 class="submenu-hdr">{{ $groupLabels[$groupKey] ?? ucfirst($groupKey) }}</h6>
        <ul>
            @foreach($items as $item)
            <li>
                <a href="{{ $item->url($instance) }}"
                   class="{{ $item->isActive() ? 'active' : '' }}">
                    <i class="{{ $item->icon }} fs-16 me-2"></i>
                    <span>{{ $item->label }}</span>
                </a>
            </li>
            @endforeach
        </ul>
    </li>
    @endforeach
@endif
