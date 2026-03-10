<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;

final class DashboardController extends Controller
{
    public function index(string $slug)
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            abort(503, 'Instance non résolue.');
        }

        // Membres actifs de CETTE instance
        $memberCount = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->count();

        // Utilisateurs de CETTE instance (pas tous les users system)
        $totalUsers = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->count();

        return view('dashboard::index', [
            'instance'    => $instance,
            'memberCount' => $memberCount,
            'totalUsers'  => $totalUsers,
        ]);
    }
}
