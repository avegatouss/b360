<?php

namespace Modules\Users\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Modules\Users\Http\Requests\MembershipSyncRequest;
use Modules\Users\Services\MembershipService;
use Modules\Users\Services\TeamRoleAssigner;

final class UserMembershipController extends Controller
{
    use AuthorizesRequests;
    public function sync(
        MembershipSyncRequest $request,
        string $slug,
        User $user,
        MembershipService $memberships,
        TeamRoleAssigner $roles
    ) {
        $this->authorize('manageMembership', $user);

        foreach ($request->input('memberships', []) as $row) {
            $instanceId = (int) $row['instance_id'];
            $status = (string) $row['status'];
            $role = $row['role'] ?? null;
            $roleList = $role ? [$role] : [];

            $memberships->addToInstance($user, $instanceId, $status);
            $roles->syncRolesForInstance($user, $instanceId, $roleList);
        }

        return back()->with('status', 'Adhésions mises à jour.');
    }
}
