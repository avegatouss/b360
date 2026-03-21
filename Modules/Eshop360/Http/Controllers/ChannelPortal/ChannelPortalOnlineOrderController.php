<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Services\OnlineOrderService;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Services\Payment\Drivers\WalletDriver;
use Modules\Eshop360\Services\StockService;

class ChannelPortalOnlineOrderController extends Controller
{
    public function __construct(private OnlineOrderService $onlineOrderService) {}

    public function index(Request $request)
    {
        $channel = $request->resolved_channel;

        $orders = OnlineOrder::where('channel_id', $channel->id)
            ->with('customer', 'items.product')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('reference', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('eshop360::channel-portal.online-orders.index', compact('channel', 'orders'));
    }

    public function show(Request $request, $channel, OnlineOrder $onlineOrder)
    {
        $channel = $request->resolved_channel;
        abort_unless((int) $onlineOrder->channel_id === (int) $channel->id, 404);

        $onlineOrder->load('customer', 'items.product');

        return view('eshop360::channel-portal.online-orders.show', compact('channel', 'onlineOrder'));
    }

    public function updateStatus(Request $request, $channel, OnlineOrder $onlineOrder)
    {
        $channel = $request->resolved_channel;
        abort_unless((int) $onlineOrder->channel_id === (int) $channel->id, 404);

        $validated = $request->validate([
            'status' => 'required|in:validated,preparing,prepared,shipping,delivered,received,invoiced,cancelled',
        ]);

        $newStatus = $validated['status'];

        try {
            // Validation = confirm + debit wallet
            if ($newStatus === 'validated' && $onlineOrder->status === 'pending_validation') {
                $this->debitCustomerWallet($onlineOrder);
            }

            // Cancellation = refund if already debited
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
                        $channel->warehouse_id,
                        -$item->quantity,
                        'out',
                        "Online order #{$onlineOrder->reference} (canal: {$channel->name})",
                        auth()->id(),
                    );
                }
            }

            // Invoiced = convert to regular order
            if ($newStatus === 'invoiced') {
                $orderService = app(OrderService::class);
                $order = $this->onlineOrderService->convertToOrder($onlineOrder, $orderService);

                $order->update([
                    'channel_id' => $channel->id,
                    'warehouse_id' => $channel->warehouse_id,
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

            return redirect()->back()->with('success', __('Statut mis a jour.'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function debitCustomerWallet(OnlineOrder $onlineOrder): void
    {
        $customer = $onlineOrder->customer;
        if (! $customer) {
            throw new \RuntimeException(__('Client introuvable.'));
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

        $wallet = new WalletDriver();
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

        $txn = \Modules\Eshop360\Models\CustomerTransaction::where('customer_id', $customer->id)
            ->where('type', 'debit')
            ->where('description', 'like', "%{$onlineOrder->reference}%")
            ->first();

        $wallet = new WalletDriver();
        $wallet->refund(
            $txn?->reference ?? 'WALLET-' . $customer->id . '-MANUAL',
            (float) $onlineOrder->total,
        );
    }
}
