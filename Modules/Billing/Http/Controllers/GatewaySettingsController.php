<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Services\GatewayManager;
use Modules\Core\Support\CurrentInstance;

/**
 * Gateway configuration UI — enable/disable gateways, manage credentials.
 */
final class GatewaySettingsController extends Controller
{
    public function __construct(
        private readonly GatewayManager $gatewayManager,
    ) {}

    public function index(string $slug)
    {
        $instance = CurrentInstance::get();
        $gateways = $this->gatewayManager->all();

        $statuses = [];
        foreach ($gateways as $gw) {
            $statuses[$gw->id] = [
                'enabled' => $this->gatewayManager->isEnabled($gw->id, $instance->id),
                'configured' => false,
            ];

            try {
                $driver = $this->gatewayManager->resolve($gw->id, $instance->id);
                $statuses[$gw->id]['configured'] = $driver->isConfigured();
            } catch (\Throwable) {
                // Not configured
            }
        }

        return view('billing::gateways.index', compact('instance', 'gateways', 'statuses'));
    }

    public function edit(string $slug, string $gateway)
    {
        $instance = CurrentInstance::get();
        $definition = $this->gatewayManager->definition($gateway);

        if (!$definition) {
            abort(404, 'Passerelle introuvable.');
        }

        $credentials = $this->gatewayManager->credentials($gateway, $instance->id);
        $enabled = $this->gatewayManager->isEnabled($gateway, $instance->id);

        return view('billing::gateways.edit', compact('instance', 'definition', 'credentials', 'enabled'));
    }

    public function update(Request $request, string $slug, string $gateway)
    {
        $instance = CurrentInstance::get();
        $definition = $this->gatewayManager->definition($gateway);

        if (!$definition) {
            abort(404);
        }

        $credentials = $request->input('credentials', []);
        $enabled = (bool) $request->input('enabled', false);

        $this->gatewayManager->saveCredentials($gateway, $instance->id, $credentials);
        $this->gatewayManager->toggle($gateway, $instance->id, $enabled);

        return redirect()
            ->route('billing.gateways.edit', [$instance->slug, $gateway])
            ->with('status', 'Configuration de ' . $definition->label . ' mise a jour.');
    }

    public function toggle(Request $request, string $slug, string $gateway)
    {
        $instance = CurrentInstance::get();
        $enabled = (bool) $request->input('enabled', false);

        $this->gatewayManager->toggle($gateway, $instance->id, $enabled);

        return back()->with('status', 'Passerelle ' . ($enabled ? 'activee' : 'desactivee') . '.');
    }

    public function test(string $slug, string $gateway)
    {
        $instance = CurrentInstance::get();
        $result = $this->gatewayManager->testConnection($gateway, $instance->id);

        return back()->with(
            $result['success'] ? 'status' : 'error',
            $result['message']
        );
    }
}
