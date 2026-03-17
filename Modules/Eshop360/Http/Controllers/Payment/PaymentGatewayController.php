<?php

namespace Modules\Eshop360\Http\Controllers\Payment;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\EshopPaymentGateway;
use Modules\Eshop360\Services\Payment\PaymentGatewayManager;

class PaymentGatewayController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayManager $manager,
    ) {}

    public function index()
    {
        $instance = CurrentInstance::get();
        $gateways = EshopPaymentGateway::where('instance_id', $instance->id)
            ->orderBy('sort_order')->get();

        $available = $this->manager->getAvailableGateways();

        return view('eshop360::payment-gateways.index', compact('instance', 'gateways', 'available'));
    }

    public function create(Request $request)
    {
        $instance = CurrentInstance::get();
        $available = $this->manager->getAvailableGateways();
        $selectedDriver = $request->query('driver', '');
        $fields = $selectedDriver ? $this->manager->configFields($selectedDriver) : [];

        return view('eshop360::payment-gateways.form', compact('instance', 'available', 'selectedDriver', 'fields'));
    }

    public function store(Request $request)
    {
        $instance = CurrentInstance::get();

        $request->validate([
            'driver' => 'required|string|max:50',
            'display_name' => 'required|string|max:100',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (!$this->manager->isValidDriver($request->input('driver'))) {
            return redirect()->back()->with('error', 'Driver inconnu.')->withInput();
        }

        // Check uniqueness per instance
        $exists = EshopPaymentGateway::where('instance_id', $instance->id)
            ->where('driver', $request->input('driver'))->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Ce driver est deja configure pour cette instance.')->withInput();
        }

        // Collect config fields dynamically
        $configFields = $this->manager->configFields($request->input('driver'));
        $config = [];
        foreach ($configFields as $field) {
            $value = $request->input('config_' . $field['name']);
            if ($value !== null) {
                $config[$field['name']] = $value;
            }
        }

        EshopPaymentGateway::create([
            'instance_id' => $instance->id,
            'driver' => $request->input('driver'),
            'display_name' => $request->input('display_name'),
            'config' => $config,
            'is_active' => $request->boolean('is_active', false),
            'is_test_mode' => $request->boolean('is_test_mode', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('eshop360.payment-gateways.index', $instance->slug)
            ->with('success', 'Passerelle ajoutee avec succes.');
    }

    public function edit(int $id)
    {
        $instance = CurrentInstance::get();
        $gateway = EshopPaymentGateway::where('instance_id', $instance->id)->findOrFail($id);
        $available = $this->manager->getAvailableGateways();
        $fields = $this->manager->configFields($gateway->driver);

        return view('eshop360::payment-gateways.form', compact('instance', 'gateway', 'available', 'fields'));
    }

    public function update(Request $request, int $id)
    {
        $instance = CurrentInstance::get();
        $gateway = EshopPaymentGateway::where('instance_id', $instance->id)->findOrFail($id);

        $request->validate([
            'display_name' => 'required|string|max:100',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Collect config fields dynamically
        $configFields = $this->manager->configFields($gateway->driver);
        $config = $gateway->getDecryptedConfig();

        foreach ($configFields as $field) {
            $value = $request->input('config_' . $field['name']);
            if ($value !== null && $value !== '') {
                $config[$field['name']] = $value;
            }
        }

        $gateway->update([
            'display_name' => $request->input('display_name'),
            'config' => $config,
            'is_active' => $request->boolean('is_active', false),
            'is_test_mode' => $request->boolean('is_test_mode', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('eshop360.payment-gateways.index', $instance->slug)
            ->with('success', 'Passerelle mise a jour.');
    }

    public function destroy(int $id)
    {
        $instance = CurrentInstance::get();
        $gateway = EshopPaymentGateway::where('instance_id', $instance->id)->findOrFail($id);
        $gateway->delete();

        return redirect()->route('eshop360.payment-gateways.index', $instance->slug)
            ->with('success', 'Passerelle supprimee.');
    }

    public function toggle(int $id)
    {
        $instance = CurrentInstance::get();
        $gateway = EshopPaymentGateway::where('instance_id', $instance->id)->findOrFail($id);
        $gateway->update(['is_active' => !$gateway->is_active]);

        $status = $gateway->is_active ? 'activee' : 'desactivee';

        return redirect()->route('eshop360.payment-gateways.index', $instance->slug)
            ->with('success', "Passerelle {$status}.");
    }
}
