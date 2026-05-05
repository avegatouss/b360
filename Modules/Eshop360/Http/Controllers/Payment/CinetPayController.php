<?php

namespace Modules\Eshop360\Http\Controllers\Payment;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Services\CinetPayService;

class CinetPayController extends Controller
{
    public function __construct(private CinetPayService $cinetPay) {}

    public function initiate(Request $request, Order $order)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'payment_method' => 'nullable|in:mobile_money,card,orange_money,mtn_money',
            'return_url' => 'nullable|url',
        ]);

        if (! $this->cinetPay->isConfigured()) {
            return redirect()->back()->with('error', 'CinetPay n\'est pas configure.');
        }

        $result = $this->cinetPay->initiate(
            $order,
            $validated['payment_method'] ?? 'mobile_money',
            $validated
        );

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['error'] ?? 'Erreur de paiement.');
        }

        if (! empty($result['payment_url'])) {
            return redirect()->away($result['payment_url']);
        }

        return redirect()->back()->with([
            'success' => 'Paiement CinetPay initie.',
            'ussd_code' => $result['ussd_code'] ?? null,
            'transaction_id' => $result['transaction_id'] ?? null,
        ]);
    }

    public function callback(Request $request)
    {
        $result = $this->cinetPay->verifyCallback($request->all());

        if (! $result['valid']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['status' => 'ok', 'payment_status' => $result['status']]);
    }

    public function status(string $transactionId)
    {
        return response()->json($this->cinetPay->checkStatus($transactionId));
    }

    public function settings()
    {
        return view('eshop360::payment.cinetpay-settings', [
            'config' => config('eshop360.cinetpay', config('eshop360.inetpay', [])),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'merchant_id' => 'required|string',
            'secret_key' => 'required|string',
            'base_url' => 'nullable|url',
            'callback_url' => 'nullable|url',
        ]);

        return redirect()->back()->with('success', 'Parametres CinetPay sauvegardes.');
    }
}
