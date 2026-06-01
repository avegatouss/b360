<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\CronLog;

class CronLogController extends Controller
{
    public function index(Request $request)
    {
        // Distinct command names for filter dropdown
        $commands = CronLog::select('command')->distinct()->orderBy('command')->pluck('command');

        $logs = CronLog::query()
            ->forCommand($request->get('command'))
            ->withStatus($request->get('status'))
            ->betweenDates($request->get('date_from'), $request->get('date_to'))
            ->latest('executed_at')
            ->paginate(50)
            ->withQueryString();

        // Stats
        $stats = [
            'total' => CronLog::count(),
            'success' => CronLog::where('status', 'success')->count(),
            'failed' => CronLog::where('status', 'failed')->count(),
            'avg_duration' => (int) CronLog::avg('duration_ms'),
        ];

        return view('core::admin.cron-logs', compact('logs', 'commands', 'stats'));
    }
}
