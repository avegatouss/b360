<?php

namespace Modules\Eshop360\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Store;
use Modules\Eshop360\Models\UserAssignment;
use Modules\Eshop360\Models\Warehouse;

class UserAssignmentController extends Controller
{
    /**
     * List all users with their assignment counts.
     */
    public function index()
    {
        $instance = CurrentInstance::get();

        // Get instance users from system DB
        $userIds = \Illuminate\Support\Facades\DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)->orderBy('full_name')->get();

        // Load assignment counts per user
        $assignmentCounts = UserAssignment::where('instance_id', $instance->id)
            ->selectRaw('user_id, resource_type, COUNT(*) as count')
            ->groupBy('user_id', 'resource_type')
            ->get()
            ->groupBy('user_id');

        return view('eshop360::settings.user-assignments-index', compact('users', 'assignmentCounts', 'instance'));
    }

    /**
     * Show the assignment form for a specific user.
     */
    public function edit(Request $request, string $slug, int $userId)
    {
        $instance = CurrentInstance::get();
        $user = User::findOrFail($userId);

        // Get current assignments
        $currentAssignments = UserAssignment::where('instance_id', $instance->id)
            ->where('user_id', $userId)
            ->get()
            ->groupBy('resource_type')
            ->map(fn ($group) => $group->pluck('resource_id')->toArray());

        // Load all resources (use withoutGlobalScopes to see all)
        $warehouses = Warehouse::withoutGlobalScopes()->where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();
        $stores = Store::withoutGlobalScopes()->where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();
        $customers = Customer::withoutGlobalScopes()->where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get();

        return view('eshop360::settings.user-assignments', compact('user', 'currentAssignments', 'warehouses', 'stores', 'customers', 'instance'));
    }

    /**
     * Save assignments for a user.
     */
    public function update(Request $request, string $slug, int $userId)
    {
        $instance = CurrentInstance::get();
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'warehouses' => 'nullable|array',
            'warehouses.*' => 'integer|exists:eshop_warehouses,id',
            'stores' => 'nullable|array',
            'stores.*' => 'integer|exists:eshop_stores,id',
            'customers' => 'nullable|array',
            'customers.*' => 'integer|exists:eshop_customers,id',
        ]);

        // Delete existing assignments for this user in this instance
        UserAssignment::where('instance_id', $instance->id)
            ->where('user_id', $userId)
            ->delete();

        // Create new assignments
        $assignments = [];

        foreach ($validated['warehouses'] ?? [] as $warehouseId) {
            $assignments[] = [
                'user_id' => $userId,
                'resource_type' => 'warehouse',
                'resource_id' => $warehouseId,
                'instance_id' => $instance->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($validated['stores'] ?? [] as $storeId) {
            $assignments[] = [
                'user_id' => $userId,
                'resource_type' => 'store',
                'resource_id' => $storeId,
                'instance_id' => $instance->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($validated['customers'] ?? [] as $customerId) {
            $assignments[] = [
                'user_id' => $userId,
                'resource_type' => 'customer',
                'resource_id' => $customerId,
                'instance_id' => $instance->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($assignments)) {
            UserAssignment::insert($assignments);
        }

        return redirect()->route('eshop360.settings.user-assignments.edit', [$slug, $userId])
            ->with('success', 'Affectations mises à jour.');
    }
}
