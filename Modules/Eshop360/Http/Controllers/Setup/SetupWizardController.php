<?php

namespace Modules\Eshop360\Http\Controllers\Setup;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Services\EshopInitializer;
use Modules\Eshop360\Services\EshopSettingsService;

final class SetupWizardController extends Controller
{
    public function __construct(
        private readonly EshopInitializer $initializer,
        private readonly EshopSettingsService $settings,
    ) {}

    public function hub(): View|RedirectResponse
    {
        $instance = CurrentInstance::get();

        if ($this->initializer->isInitialized($instance->id)) {
            return redirect()->route('eshop360.nav.home', $instance->slug);
        }

        $defaults = [
            'name' => 'Saphir Plus',
            'code' => 'SAPHIR',
            'theme_color' => '#4f46e5',
        ];

        $sessionData = session('eshop360_setup.hub', []);
        $hub = array_merge($defaults, $sessionData);
        $featureDefaults = $this->settings->defaults('features');

        return view('eshop360::setup.hub', compact('hub', 'featureDefaults', 'instance'));
    }

    public function storeHub(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'theme_color' => 'required|string|max:7',
            'features' => 'nullable|array',
            'features.*' => 'in:0,1',
        ]);

        session(['eshop360_setup.hub' => $validated]);

        return redirect()->route('eshop360.setup.channels', $instance->slug);
    }

    public function channels(): View|RedirectResponse
    {
        $instance = CurrentInstance::get();

        if ($this->initializer->isInitialized($instance->id)) {
            return redirect()->route('eshop360.nav.home', $instance->slug);
        }

        if (!session()->has('eshop360_setup.hub')) {
            return redirect()->route('eshop360.setup.hub', $instance->slug);
        }

        $channels = session('eshop360_setup.channels', []);
        $featureDefaults = $this->settings->defaults('features');

        return view('eshop360::setup.channels', compact('channels', 'featureDefaults', 'instance'));
    }

    public function storeChannels(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'channels' => 'nullable|array',
            'channels.*.name' => 'required|string|max:255',
            'channels.*.code' => 'required|string|max:20',
            'channels.*.theme_color' => 'required|string|max:7',
            'channels.*.margin_rate' => 'required|numeric|min:0|max:1',
            'channels.*.features' => 'nullable|array',
            'channels.*.features.*' => 'in:0,1',
        ]);

        session(['eshop360_setup.channels' => $validated['channels'] ?? []]);

        return redirect()->route('eshop360.setup.settings', $instance->slug);
    }

    public function settings(): View|RedirectResponse
    {
        $instance = CurrentInstance::get();

        if ($this->initializer->isInitialized($instance->id)) {
            return redirect()->route('eshop360.nav.home', $instance->slug);
        }

        if (!session()->has('eshop360_setup.hub')) {
            return redirect()->route('eshop360.setup.hub', $instance->slug);
        }

        $sessionData = session('eshop360_setup.settings', []);
        $defaults = [
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'tax_number' => '',
            'currency_symbol' => 'FCFA',
            'pos_layout' => 'layout1',
            'payment_methods' => ['cash', 'card'],
        ];
        $settings = array_merge($defaults, $sessionData);

        return view('eshop360::setup.settings', compact('settings', 'instance'));
    }

    public function storeSettings(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $user = $request->user();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'currency_symbol' => 'nullable|string|max:10',
            'pos_layout' => 'required|in:layout1,layout2,layout3,layout4,layout5',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'string|in:cash,card,cheque,bank_transfer',
        ]);

        $hubData = session('eshop360_setup.hub');
        $channelsData = session('eshop360_setup.channels', []);

        $hubData['features'] = collect($hubData['features'] ?? [])
            ->map(fn ($v) => (bool) $v)->all();

        foreach ($channelsData as &$ch) {
            $ch['features'] = collect($ch['features'] ?? [])
                ->map(fn ($v) => (bool) $v)->all();
            $ch['margin_rate'] = (float) $ch['margin_rate'];
        }
        unset($ch);

        $this->initializer->initialize(
            instanceId: $instance->id,
            hubData: $hubData,
            channels: $channelsData,
            baseSettings: $validated,
            admin: $user,
        );

        session()->forget('eshop360_setup');

        return redirect()->route('eshop360.nav.home', $instance->slug)
            ->with('success', __('Votre espace eShop est pret !'));
    }
}
