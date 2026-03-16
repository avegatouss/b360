<?php

namespace Modules\Eshop360\Http\Controllers\Codifarm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\CodifarmMarginConfig;
use Modules\Eshop360\Models\CodifarmMarginLog;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Services\MarginService;
use Modules\Eshop360\Services\CostCalculatorService;
use Modules\Core\Support\CurrentInstance;

class CodifarmController extends Controller
{
    public function __construct(
        private MarginService $marginService,
        private CostCalculatorService $costCalculator
    ) {}

    public function dashboard()
    {
        $instance = CurrentInstance::get();
        $config = CodifarmMarginConfig::where('instance_id', $instance->id)->first();
        $summary = $this->codifarmSummary(
            $instance->id,
            now()->startOfMonth()->startOfDay()->toDateTimeString(),
            now()->endOfDay()->toDateTimeString()
        );
        $recentLogs = CodifarmMarginLog::where('instance_id', $instance->id)
            ->with('order.customer')
            ->latest()
            ->limit(20)
            ->get();
        return view('eshop360::codifarm.dashboard', compact('config', 'summary', 'recentLogs'));
    }

    public function config()
    {
        $instance = CurrentInstance::get();
        $config = CodifarmMarginConfig::firstOrCreate(
            ['instance_id' => $instance->id],
            ['saphir_margin_rate' => 0.13, 'codifarm_buy_rate' => 0.20, 'debt_share' => 0.3333, 'codifarm_share' => 0.3333, 'saphir_share' => 0.3334]
        );
        return view('eshop360::codifarm.config', compact('config'));
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'saphir_margin_rate' => 'required|numeric|min:0|max:1',
            'codifarm_buy_rate' => 'required|numeric|min:0|max:1',
            'debt_share' => 'required|numeric|min:0|max:1',
            'codifarm_share' => 'required|numeric|min:0|max:1',
            'saphir_share' => 'required|numeric|min:0|max:1',
        ]);
        $instance = CurrentInstance::get();
        CodifarmMarginConfig::updateOrCreate(
            ['instance_id' => $instance->id],
            $validated
        );
        return redirect()->back()->with('success', 'Configuration updated.');
    }

    public function margins(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $fromDateTime = $from . ' 00:00:00';
        $toDateTime = $to . ' 23:59:59';
        $summary = $this->codifarmSummary($instance->id, $fromDateTime, $toDateTime);
        $logs = CodifarmMarginLog::where('instance_id', $instance->id)
            ->whereBetween('created_at', [$fromDateTime, $toDateTime])
            ->with('order.customer')
            ->latest()
            ->paginate(20);
        return view('eshop360::codifarm.margins', compact('summary', 'logs', 'from', 'to'));
    }

    public function orders(Request $request)
    {
        $instance = CurrentInstance::get();
        $orders = Order::where('instance_id', $instance->id)
            ->where('is_codifarm', true)
            ->with('customer', 'items', 'codifarmMarginLog')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(function ($searchQuery) use ($search) {
                $searchQuery->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"));
            }))
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(20);
        return view('eshop360::codifarm.orders', compact('orders'));
    }

    private function codifarmSummary(int $instanceId, ?string $from = null, ?string $to = null): array
    {
        $query = CodifarmMarginLog::where('instance_id', $instanceId);

        if ($from !== null) {
            $query->where('created_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('created_at', '<=', $to);
        }

        return [
            'total_margin' => (float) $query->sum('total_margin'),
            'debt' => (float) $query->sum('debt_part'),
            'codifarm_part' => (float) $query->sum('codifarm_part'),
            'saphir_part' => (float) $query->sum('saphir_part'),
            'count' => (int) $query->count(),
        ];
    }
}
