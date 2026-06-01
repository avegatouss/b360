<?php

namespace Modules\Eshop360\Http\Controllers\Navigation;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Hooks\HookManager;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\HierarchicalMenuService;
use Modules\Eshop360\Support\CurrentChannel;

final class HierarchicalMenuController extends Controller
{
    public function __construct(
        private readonly HierarchicalMenuService $menuService,
        private readonly ChannelAccessService $channelAccess,
    ) {}

    /**
     * Level 1 — Home page: logo + channel cards.
     * Only shows channels the current user can access.
     */
    public function home()
    {
        $user = auth()->user();
        $channels = $this->menuService->getChannels();

        // Fallback: if no channels exist and hierarchical menu is active, redirect to wizard
        if ($channels->isEmpty()) {
            $instance = CurrentInstance::get();
            return redirect()->route('eshop360.setup.hub', $instance->slug);
        }

        // Filter channels by user access (hub admins see all)
        if (!$this->channelAccess->isHubAdmin($user)) {
            $accessible = $this->channelAccess->accessibleChannelIds($user);
            $channels = $channels->filter(fn ($ch) => $accessible?->contains($ch->id));
        }

        // Clear channel context when returning to home (channel selection)
        CurrentChannel::clear();

        $instance = CurrentInstance::get();

        return view('eshop360::hierarchical-menu.home', compact('channels', 'instance'));
    }

    /**
     * Level 2 — Module cards for a given channel.
     */
    public function modules(Request $request, string $slug, string $channelSlug)
    {
        $channel = $this->menuService->findChannel($channelSlug);

        if (!$channel) {
            abort(404, 'Canal introuvable');
        }

        // Verify user has access to this channel
        abort_unless($this->channelAccess->canAccessChannel(auth()->user(), $channel), 403);

        // Set active channel in session — all subsequent pages will be scoped
        CurrentChannel::set($channel);

        $moduleGroups = $this->menuService->getModuleGroups($channel);
        $instance = CurrentInstance::get();

        return view('eshop360::hierarchical-menu.modules', compact(
            'channel', 'moduleGroups', 'instance'
        ));
    }

    /**
     * Level 3 — Action cards for a given module within a channel.
     */
    public function actions(Request $request, string $slug, string $channelSlug, string $moduleKey)
    {
        $channel = $this->menuService->findChannel($channelSlug);

        if (!$channel) {
            abort(404, 'Canal introuvable');
        }

        abort_unless($this->channelAccess->canAccessChannel(auth()->user(), $channel), 403);

        // Reinforce channel context in session
        CurrentChannel::set($channel);

        $module = $this->menuService->getModuleChildren($moduleKey, $channel);

        if (!$module) {
            abort(404, 'Module introuvable');
        }

        $instance = CurrentInstance::get();

        return view('eshop360::hierarchical-menu.actions', compact(
            'channel', 'module', 'instance'
        ));
    }

    /**
     * Administration page — shows instance-level menu sections as tiles.
     * Visible only to super-admin and instance-admin.
     */
    public function admin()
    {
        $user = auth()->user();
        abort_unless($this->channelAccess->isHubAdmin($user), 403);

        $instance = CurrentInstance::get();
        $registry = app(HookManager::class)->registry();

        $allMenus = $registry->menu();

        $adminSections = [];

        // Admin group items (Users, Roles, Modules, Settings, Billing)
        $adminItems = $allMenus->filter(fn ($item) => $item->group === 'admin' && !$item->parentId);
        foreach ($adminItems as $item) {
            $children = $allMenus->filter(fn ($child) => $child->parentId === $item->id);
            $adminSections[] = [
                'key' => $item->id,
                'label' => $item->label,
                'icon' => $item->icon ?? 'ti ti-settings',
                'color' => '#475569',
                'children' => $children->values()->all(),
                'count' => $children->count(),
                'route' => $item->route,
            ];
        }

        // Eshop settings group
        $eshopSettings = $allMenus->first(fn ($item) => $item->id === 'eshop360.eshop_settings');
        if ($eshopSettings) {
            $eshopChildren = $allMenus->filter(fn ($child) => $child->parentId === 'eshop360.eshop_settings');
            $adminSections[] = [
                'key' => 'eshop360.eshop_settings',
                'label' => $eshopSettings->label,
                'icon' => $eshopSettings->icon ?? 'ti ti-adjustments-horizontal',
                'color' => '#4f46e5',
                'children' => $eshopChildren->values()->all(),
                'count' => $eshopChildren->count(),
                'route' => null,
            ];
        }

        return view('eshop360::hierarchical-menu.admin', compact('adminSections', 'instance'));
    }

    /**
     * Administration sub-section — shows action tiles for a specific admin section.
     */
    public function adminSection(Request $request, string $slug, string $section)
    {
        $user = auth()->user();
        abort_unless($this->channelAccess->isHubAdmin($user), 403);

        $instance = CurrentInstance::get();
        $registry = app(HookManager::class)->registry();
        $allMenus = $registry->menu();

        $parent = $allMenus->first(fn ($item) => $item->id === $section);
        if (!$parent) {
            abort(404, 'Section introuvable');
        }

        $children = $allMenus->filter(fn ($child) => $child->parentId === $section)->values()->all();

        $sectionData = [
            'key' => $parent->id,
            'label' => $parent->label,
            'icon' => $parent->icon ?? 'ti ti-settings',
            'color' => '#475569',
            'children' => $children,
            'count' => count($children),
        ];

        return view('eshop360::hierarchical-menu.admin-section', compact('sectionData', 'instance'));
    }
}
