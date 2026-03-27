<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Models\LoginLog;
use Modules\Core\Support\CurrentInstance;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = LoginLog::with('user')
            ->where('instance_id', $instance->id)
            ->orderByDesc('created_at');

        // Filter by user (search by name or email)
        if ($search = $request->input('user')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('authmod::login-logs.index', [
            'instance' => $instance,
            'logs'     => $logs,
            'filters'  => $request->only(['user', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function userHistory(Request $request)
    {
        $instance = CurrentInstance::get();
        $user = $request->user();

        $logs = LoginLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('authmod::login-logs.index', [
            'instance'  => $instance,
            'logs'      => $logs,
            'filters'   => [],
            'userMode'  => true,
        ]);
    }
}
