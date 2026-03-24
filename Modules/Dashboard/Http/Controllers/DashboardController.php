<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Modules\ModuleManager;
use Modules\Core\Support\CurrentInstance;

final class DashboardController extends Controller
{
    public function index(string $slug)
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            abort(503, 'Instance non résolue.');
        }

        // When hierarchical menu is enabled, redirect to the nav home instead of dashboard
        $hmEnabled = (bool) config('eshop360.hierarchical_menu');
        if (!$hmEnabled && class_exists(\Modules\Eshop360\Services\EshopSettingsService::class)) {
            try {
                $hmEnabled = (bool) app(\Modules\Eshop360\Services\EshopSettingsService::class)
                    ->value('general', 'hierarchical_menu', false);
            } catch (\Throwable) {
                // DB not ready
            }
        }
        if ($hmEnabled) {
            return redirect()->route('eshop360.nav.home', $slug);
        }

        $memberCount = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->count();

        $totalUsers = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->count();

        // Collect widgets from hook registry
        $registry = app(HookRegistry::class);
        $user = auth()->user();

        $modules = app(ModuleManager::class);

        $widgets = $registry->widgets()
            ->filter(function ($widget) use ($user, $instance, $modules) {
                if ($widget->requiredModule && !$modules->isEnabled($widget->requiredModule)) {
                    return false;
                }
                if ($widget->requiredPermission && !$user?->can($widget->requiredPermission)) {
                    return false;
                }
                if ($widget->visibleWhen && !($widget->visibleWhen)($user, $instance)) {
                    return false;
                }
                return true;
            })
            ->values();

        return view('dashboard::index', [
            'instance'    => $instance,
            'memberCount' => $memberCount,
            'totalUsers'  => $totalUsers,
            'widgets'     => $widgets,
        ]);
    }
}
