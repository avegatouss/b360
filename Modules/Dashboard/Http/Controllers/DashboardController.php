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

        if (! $instance) {
            abort(503, 'Instance non résolue.');
        }

        // R-401-FIX S6 — Le mode "hierarchical menu" est désormais piloté
        // par le slot HookRegistry 'hierarchical-nav.fab' (le FAB est
        // rendu dans le master layout via x-dashboard::layout-slot).
        // L'ancienne redirection inconditionnelle vers eshop360.nav.home
        // a été retirée : l'utilisateur voit le dashboard widgets et
        // clique le FAB pour atteindre la nav hiérarchique. Le bouton
        // est masqué automatiquement si Eshop360 OFF ou si setting
        // hierarchical_menu désactivé (cf. Eshop360HooksProvider
        // registerLayoutSlots visibleWhen).

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
                if ($widget->requiredModule && ! $modules->isEnabled($widget->requiredModule)) {
                    return false;
                }
                if ($widget->requiredPermission && ! $user?->can($widget->requiredPermission)) {
                    return false;
                }
                if ($widget->visibleWhen && ! ($widget->visibleWhen)($user, $instance)) {
                    return false;
                }

                return true;
            })
            ->values();

        return view('dashboard::index', [
            'instance' => $instance,
            'memberCount' => $memberCount,
            'totalUsers' => $totalUsers,
            'widgets' => $widgets,
        ]);
    }
}
