<?php

namespace Modules\Dashboard\View\Components;

use Illuminate\View\Component;
use Modules\Core\Hooks\HookFilter;
use Modules\Core\Hooks\Registry\HookRegistry;

final class Sidebar extends Component
{
    public $menuGroups;
    public $instance;

    public function __construct($instance = null)
    {
        $this->instance = $instance;
        $user = auth()->user();

        $registry = app(HookRegistry::class);
        $filter = app(HookFilter::class);

        $filtered = $filter->filter($registry->menu(), $user, $instance);
        $this->menuGroups = $filtered->groupBy('group');
    }

    public function render()
    {
        return view('dashboard::components.sidebar');
    }
}
