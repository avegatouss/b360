<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\CashRegister;
use Modules\Eshop360\Services\CashRegisterService;

class ChannelPortalCashRegisterController extends Controller
{
    public function __construct(private CashRegisterService $registerService) {}

    public function open(Request $request)
    {
        $channel = $request->resolved_channel;
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $this->registerService->open(
            $instance->id,
            (float) $validated['opening_amount'],
            null, // store_id
            $channel->id,
        );

        return back()->with('success', 'Caisse ouverte.');
    }

    public function close(Request $request, $channelParam, $registerId)
    {
        $channel = $request->resolved_channel;

        $register = CashRegister::where('channel_id', $channel->id)
            ->where('user_id', auth()->id())
            ->findOrFail($registerId);

        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
        ]);

        $this->registerService->close($register, (float) $validated['closing_amount']);

        return back()->with('success', 'Caisse fermée.');
    }
}
