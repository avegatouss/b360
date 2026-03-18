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
                @if($item->hasChildren())
                {{-- Parent with sub-menu --}}
                <li class="submenu">
                    <a href="javascript:void(0);"
                       class="{{ $item->isActive() ? 'subdrop active' : '' }}">
                        @if($item->icon)<i class="{{ $item->icon }} fs-16 me-2"></i>@endif
                        <span>{{ $item->label }}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    {{-- Use inline style instead of d-block class to avoid !important conflict with jQuery slideToggle --}}
                    <ul {!! $item->isActive() ? 'style="display:block;"' : 'style="display:none;"' !!}>
                        @foreach($item->children as $child)
                        <li>
                            <a href="{{ $child->url($instance) }}"
                               class="{{ $child->isActive() ? 'active' : '' }}">
                                {{ $child->label }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @else
                {{-- Simple menu item (no children) --}}
                <li>
                    <a href="{{ $item->url($instance) }}"
                       class="{{ $item->isActive() ? 'active' : '' }}">
                        @if($item->icon)<i class="{{ $item->icon }} fs-16 me-2"></i>@endif
                        <span>{{ $item->label }}</span>
                    </a>
                </li>
                @endif
            @endforeach
        </ul>
    </li>
    @endforeach
@endif
