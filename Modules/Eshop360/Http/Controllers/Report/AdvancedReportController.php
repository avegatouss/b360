<?php

namespace Modules\Eshop360\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\InstallmentPlan;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\ReportService;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\ChargesService;
use Modules\Eshop360\Services\MarginService;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Support\CurrentChannel;

class AdvancedReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private FinanceService $financeService,
        private readonly ChannelAccessService $channelAccess,
    ) {}

    /**
     * Advanced reports are hub-level aggregations.
     * Non-hub users should not access them.
     */
    private function channelId(): ?int
    {
        return CurrentChannel::isScoped() ? CurrentChannel::id() : null;
    }

    private function ensureHubAccess(): void
    {
        abort_unless(
            $this->channelAccess->isHubAdmin(auth()->user()),
            403,
            __('Ces rapports sont reserves aux administrateurs.')
        );
    }

    public function overview(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->overview($instance->id, $from, $to, $this->channelId());
        return view('eshop360::reports.overview', compact('data', 'from', 'to'));
    }

    public function cashbook(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->cashbook($instance->id, $from, $to, $this->channelId());
        return view('eshop360::reports.cashbook', compact('data', 'from', 'to'));
    }

    public function profitLoss(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->financeService->profitAndLoss($instance->id, $from, $to);

        // Include real-time charges data for the "Charges imputees" section
        $chargesService = app(ChargesService::class);
        $chargesData = $chargesService->getDashboardData($instance->id);

        return view('eshop360::reports.profit-loss', compact('data', 'from', 'to', 'chargesData'));
    }

    public function salesByCategory(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->salesByCategory($instance->id, $from, $to, $this->channelId());
        return view('eshop360::reports.sales-by-category', compact('data', 'from', 'to'));
    }

    public function salesByProduct(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->salesByProduct($instance->id, $from, $to, $this->channelId());
        return view('eshop360::reports.sales-by-product', compact('data', 'from', 'to'));
    }

    public function taxReport(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->taxReport($instance->id, $from, $to, $this->channelId());
        return view('eshop360::reports.tax', compact('data', 'from', 'to'));
    }

    public function customerDues()
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $data = $this->reportService->customerDues($instance->id, $this->channelId());
        return view('eshop360::reports.customer-dues', compact('data'));
    }

    public function supplierDues()
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $data = $this->reportService->supplierDues($instance->id, $this->channelId());
        return view('eshop360::reports.supplier-dues', compact('data'));
    }

    public function employeeCommissions(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->employeeCommissions($instance->id, $from, $to, $this->channelId());
        return view('eshop360::reports.commissions', compact('data', 'from', 'to'));
    }

    public function posOverview(Request $request)
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $data = $this->reportService->posOverview($instance->id, $from, $to, $this->channelId());
        return view('eshop360::reports.pos-overview', compact('data', 'from', 'to'));
    }

    public function monthlyRevenue(Request $request)
    {
        $instance = CurrentInstance::get();
        $year = $request->get('year', now()->year);
        $data = $this->reportService->monthlyRevenue($instance->id, $year, $this->channelId());
        return view('eshop360::reports.monthly-revenue', compact('data', 'year'));
    }

    public function monthlyExpenses(Request $request)
    {
        $instance = CurrentInstance::get();
        $year = $request->get('year', now()->year);
        $data = $this->reportService->monthlyExpenses($instance->id, $year, $this->channelId());
        return view('eshop360::reports.monthly-expenses', compact('data', 'year'));
    }

    public function stockReport(Request $request)
    {
        $instance = CurrentInstance::get();
        $warehouseId = $request->get('warehouse_id');
        $data = $this->reportService->stockReport($instance->id, $warehouseId, $this->channelId());
        return view('eshop360::reports.stock', compact('data'));
    }

    public function channelsReport(Request $request)
    {
        $this->ensureHubAccess();
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
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $chargesService = app(ChargesService::class);
        $data = $chargesService->getDashboardData($instance->id);
        return view('eshop360::reports.charges', compact('data'));
    }

    public function installmentsOverview()
    {
        $this->ensureHubAccess();
        $instance = CurrentInstance::get();
        $plans = InstallmentPlan::where('instance_id', $instance->id)
            ->with('order.customer', 'payments')
            ->get();
        return view('eshop360::reports.installments', compact('plans'));
    }
}
