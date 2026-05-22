@php
    $groupLabels = [
        'main' => $instance->name ?? $instance->slug ?? 'Navigation',
        'admin' => 'Administration',
    ];
@endphp

{{-- @if(isset($instance))
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
@endif --}}

@if(isset($instance))
    <div class="startbar d-print-none">
        <!--start brand-->
        <div class="brand">
            <a href="{{ url('/') }}" class="logo">
                <span>
                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="logo-small" class="logo-sm">
                </span>
                <span>
                    <img src="{{ asset('assets/images/logo-light.png') }}" alt="logo-large" class="logo-lg logo-light">
                    <img src="{{ asset('assets/images/logo-dark.png') }}" alt="logo-large" class="logo-lg logo-dark">
                </span>
            </a>
        </div>
        <!--end brand-->

        <!--start startbar-menu-->
        <div class="startbar-menu">
            <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                <div class="d-flex align-items-start flex-column w-100">

                    <ul class="navbar-nav mb-auto w-100">

                        @foreach($menuGroups as $groupKey => $items)

                            <!-- Titre du groupe -->
                            <li class="menu-label mt-2">
                                <span>
                                    {{ $groupLabels[$groupKey] ?? ucfirst($groupKey) }}
                                </span>
                            </li>

                            <!-- Menus -->
                            @foreach($items as $item)

                                <li class="nav-item {{ $item->isActive() ? 'active' : '' }}">
                                    <a href="{{ $item->url($instance) }}"
                                    class="nav-link {{ $item->isActive() ? 'active' : '' }}">

                                        <i class="{{ $item->icon }} menu-icon"></i>
                                        <span>{{ $item->label }}</span>
                                    </a>
                                </li>

                            @endforeach

                        @endforeach

                    </ul>

                </div>
            </div>
        </div>
        <!--end startbar-menu-->
    </div>

    <div class="startbar-overlay d-print-none"></div>
@endif
