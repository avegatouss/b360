<?php

namespace Modules\Eshop360\Http\Controllers\OnlineOrder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Sales\Models\OnlineOrder;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\Payment\Drivers\WalletDriver;
use Modules\Eshop360\Services\StockService;

class OnlineOrderController extends Controller
{
    public function __construct(
        private OnlineOrderService $onlineOrderService,
    ) {}

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $channelFilter = $request->integer('channel_id') ?: null;

        $query = OnlineOrder::where('eshop_online_orders.instance_id', $instance->id)
            ->with('customer', 'channel')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->customer_id, fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('eshop_online_orders.created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('eshop_online_orders.created_at', '<=', $d))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('reference', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            }));

        // Optional UI channel filter
        $query->when($channelFilter, fn ($q) => $q->where('channel_id', $channelFilter));

        // KPIs from filtered query
        $statsQuery = clone $query;
        $kpi = (object) [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending_validation')->count(),
            'validated' => (clone $statsQuery)->whereIn('status', ['validated', 'preparing', 'prepared'])->count(),
            'shipping' => (clone $statsQuery)->whereIn('status', ['shipping', 'delivered'])->count(),
            'completed' => (clone $statsQuery)->whereIn('status', ['received', 'invoiced'])->count(),
            'cancelled' => (clone $statsQuery)->where('status', 'cancelled')->count(),
            'revenue' => round((float) (clone $statsQuery)->whereNotIn('status', ['cancelled'])->sum('total'), 0),
            'avg' => round((float) (clone $statsQuery)->whereNotIn('status', ['cancelled'])->avg('total'), 0),
        ];

        $orders = $query->latest()->paginate(25)->withQueryString();

        // Filter lookups
        $customers = \Modules\Eshop360\Domain\CRM\Models\Customer::where('instance_id', $instance->id)
            ->whereHas('onlineOrders')
            ->orderBy('name')->get(['id', 'name', 'code']);

        $channels = \Modules\Eshop360\Domain\Channel\Models\DistributionChannel::where('instance_id', $instance->id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('eshop360::online-orders.index', compact('orders', 'kpi', 'customers', 'channels'));
    }

    public function show(string $slug, OnlineOrder $onlineOrder)
    {
        $onlineOrder->load('customer', 'channel', 'items.product');

        return view('eshop360::online-orders.show', compact('onlineOrder'));
    }

    public function updateStatus(Request $request, string $slug, OnlineOrder $onlineOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:validated,preparing,prepared,shipping,delivered,received,invoiced,cancelled',
        ]);

        $newStatus = $validated['status'];

        try {
            // Validation = confirm order + debit wallet
            if ($newStatus === 'validated' && $onlineOrder->status === 'pending_validation') {
                $this->debitCustomerWallet($onlineOrder);
            }

            // Cancellation = refund wallet if already debited
            if ($newStatus === 'cancelled' && $onlineOrder->confirmed_at) {
                $this->refundCustomerWallet($onlineOrder);
            }

            $this->onlineOrderService->advanceStatus($onlineOrder, $newStatus);

            // Prepared = deduct stock
            if ($newStatus === 'prepared') {
                $stockService = app(StockService::class);
                foreach ($onlineOrder->items as $item) {
                    $stockService->adjustStock(
                        $item->product,
                        null,
                        -$item->quantity,
                        'out',
                        "Online order #{$onlineOrder->reference}",
                        auth()->id(),
                    );
                }
            }

            // Invoiced = convert to regular order + create invoice
            if ($newStatus === 'invoiced') {
                $orderService = app(OrderService::class);
                $order = $this->onlineOrderService->convertToOrder($onlineOrder, $orderService);

                // Mark as paid since wallet was already debited
                $order->update([
                    'payment_method' => 'wallet',
                    'paid_amount' => $order->total,
                    'due_amount' => 0,
                    'payment_status' => 'paid',
                ]);

                $order->loadMissing('invoice', 'items');
                if (! $order->invoice) {
                    app(InvoiceService::class)->createFromOrder($order);
                }
            }

            $statusLabels = [
                'validated' => 'Commande confirmee et wallet debite.',
                'preparing' => 'Commande en preparation.',
                'prepared' => 'Commande preparee, stock deduit.',
                'shipping' => 'Commande expediee.',
                'delivered' => 'Commande livree.',
                'received' => 'Reception confirmee.',
                'invoiced' => 'Commande facturee.',
                'cancelled' => 'Commande annulee.',
            ];

            return redirect()->back()->with('success', __($statusLabels[$newStatus] ?? 'Statut mis a jour.'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $slug, OnlineOrder $onlineOrder)
    {
        if ($onlineOrder->status !== 'pending_validation') {
            return redirect()->back()->with('error', __('Seules les commandes en attente peuvent etre supprimees.'));
        }
        $onlineOrder->delete();

        return redirect()->route('eshop360.online-orders.index', $slug)
            ->with('success', __('Commande supprimee.'));
    }

    /**
     * Fast-track: validate + all steps through delivered in one action.
     */
    public function fastDeliver(string $slug, OnlineOrder $onlineOrder)
    {
        if (! in_array($onlineOrder->status, ['pending_validation', 'validated', 'preparing', 'prepared', 'shipping'])) {
            return redirect()->back()->with('error', __('Cette commande ne peut pas etre traitee en livraison rapide.'));
        }

        try {
            $stepsToDeliver = ['validated', 'preparing', 'prepared', 'shipping', 'delivered'];
            $this->runFastSteps($onlineOrder, $stepsToDeliver);

            return redirect()->back()->with('success', __('Commande validee et livree avec succes.'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Fast-track: validate + all steps through received in one action.
     */
    public function fastComplete(string $slug, OnlineOrder $onlineOrder)
    {
        if (! in_array($onlineOrder->status, ['pending_validation', 'validated', 'preparing', 'prepared', 'shipping', 'delivered'])) {
            return redirect()->back()->with('error', __('Cette commande ne peut pas etre traitee en completion rapide.'));
        }

        try {
            $stepsToReceived = ['validated', 'preparing', 'prepared', 'shipping', 'delivered', 'received'];
            $this->runFastSteps($onlineOrder, $stepsToReceived);

            return redirect()->back()->with('success', __('Commande validee, livree et reception confirmee.'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function runFastSteps(OnlineOrder $onlineOrder, array $targetSteps): void
    {
        $allSteps = ['pending_validation', 'validated', 'preparing', 'prepared', 'shipping', 'delivered', 'received', 'invoiced'];
        $currentIdx = array_search($onlineOrder->status, $allSteps);

        foreach ($targetSteps as $step) {
            $stepIdx = array_search($step, $allSteps);
            if ($stepIdx <= $currentIdx) {
                continue; // already past this step
            }

            // Wallet debit on validation
            if ($step === 'validated' && $onlineOrder->status === 'pending_validation') {
                $this->debitCustomerWallet($onlineOrder);
            }

            $this->onlineOrderService->advanceStatus($onlineOrder, $step);
            $onlineOrder = $onlineOrder->fresh();

            // Deduct stock on prepared
            if ($step === 'prepared') {
                $onlineOrder->load('items.product');
                $stockService = app(StockService::class);
                foreach ($onlineOrder->items as $item) {
                    $stockService->adjustStock(
                        $item->product, null, -$item->quantity, 'out',
                        "Online order #{$onlineOrder->reference}", auth()->id(),
                    );
                }
            }
        }
    }

    private function debitCustomerWallet(OnlineOrder $onlineOrder): void
    {
        $customer = $onlineOrder->customer;
        if (! $customer) {
            throw new \RuntimeException(__('Client introuvable pour cette commande.'));
        }

        $amount = (float) $onlineOrder->total;

        if ((float) $customer->wallet_balance < $amount) {
            throw new \RuntimeException(
                __('Solde wallet insuffisant. Solde: :balance, Requis: :required', [
                    'balance' => number_format($customer->wallet_balance, 0, ',', ' '),
                    'required' => number_format($amount, 0, ',', ' '),
                ])
            );
        }

        $wallet = new WalletDriver;
        $result = $wallet->initiate($amount, 'XAF', [
            'customer_id' => $customer->id,
            'description' => "Commande en ligne #{$onlineOrder->reference}",
        ]);

        if (! $result['success']) {
            throw new \RuntimeException(__('Echec du debit wallet: :error', ['error' => $result['error'] ?? 'Unknown']));
        }
    }

    private function refundCustomerWallet(OnlineOrder $onlineOrder): void
    {
        $customer = $onlineOrder->customer;
        if (! $customer) {
            return;
        }

        $amount = (float) $onlineOrder->total;

        // Find the original wallet transaction to get its ID for refund
        $txn = \Modules\Eshop360\Domain\CRM\Models\CustomerTransaction::where('customer_id', $customer->id)
            ->where('type', 'debit')
            ->where('description', 'like', "%{$onlineOrder->reference}%")
            ->first();

        $wallet = new WalletDriver;
        $wallet->refund(
            $txn?->reference ?? 'WALLET-'.$customer->id.'-MANUAL',
            $amount,
        );
    }
}
