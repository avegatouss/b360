<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\InstallmentPayment;
use Modules\Eshop360\Models\InstallmentPlan;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\OrderService;

class InstallmentController extends Controller
{
    public function __construct(
        private FinanceService $financeService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = InstallmentPlan::where('instance_id', $instance->id)
            ->with('order.customer', 'payments')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->frequency, fn ($q, $f) => $q->where('frequency', $f))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->whereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%")));
            }));

        $fq = clone $query;
        $allPlanIds = (clone $fq)->pluck('id');
        $activePlanIds = (clone $fq)->where('status', 'active')->pluck('id');

        $kpi = (object) [
            'total'          => (clone $fq)->count(),
            'active'         => (clone $fq)->where('status', 'active')->count(),
            'completed'      => (clone $fq)->where('status', 'completed')->count(),
            'total_amount'   => (int) (clone $fq)->sum('total'),
            'total_paid'     => (int) InstallmentPayment::whereIn('plan_id', $allPlanIds)->where('status', 'paid')->sum('amount'),
            'overdue_count'  => InstallmentPayment::whereIn('plan_id', $activePlanIds)->where('status', '!=', 'paid')->where('due_date', '<', now())->count(),
        ];
        $kpi->total_remaining = $kpi->total_amount - $kpi->total_paid;

        $plans = $query->latest()->paginate(20)->withQueryString();

        $orders = Order::where('instance_id', $instance->id)
            ->with('customer')
            ->where('due_amount', '>', 0)
            ->whereDoesntHave('installmentPlan')
            ->whereIn('status', ['completed', 'pending', 'processing'])
            ->latest()
            ->limit(50)
            ->get();

        return view('eshop360::finance.installments.index', compact('plans', 'kpi', 'orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'           => 'required|exists:eshop_orders,id',
            'installments_count' => 'required|integer|min:2|max:60',
            'frequency'          => 'required|in:weekly,biweekly,monthly',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        if ($order->installmentPlan) {
            return redirect()->back()->with('error', __('Cette commande a deja un echeancier.'));
        }

        $this->financeService->createInstallmentPlan($order, $validated['installments_count'], $validated['frequency']);

        return redirect()->back()->with('success', __('Echeancier cree avec succes.'));
    }

    public function show(string $slug, InstallmentPlan $plan)
    {
        $plan->load('order.customer', 'payments');
        return view('eshop360::finance.installments.show', compact('plan'));
    }

    public function recordPayment(Request $request, string $slug, InstallmentPayment $payment)
    {
        if ($payment->status === 'paid') {
            return redirect()->back()->with('error', __('Ce paiement est deja enregistre.'));
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['paid_at' => now(), 'status' => 'paid']);

            $plan = $payment->plan()->with('order.payments')->first();
            $order = $plan?->order;

            if ($order && ! $order->payments()->where('reference', 'INST-' . $payment->id)->exists()) {
                $order->payments()->create([
                    'instance_id' => $order->instance_id,
                    'amount'      => $payment->amount,
                    'method'      => 'installment',
                    'reference'   => 'INST-' . $payment->id,
                    'status'      => 'completed',
                    'notes'       => __('Echeance #:num', ['num' => $payment->id]),
                    'received_by' => auth()->id(),
                ]);

                $totalPaid = (float) $order->payments()->where('status', 'completed')->sum('amount');
                $order->update([
                    'paid_amount'    => $totalPaid,
                    'due_amount'     => max(0, (float) $order->total - $totalPaid),
                    'payment_status' => $totalPaid >= (float) $order->total ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
                ]);
            }

            if ($plan) {
                $allPaid = $plan->payments()->where('status', '!=', 'paid')->doesntExist();
                $plan->update(['status' => $allPaid ? 'completed' : 'active']);
            }
        });

        return redirect()->back()->with('success', __('Paiement enregistre.'));
    }
}
