<?php

namespace Modules\Eshop360\Http\Controllers\Communication;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Communication\Models\SmsGateway;
use Modules\Eshop360\Services\Sms\SmsManager;

class SmsGatewayController extends Controller
{
    protected SmsManager $smsManager;

    public function __construct(SmsManager $smsManager)
    {
        $this->smsManager = $smsManager;
    }

    /**
     * List configured SMS gateways.
     */
    public function index()
    {
        $instance = CurrentInstance::get();
        $gateways = SmsGateway::where('instance_id', $instance->id)
            ->orderByDesc('is_default')
            ->orderBy('display_name')
            ->get();

        $availableDrivers = $this->smsManager->getAvailableDrivers();

        return view('eshop360::communication.sms-gateways.index', compact('gateways', 'availableDrivers'));
    }

    /**
     * Show form to create a new gateway.
     */
    public function create()
    {
        $availableDrivers = $this->smsManager->getAvailableDrivers();
        $gateway = null;

        return view('eshop360::communication.sms-gateways.form', compact('availableDrivers', 'gateway'));
    }

    /**
     * Store a new SMS gateway.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => 'required|string|in:'.implode(',', array_keys($this->smsManager->getAvailableDrivers())),
            'display_name' => 'required|string|max:255',
            'config' => 'required|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $instance = CurrentInstance::get();

        // If setting as default, unset other defaults
        if (! empty($validated['is_default'])) {
            SmsGateway::where('instance_id', $instance->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        SmsGateway::create([
            'instance_id' => $instance->id,
            'driver' => $validated['driver'],
            'display_name' => $validated['display_name'],
            'config' => $validated['config'],
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('eshop360.sms-gateways.index')
            ->with('success', __('SMS gateway created successfully.'));
    }

    /**
     * Show form to edit an existing gateway.
     */
    public function edit(SmsGateway $gateway)
    {
        $availableDrivers = $this->smsManager->getAvailableDrivers();

        return view('eshop360::communication.sms-gateways.form', compact('availableDrivers', 'gateway'));
    }

    /**
     * Update an existing gateway.
     */
    public function update(Request $request, SmsGateway $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => 'required|string|in:'.implode(',', array_keys($this->smsManager->getAvailableDrivers())),
            'display_name' => 'required|string|max:255',
            'config' => 'required|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $instance = CurrentInstance::get();

        // If setting as default, unset other defaults
        if (! empty($validated['is_default'])) {
            SmsGateway::where('instance_id', $instance->id)
                ->where('is_default', true)
                ->where('id', '!=', $gateway->id)
                ->update(['is_default' => false]);
        }

        $gateway->update([
            'driver' => $validated['driver'],
            'display_name' => $validated['display_name'],
            'config' => $validated['config'],
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('eshop360.sms-gateways.index')
            ->with('success', __('SMS gateway updated successfully.'));
    }

    /**
     * Delete a gateway.
     */
    public function destroy(SmsGateway $gateway): RedirectResponse
    {
        $gateway->delete();

        return redirect()->route('eshop360.sms-gateways.index')
            ->with('success', __('SMS gateway deleted successfully.'));
    }

    /**
     * Send a test SMS via the gateway.
     */
    public function test(Request $request, SmsGateway $gateway): RedirectResponse
    {
        $request->validate([
            'test_phone' => 'required|string|max:20',
        ]);

        try {
            $driver = $this->smsManager->driverFromGateway($gateway);
            $success = $driver->send(
                $request->input('test_phone'),
                __('This is a test SMS from :app.', ['app' => config('app.name', 'B360')])
            );

            if ($success) {
                return redirect()->route('eshop360.sms-gateways.index')
                    ->with('success', __('Test SMS sent successfully.'));
            }

            return redirect()->route('eshop360.sms-gateways.index')
                ->with('error', __('Test SMS failed. Check your gateway configuration and logs.'));
        } catch (\Throwable $e) {
            return redirect()->route('eshop360.sms-gateways.index')
                ->with('error', __('Test SMS error: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Set a gateway as the default.
     */
    public function setDefault(SmsGateway $gateway): RedirectResponse
    {
        $instance = CurrentInstance::get();

        // Unset all defaults for this instance
        SmsGateway::where('instance_id', $instance->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $gateway->update(['is_default' => true]);

        return redirect()->route('eshop360.sms-gateways.index')
            ->with('success', __(':name is now the default SMS gateway.', ['name' => $gateway->display_name]));
    }
}
