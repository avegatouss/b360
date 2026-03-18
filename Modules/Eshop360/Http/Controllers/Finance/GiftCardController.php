<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\GiftCard;
use Modules\Eshop360\Models\GiftCardTopup;
use Modules\Eshop360\Services\FinanceService;
use Modules\Core\Support\CurrentInstance;

class GiftCardController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $giftCards = GiftCard::where('instance_id', $instance->id)->latest()->paginate(20);
        return view('eshop360::finance.gift-cards.index', compact('giftCards'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'expiry_date' => 'nullable|date|after:today',
        ]);
        $card = $this->financeService->createGiftCard(
            CurrentInstance::get()->id,
            $validated['amount'],
            $validated['expiry_date'] ?? null,
            auth()->id()
        );
        return redirect()->back()->with('success', "Gift card created: {$card->code}");
    }

    public function topup(Request $request, string $slug, GiftCard $giftCard)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:1']);

        DB::transaction(function () use ($validated, $giftCard) {
            GiftCardTopup::create([
                'gift_card_id' => $giftCard->id,
                'amount' => $validated['amount'],
                'user_id' => auth()->id(),
            ]);
            $giftCard->increment('balance', $validated['amount']);
            if ($giftCard->status === 'depleted') {
                $giftCard->update(['status' => 'active']);
            }
        });

        return redirect()->back()->with('success', 'Gift card topped up.');
    }

    public function disable(string $slug, GiftCard $giftCard)
    {
        $giftCard->update(['status' => 'disabled']);

        return redirect()->back()->with('success', 'Gift card disabled.');
    }

    public function destroy(string $slug, GiftCard $giftCard)
    {
        $giftCard->delete();
        return redirect()->back()->with('success', 'Gift card deleted.');
    }
}
