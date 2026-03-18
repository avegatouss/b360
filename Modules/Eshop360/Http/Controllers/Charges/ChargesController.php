<?php

namespace Modules\Eshop360\Http\Controllers\Charges;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\CompanyCharge;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Services\ChargesService;
use Modules\Core\Support\CurrentInstance;

class ChargesController extends Controller
{
    public function __construct(private ChargesService $chargesService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $charges = CompanyCharge::where('instance_id', $instance->id)->get();
        $dashboardData = $this->chargesService->getDashboardData($instance->id);
        return view('eshop360::charges.index', compact('charges', 'dashboardData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:rent,electricity,salary,transport,maintenance,insurance,other',
            'amount_monthly' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        CompanyCharge::create($validated);
        return redirect()->back()->with('success', 'Charge added.');
    }

    public function update(Request $request, string $slug, CompanyCharge $charge)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:rent,electricity,salary,transport,maintenance,insurance,other',
            'amount_monthly' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $charge->update($validated);
        return redirect()->back()->with('success', 'Charge updated.');
    }

    public function destroy(string $slug, CompanyCharge $charge)
    {
        $charge->delete();
        return redirect()->back()->with('success', 'Charge deleted.');
    }

    /**
     * API endpoint for real-time dashboard counter
     */
    public function realtime()
    {
        $instance = CurrentInstance::get();
        return response()->json($this->chargesService->getDashboardData($instance->id));
    }

    /**
     * Real-time data endpoint — structured for the charges widget.
     * Returns total_per_second, accumulated_this_month, breakdown array.
     */
    public function realtimeData()
    {
        $instance = CurrentInstance::get();
        $data = $this->chargesService->getDashboardData($instance->id);

        // Reshape breakdown into flat array for the widget
        $breakdown = [];
        foreach ($data['breakdown'] ?? [] as $category => $info) {
            $breakdown[] = [
                'category' => ucfirst($category),
                'monthly' => $info['monthly_total'],
                'per_second' => $info['cost_per_second'],
                'accumulated' => $info['accumulated'],
            ];
        }

        return response()->json([
            'total_per_second' => $data['cost_per_second'],
            'accumulated_this_month' => $data['accumulated_since_month_start'],
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Dashboard: charge allocation per product sold — real-time cost absorption.
     */
    public function costAbsorption(Request $request)
    {
        $instance = CurrentInstance::get();
        $instanceId = $instance->id;
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        // Total charges for the period (prorated monthly)
        $charges = CompanyCharge::where('instance_id', $instanceId)->where('is_active', true)->get();
        $daysInPeriod = max(1, now()->parse($dateFrom)->diffInDays(now()->parse($dateTo)) + 1);
        $totalChargesForPeriod = $charges->sum(fn ($c) => round(($c->amount_monthly / 30) * $daysInPeriod, 2));

        // Total units sold in period
        $salesData = DB::table('eshop_order_items as oi')
            ->join('eshop_orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.instance_id', $instanceId)
            ->where('o.status', '!=', 'cancelled')
            ->whereBetween('o.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select(
                'oi.product_id', 'oi.product_name',
                DB::raw('SUM(oi.quantity) as qty_sold'),
                DB::raw('SUM(oi.total) as revenue'),
            )
            ->groupBy('oi.product_id', 'oi.product_name')
            ->orderByDesc('qty_sold')
            ->get();

        $totalUnitsSold = $salesData->sum('qty_sold');
        $totalRevenue = $salesData->sum('revenue');
        $chargePerUnit = $totalUnitsSold > 0 ? round($totalChargesForPeriod / $totalUnitsSold, 2) : 0;
        $chargeAbsorbed = $chargePerUnit * $totalUnitsSold;
        $absorptionRate = $totalRevenue > 0 ? round(($totalChargesForPeriod / $totalRevenue) * 100, 2) : 0;

        // Per-product breakdown
        $products = $salesData->map(function ($row) use ($chargePerUnit, $totalRevenue, $totalUnitsSold) {
            $chargeAllocated = round($chargePerUnit * $row->qty_sold, 2);
            $margin = round($row->revenue - $chargeAllocated, 2);
            return (object) [
                'product_id' => $row->product_id,
                'name' => $row->product_name,
                'qty_sold' => (int) $row->qty_sold,
                'revenue' => round((float) $row->revenue, 2),
                'charge_allocated' => $chargeAllocated,
                'margin_after_charges' => $margin,
                'charge_pct' => $row->revenue > 0 ? round(($chargeAllocated / $row->revenue) * 100, 1) : 0,
            ];
        });

        // Charge breakdown per category for the period
        $chargeBreakdown = $charges->groupBy('category')->map(fn ($group, $cat) => [
            'category' => $cat,
            'monthly' => $group->sum('amount_monthly'),
            'period_total' => round($group->sum(fn ($c) => ($c->amount_monthly / 30) * $daysInPeriod), 2),
            'per_unit' => $totalUnitsSold > 0 ? round($group->sum(fn ($c) => ($c->amount_monthly / 30) * $daysInPeriod) / $totalUnitsSold, 2) : 0,
        ])->values();

        return view('eshop360::charges.cost-absorption', compact(
            'charges', 'products', 'chargeBreakdown',
            'totalChargesForPeriod', 'totalUnitsSold', 'totalRevenue',
            'chargePerUnit', 'chargeAbsorbed', 'absorptionRate',
            'dateFrom', 'dateTo', 'daysInPeriod',
        ));
    }
}
