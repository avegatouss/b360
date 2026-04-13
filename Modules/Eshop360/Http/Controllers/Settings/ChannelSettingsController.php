<?php

namespace Modules\Eshop360\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Support\CurrentChannel;

final class ChannelSettingsController extends Controller
{
    public function __construct(
        private readonly EshopSettingsService $settings,
    ) {}

    public function branding()
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();

        if (!$channel) {
            return redirect()->route('eshop360.nav.home', $instance->slug)
                ->with('error', __('Selectionnez un canal d\'abord.'));
        }

        $branding = $this->settings->getForChannel('channel_branding', $channel->id);

        return view('eshop360::channel-settings.branding', compact('branding', 'channel', 'instance'));
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();
        abort_unless($channel, 404);

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'invoice_header' => 'nullable|string|max:500',
            'invoice_footer' => 'nullable|string|max:500',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
            'currency_symbol' => 'nullable|string|max:10',
        ]);

        $this->settings->setForChannel('channel_branding', $validated, $channel->id);

        return redirect()->route('eshop360.channel-settings.branding', $instance->slug)
            ->with('success', __('Branding du canal mis a jour.'));
    }

    public function features()
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();

        if (!$channel) {
            return redirect()->route('eshop360.nav.home', $instance->slug)
                ->with('error', __('Selectionnez un canal d\'abord.'));
        }

        $features = $this->settings->getForChannel('features', $channel->id);
        $featureDefaults = $this->settings->defaults('features');

        return view('eshop360::channel-settings.features', compact('features', 'featureDefaults', 'channel', 'instance'));
    }

    public function updateFeatures(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $channel = CurrentChannel::get();
        abort_unless($channel, 404);

        $validated = $request->validate([
            'features' => 'required|array',
            'features.*' => 'in:0,1',
        ]);

        $features = collect($validated['features'])->map(fn ($v) => (bool) $v)->all();
        $this->settings->setChannelFeatures($features, $channel->id);

        return redirect()->route('eshop360.channel-settings.features', $instance->slug)
            ->with('success', __('Modules du canal mis a jour.'));
    }
}
