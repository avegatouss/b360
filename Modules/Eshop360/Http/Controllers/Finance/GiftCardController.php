<?php

namespace Modules\Eshop360\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\GiftCard;
use Modules\Eshop360\Models\GiftCardTopup;
class GiftCardController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = GiftCard::where('instance_id', $instance->id)
            ->with('customer', 'creator')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->customer_id, fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->batch_id, fn ($q, $b) => $q->where('batch_id', $b))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('code', 'like', "%{$s}%")->orWhere('customer_name', 'like', "%{$s}%");
            }))
            ->when($request->expired === '1', fn ($q) => $q->whereNotNull('expiry_date')->where('expiry_date', '<', now()));

        $fq = clone $query;
        $kpi = (object) [
            'total'         => (clone $fq)->count(),
            'active'        => (clone $fq)->where('status', 'active')->count(),
            'total_value'   => (int) (clone $fq)->sum('amount'),
            'total_balance' => (int) (clone $fq)->sum('balance'),
            'total_used'    => (int) ((clone $fq)->sum('amount') - (clone $fq)->sum('balance')),
            'depleted'      => (clone $fq)->where('status', 'depleted')->count(),
        ];

        $giftCards = $query->latest()->paginate(20)->withQueryString();
        $customers = Customer::where('instance_id', $instance->id)->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'code']);
        $batches = GiftCard::where('instance_id', $instance->id)->whereNotNull('batch_id')->distinct()->pluck('batch_id');
        $codeSettings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('gift_card_code');

        return view('eshop360::finance.gift-cards.index', compact('giftCards', 'kpi', 'customers', 'batches', 'codeSettings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:1',
            'customer_id' => 'nullable|exists:eshop_customers,id',
            'expiry_date' => 'nullable|date|after:today',
            'notes'       => 'nullable|string|max:1000',
            'code'        => 'nullable|string|max:50|unique:eshop_gift_cards,code',
        ]);

        $instance = CurrentInstance::get();
        $codeSettings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('gift_card_code');
        $code = ! empty($validated['code']) ? strtoupper($validated['code']) : GiftCard::generateCode($codeSettings);
        $customer = ! empty($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;

        GiftCard::create([
            'instance_id'   => $instance->id,
            'code'          => $code,
            'customer_id'   => $customer?->id,
            'customer_name' => $customer?->name,
            'amount'        => $validated['amount'],
            'balance'       => $validated['amount'],
            'status'        => 'active',
            'expiry_date'   => $validated['expiry_date'] ?? null,
            'notes'         => $validated['notes'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        return redirect()->back()->with('success', __('Carte cadeau creee: :code', ['code' => $code]));
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'quantity'    => 'required|integer|min:1|max:100',
            'amount'      => 'required|numeric|min:1',
            'customer_id' => 'nullable|exists:eshop_customers,id',
            'expiry_date' => 'nullable|date|after:today',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $instance = CurrentInstance::get();
        $codeSettings = app(\Modules\Eshop360\Services\EshopSettingsService::class)->get('gift_card_code');
        $batchId = 'LOT-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        $customer = ! empty($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;
        $created = 0;

        DB::transaction(function () use ($validated, $instance, $codeSettings, $batchId, $customer, &$created) {
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $code = GiftCard::generateCode($codeSettings);
                while (GiftCard::where('code', $code)->exists()) {
                    $code = GiftCard::generateCode($codeSettings);
                }
                GiftCard::create([
                    'instance_id'   => $instance->id,
                    'code'          => $code,
                    'customer_id'   => $customer?->id,
                    'customer_name' => $customer?->name,
                    'batch_id'      => $batchId,
                    'amount'        => $validated['amount'],
                    'balance'       => $validated['amount'],
                    'status'        => 'active',
                    'expiry_date'   => $validated['expiry_date'] ?? null,
                    'notes'         => $validated['notes'] ?? null,
                    'created_by'    => auth()->id(),
                ]);
                $created++;
            }
        });

        return redirect()->back()->with('success', __(':count cartes generees (lot: :batch)', ['count' => $created, 'batch' => $batchId]));
    }

    public function saveCodeSettings(Request $request)
    {
        $validated = $request->validate([
            'prefix'     => 'nullable|string|max:10',
            'length'     => 'required|integer|min:4|max:20',
            'separator'  => 'nullable|string|max:2',
            'group_size' => 'nullable|integer|min:0|max:10',
            'charset'    => 'required|in:alphanumeric,numeric,alpha',
        ]);

        app(\Modules\Eshop360\Services\EshopSettingsService::class)->set('gift_card_code', $validated);
        return redirect()->back()->with('success', __('Format de code enregistre.'));
    }

    public function topup(Request $request, string $slug, GiftCard $giftCard)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:1']);

        DB::transaction(function () use ($validated, $giftCard) {
            GiftCardTopup::create(['gift_card_id' => $giftCard->id, 'amount' => $validated['amount'], 'notes' => 'Rechargement', 'user_id' => auth()->id()]);
            $giftCard->increment('balance', $validated['amount']);
            $giftCard->increment('amount', $validated['amount']);
            if ($giftCard->status === 'depleted') {
                $giftCard->update(['status' => 'active']);
            }
        });

        return redirect()->back()->with('success', __('Carte rechargee.'));
    }

    public function disable(string $slug, GiftCard $giftCard)
    {
        $giftCard->update(['status' => 'disabled']);
        return redirect()->back()->with('success', __('Carte desactivee.'));
    }

    public function destroy(string $slug, GiftCard $giftCard)
    {
        $giftCard->delete();
        return redirect()->back()->with('success', __('Carte supprimee.'));
    }
}
