<?php

namespace Modules\Users\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Modules\Users\Http\Requests\MembershipSyncRequest;
use Modules\Users\Services\MembershipService;
use Modules\Users\Services\TeamRoleAssigner;

final class UserMembershipController extends Controller
{
    public function sync(
        MembershipSyncRequest $request,
        User $user,
        MembershipService $memberships,
        TeamRoleAssigner $roles
    ) {
        $this->authorize('manageMembership', $user);

        foreach ($request->input('memberships', []) as $row) {
            $instanceId = (int) $row['instance_id'];
            $status = (string) $row['status'];
            $roleList = (array) ($row['roles'] ?? []);

            $memberships->addToInstance($user, $instanceId, $status);
            $roles->syncRolesForInstance($user, $instanceId, $roleList);
        }

        return back()->with('status', 'Memberships updated.');
    }
}
