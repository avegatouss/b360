@php
$groupLabels = [
'main' => $instance->name ?? $instance->slug ?? 'Navigation',
'admin' => 'Administration',
];
@endphp

<div class="sidebar-inner" data-simplebar>
    <div id="sidebar-menu" class="sidebar-menu">

        @if(isset($instance))
        <ul>
            @foreach($menuGroups as $groupKey => $items)




            <li class="menu-title">
                <span>{{ $groupLabels[$groupKey] ?? ucfirst($groupKey) }}</span>
            </li>
            <li>
                <ul>
                    @foreach($items as $item)

                    @if($item->hasChildren())
                    {{-- Parent with sub-menu --}}
                    <li class="submenu">
                        <a href="javascript:void(0);" class="{{ $item->isActive() ? 'active subdrop' : '' }}">
                            @if($item->icon)<i class="{{ $item->icon }}"></i>@endif
                            <span>{{ $item->label }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        {{-- Use inline style instead of d-block class to avoid !important conflict with jQuery
                        slideToggle --}}


                        <ul {!! $item->isActive() ? 'style="display:block;"' : 'style="display:none;"' !!}>
                            @foreach($item->children as $child)
                            <li>
                                {{-- <a href="{{ $child->url($instance) }}"
                                    class="{{ $child->isActive() ? 'active' : '' }}">
                                    {{ $child->label }}
                                </a> --}}
                                <a href="{{ $child->url($instance) }}"
                                    class="{{ request()->url() === $child->url($instance) ? 'active' : '' }}">
                                    {{ $child->label }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @else
                    {{-- Simple menu item (no children) --}}
                    <li class="{{ $item->isActive() ? 'active' : '' }}">
                        <a href="{{ $item->url($instance) }}">
                            @if($item->icon)<i class="{{ $item->icon }}"></i>@endif
                            <span>{{ $item->label }}</span>
                        </a>
                    </li>
                    @endif

                    @endforeach
                </ul>
            </li>

            @endforeach
        </ul>
        @endif

        <div class="sidebar-footer">
            <div class="trial-item bg-white text-center border">
                <div class="bg-light p-3 text-center upgrade-image">
                    <img src="assets/img/icons/upgrade2.svg" alt="img">
                </div>
                <div class="p-2">
                    <h6 class="fs-14 fw-semibold mb-1">Upgrade to More</h6>
                    <p class="fs-13 mb-2">Subscribe to get more with Premium Features</p>
                    <a href="plans-billings.html"
                        class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center">
                        <i class="isax isax-crown5 me-1"></i>Upgrade
                    </a>
                </div>
                <a href="javascript:void(0);" class="close-icon fs-6"><i class="fa-solid fa-x"></i></a>
            </div>
            <ul class="menu-list">
                <li>
                    <a href="account-settings.html" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-title="Settings"><i class="isax isax-setting-25"></i></a>
                </li>
                <li>
                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-title="Documentation"><i class="isax isax-document-normal4"></i></a>
                </li>
                <li>
                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-title="Changelog"><i class="isax isax-cloud-change5"></i></a>
                </li>
                <li>
                    <a href="login.html" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Login"><i
                            class="isax isax-login-15"></i></a>
                </li>
            </ul>
        </div>

    </div>
</div>
