<?php

namespace Modules\Eshop360\Http\Controllers\Navigation;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
}
