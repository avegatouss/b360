<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;

final class MaintenanceController extends Controller
{
    /**
     * Toggle maintenance mode for the current instance.
     */
    public function toggle(Request $request)
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            abort(404, 'Instance non trouvee.');
        }

        $instance->is_maintenance = !$instance->is_maintenance;
        $instance->save();

        $status = $instance->is_maintenance ? 'activee' : 'desactivee';

        return back()->with('success', "La maintenance a ete {$status} pour l'instance {$instance->name}.");
    }

    /**
     * Update maintenance message and allowed IPs.
     */
    public function update(Request $request)
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            abort(404, 'Instance non trouvee.');
        }

        $validated = $request->validate([
            'maintenance_message' => ['nullable', 'string', 'max:2000'],
            'maintenance_allowed_ips' => ['nullable', 'string'],
        ]);

        $instance->maintenance_message = $validated['maintenance_message'] ?? null;

        // Parse IPs from comma/newline separated string
        $ipsRaw = $validated['maintenance_allowed_ips'] ?? '';
        $ips = array_values(array_filter(
            array_map('trim', preg_split('/[\s,]+/', $ipsRaw)),
            fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP) !== false
        ));

        $instance->maintenance_allowed_ips = !empty($ips) ? $ips : null;
        $instance->save();

        return back()->with('success', 'Les parametres de maintenance ont ete mis a jour.');
    }
}
