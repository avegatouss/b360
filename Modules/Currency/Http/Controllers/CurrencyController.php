<?php

namespace Modules\Currency\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Modules\Core\Support\CurrentInstance;
use Modules\Currency\Models\Currency;

final class CurrencyController extends Controller
{
    /**
     * List all currencies.
     */
    public function index(string $slug)
    {
        $instance = CurrentInstance::get();
        $currencies = Currency::orderByDesc('is_default')->orderBy('code')->get();

        return view('currency::index', compact('instance', 'currencies'));
    }

    /**
     * Store a new currency.
     */
    public function store(Request $request, string $slug)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:currencies,code'],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:10'],
            'decimals' => ['required', 'integer', 'min:0', 'max:4'],
            'rate' => ['required', 'numeric', 'min:0'],
            'auto_update' => ['sometimes', 'boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['auto_update'] = $request->has('auto_update');
        $validated['is_active'] = true;

        Currency::create($validated);

        return back()->with('status', "Devise {$validated['code']} ajoutee.");
    }

    /**
     * Update a currency.
     */
    public function update(Request $request, string $slug, int $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:10'],
            'decimals' => ['required', 'integer', 'min:0', 'max:4'],
            'rate' => ['required', 'numeric', 'min:0'],
            'auto_update' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['auto_update'] = $request->has('auto_update');
        $validated['is_active'] = $request->has('is_active');

        $currency->update($validated);

        return back()->with('status', "Devise {$currency->code} mise a jour.");
    }

    /**
     * Delete a currency.
     */
    public function destroy(string $slug, int $id)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->is_default) {
            return back()->with('error', 'Impossible de supprimer la devise par defaut.');
        }

        $code = $currency->code;
        $currency->delete();

        return back()->with('status', "Devise {$code} supprimee.");
    }

    /**
     * Set a currency as default.
     */
    public function setDefault(string $slug, int $id)
    {
        $currency = Currency::findOrFail($id);

        // Unset all others
        Currency::where('is_default', true)->update(['is_default' => false]);

        $currency->update(['is_default' => true, 'rate' => 1.000000]);

        return back()->with('status', "{$currency->code} definie comme devise par defaut.");
    }

    /**
     * Manually trigger rate update from API.
     */
    public function updateRates(string $slug)
    {
        try {
            $default = Currency::where('is_default', true)->first();
            $baseCurrency = $default ? $default->code : 'USD';

            $response = Http::timeout(10)->get("https://open.er-api.com/v6/latest/{$baseCurrency}");

            if (!$response->successful()) {
                return back()->with('error', 'Impossible de recuperer les taux de change.');
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];

            $updated = 0;
            Currency::where('is_default', false)->where('auto_update', true)->each(function ($currency) use ($rates, &$updated) {
                if (isset($rates[$currency->code])) {
                    $currency->update([
                        'rate' => $rates[$currency->code],
                        'rate_updated_at' => now(),
                    ]);
                    $updated++;
                }
            });

            return back()->with('status', "{$updated} taux de change mis a jour depuis l'API.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur lors de la mise a jour : ' . $e->getMessage());
        }
    }
}
