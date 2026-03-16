<?php

namespace Modules\Eshop360\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\InstallmentPlan;
use Modules\Eshop360\Services\ReportService;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\ChargesService;
use Modules\Eshop360\Services\MarginService;
use Modules\Core\Support\CurrentInstance;

class AdvancedReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private FinanceService $financeService,
    ) {}

    public function overview(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->overview($instance->id, $from, $to);
        return view('eshop360::reports.overview', compact('data', 'from', 'to'));
    }

    public function cashbook(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->cashbook($instance->id, $from, $to);
        return view('eshop360::reports.cashbook', compact('data', 'from', 'to'));
    }

    public function profitLoss(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->financeService->profitAndLoss($instance->id, $from, $to);
        return view('eshop360::reports.profit-loss', compact('data', 'from', 'to'));
    }

    public function salesByCategory(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->salesByCategory($instance->id, $from, $to);
        return view('eshop360::reports.sales-by-category', compact('data', 'from', 'to'));
    }

    public function salesByProduct(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->salesByProduct($instance->id, $from, $to);
        return view('eshop360::reports.sales-by-product', compact('data', 'from', 'to'));
    }

    public function taxReport(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->taxReport($instance->id, $from, $to);
        return view('eshop360::reports.tax', compact('data', 'from', 'to'));
    }

    public function customerDues()
    {
        $instance = CurrentInstance::get();
        $data = $this->reportService->customerDues($instance->id);
        return view('eshop360::reports.customer-dues', compact('data'));
    }

    public function supplierDues()
    {
        $instance = CurrentInstance::get();
        $data = $this->reportService->supplierDues($instance->id);
        return view('eshop360::reports.supplier-dues', compact('data'));
    }

    public function employeeCommissions(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->employeeCommissions($instance->id, $from, $to);
        return view('eshop360::reports.commissions', compact('data', 'from', 'to'));
    }

    public function posOverview(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->posOverview($instance->id, $from, $to);
        return view('eshop360::reports.pos-overview', compact('data', 'from', 'to'));
    }

    public function monthlyRevenue(Request $request)
    {
        $instance = CurrentInstance::get();
        $year = $request->get('year', now()->year);
        $data = $this->reportService->monthlyRevenue($instance->id, $year);
        return view('eshop360::reports.monthly-revenue', compact('data', 'year'));
    }

    public function monthlyExpenses(Request $request)
    {
        $instance = CurrentInstance::get();
        $year = $request->get('year', now()->year);
        $data = $this->reportService->monthlyExpenses($instance->id, $year);
        return view('eshop360::reports.monthly-expenses', compact('data', 'year'));
    }

    public function stockReport(Request $request)
    {
        $instance = CurrentInstance::get();
        $warehouseId = $request->get('warehouse_id');
        $data = $this->reportService->stockReport($instance->id, $warehouseId);
        return view('eshop360::reports.stock', compact('data'));
    }

    public function channelsReport(Request $request)
    {
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $marginService = app(MarginService::class);
        $channels = \Modules\Eshop360\Models\DistributionChannel::where('instance_id', $instance->id)->get();
        $data = [];
        foreach ($channels as $channel) {
            $data[] = [
                'channel' => $channel,
                'summary' => $marginService->getMarginSummary($instance->id, $from, $to, $channel->id),
            ];
        }
        $totals = $marginService->getMarginSummary($instance->id, $from, $to);
        return view('eshop360::reports.channels', compact('data', 'totals', 'from', 'to'));
    }

    public function chargesReport()
    {
        $instance = CurrentInstance::get();
        $chargesService = app(ChargesService::class);
        $data = $chargesService->getDashboardData($instance->id);
        return view('eshop360::reports.charges', compact('data'));
    }

    public function installmentsOverview()
    {
        $instance = CurrentInstance::get();
        $plans = InstallmentPlan::where('instance_id', $instance->id)
            ->with('order.customer', 'payments')
            ->get();
        return view('eshop360::reports.installments', compact('plans'));
    }
}
