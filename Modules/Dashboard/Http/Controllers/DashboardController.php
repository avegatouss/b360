<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\CurrentInstance;

final class DashboardController extends Controller
{
    public function index(string $slug)
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            abort(503, 'Instance non résolue.');
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

        $widgets = $registry->widgets()
            ->filter(function ($widget) use ($user, $instance) {
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
