<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\InstallmentPlan;
use Modules\Eshop360\Models\InstallmentPayment;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Services\FinanceService;
use Modules\Eshop360\Services\OrderService;
use Modules\Core\Support\CurrentInstance;

class InstallmentController extends Controller
{
    public function __construct(
        private FinanceService $financeService,
        private OrderService $orderService,
    ) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $plans = InstallmentPlan::where('instance_id', $instance->id)
            ->with('order.customer', 'payments')
            ->latest()
            ->paginate(20);
        return view('eshop360::finance.installments.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:eshop_orders,id',
            'installments_count' => 'required|integer|min:2|max:60',
            'frequency' => 'required|in:weekly,biweekly,monthly',
        ]);
        $order = Order::findOrFail($validated['order_id']);
        $this->financeService->createInstallmentPlan($order, $validated['installments_count'], $validated['frequency']);
        return redirect()->back()->with('success', 'Installment plan created.');
    }

    public function show(string $slug, InstallmentPlan $plan)
    {
        $plan->load('order.customer', 'payments');
        return view('eshop360::finance.installments.show', compact('plan'));
    }

    public function recordPayment(Request $request, string $slug, InstallmentPayment $payment)
    {
        if ($payment->status === 'paid') {
            return redirect()->back()->with('success', 'Payment already recorded.');
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'paid_at' => now(),
                'status' => 'paid',
            ]);

            $plan = $payment->plan()->with('order.payments')->first();
            $order = $plan?->order;

            if ($order && !$order->payments()->where('reference', 'INST-' . $payment->id)->exists()) {
                $order->payments()->create([
                    'instance_id' => $order->instance_id,
                    'amount' => $payment->amount,
                    'method' => 'installment',
                    'reference' => 'INST-' . $payment->id,
                    'status' => 'completed',
                    'notes' => 'Installment payment #' . $payment->id,
                    'received_by' => auth()->id(),
                ]);

                $this->orderService->calculateTotals($order->fresh());
            }

            if (!$plan) {
                return;
            }

            $allPaid = $plan->payments()->where('status', '!=', 'paid')->doesntExist();
            $plan->update([
                'status' => $allPaid ? 'completed' : 'active',
            ]);
        });

        return redirect()->back()->with('success', 'Payment recorded.');
    }
}
