<?php

namespace Modules\Users\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\RolePermissionManager;
use Modules\Core\Support\CurrentInstance;
use Modules\Users\Http\Requests\UserStoreRequest;
use Modules\Users\Http\Requests\UserUpdateRequest;
use Modules\Users\Services\MembershipService;

final class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, string $slug, RolePermissionManager $roleManager)
    {
        $this->authorize('viewAny', User::class);

        $instance = CurrentInstance::get();
        $teamFk = config('permission.column_names.team_foreign_key', 'instance_id');
        $scopeId = $instance->isRoot() ? 0 : (int) $instance->id;

        // Base query: users belonging to this instance
        $membershipQuery = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id);

        // Filter by membership status
        $statusFilter = $request->input('status', 'active');
        if ($statusFilter && in_array($statusFilter, ['active', 'invited', 'disabled'])) {
            $membershipQuery->where('status', $statusFilter);
        }

        $membershipData = $membershipQuery->get()->keyBy('user_id');
        $userIds = $membershipData->pluck('user_id');

        $query = User::whereIn('id', $userIds);

        // Search filter
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Account state filter
        if ($request->input('account') === 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($request->input('account') === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->orderBy('full_name')->paginate(20)->withQueryString();

        // Load roles for each user in this instance
        $userRoles = DB::connection('system')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('model_has_roles.model_id', $users->pluck('id'))
            ->where("model_has_roles.{$teamFk}", $scopeId)
            ->pluck('roles.name', 'model_has_roles.model_id')
            ->all();

        // Available roles for filter
        $roles = $roleManager->assignableRoles();

        // Filter by role (post-query since it's a pivot)
        $roleFilter = $request->input('role');
        if ($roleFilter) {
            $roleUserIds = array_keys(array_filter($userRoles, fn($r) => $r === $roleFilter));
            $users->setCollection($users->getCollection()->filter(fn($u) => in_array($u->id, $roleUserIds)));
        }

        return view('users::index', compact('users', 'instance', 'userRoles', 'roles', 'membershipData', 'statusFilter'));
    }

    public function show(string $slug, User $user, RolePermissionManager $roleManager)
    {
        $this->authorize('view', $user);

        $instance = CurrentInstance::get();
        $teamFk = config('permission.column_names.team_foreign_key', 'instance_id');
        $scopeId = $instance->isRoot() ? 0 : (int) $instance->id;

        $membership = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('user_id', $user->id)
            ->first();

        $currentRole = $roleManager->userRoleInInstance($user, $scopeId);
        $roles = $roleManager->assignableRoles();

        // User's permissions via role
        $permissions = [];
        if ($currentRole) {
            $role = \Spatie\Permission\Models\Role::where('name', $currentRole)->where('guard_name', 'web')->first();
            if ($role) {
                $permissions = $role->permissions->pluck('name')->toArray();
            }
        }

        // Activity info
        $otherMemberships = DB::connection('system')
            ->table('instance_user')
            ->join('instances', 'instances.id', '=', 'instance_user.instance_id')
            ->where('instance_user.user_id', $user->id)
            ->select('instances.slug', 'instances.name', 'instance_user.status')
            ->get();

        return view('users::show', compact(
            'user', 'instance', 'membership', 'currentRole', 'roles',
            'permissions', 'otherMemberships'
        ));
    }

    public function create(string $slug, RolePermissionManager $roleManager)
    {
        $this->authorize('create', User::class);

        $instance = CurrentInstance::get();
        $roles = $roleManager->assignableRoles();

        return view('users::create', compact('instance', 'roles'));
    }

    public function store(UserStoreRequest $request, string $slug, MembershipService $memberships, RolePermissionManager $roleManager)
    {
        $this->authorize('create', User::class);

        $instance = CurrentInstance::get();
        $scopeId = $instance->isRoot() ? 0 : (int) $instance->id;

        $user = User::create([
            'full_name' => $request->string('full_name')->toString(),
            'username' => $request->string('username')->toString() ?: null,
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        $memberships->addToInstance($user, $instance->id, 'active');

        // Assign role if specified
        if ($request->filled('role') && $request->input('role') !== '') {
            $roleManager->syncUserRole($user, $request->input('role'), $scopeId);
        }

        return redirect()
            ->route('users.show', [$instance->slug, $user])
            ->with('status', 'Utilisateur créé avec succès.');
    }

    public function edit(string $slug, User $user, RolePermissionManager $roleManager)
    {
        $this->authorize('update', $user);

        $instance = CurrentInstance::get();
        $scopeId = $instance->isRoot() ? 0 : (int) $instance->id;

        $instances = \App\Instances\Instance::orderBy('slug')->get();

        $memberships = DB::connection('system')
            ->table('instance_user')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('instance_id');

        // Charger le rôle Spatie courant pour chaque instance
        $teamFk = config('permission.column_names.team_foreign_key', 'instance_id');
        $userRoles = DB::connection('system')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.model_id', $user->id)
            ->pluck('roles.name', "model_has_roles.{$teamFk}")
            ->all();

        $currentRole = $userRoles[$scopeId] ?? null;
        $roles = $roleManager->assignableRoles();

        return view('users::edit', compact('user', 'instance', 'instances', 'memberships', 'userRoles', 'currentRole', 'roles'));
    }

    public function update(UserUpdateRequest $request, string $slug, User $user, RolePermissionManager $roleManager)
    {
        $this->authorize('update', $user);

        $instance = CurrentInstance::get();
        $scopeId = $instance->isRoot() ? 0 : (int) $instance->id;

        $payload = [
            'full_name' => $request->string('full_name')->toString(),
            'username' => $request->string('username')->toString() ?: null,
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString() ?: null,
            'is_active' => $request->boolean('is_active'),
            'is_blocked' => $request->boolean('is_blocked'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->string('password')->toString();
        }

        $user->update($payload);

        // Update role if specified
        if ($request->has('role')) {
            $roleName = $request->input('role');
            if ($roleName) {
                $roleManager->syncUserRole($user, $roleName, $scopeId);
            }
        }

        return back()->with('status', 'Utilisateur mis à jour.');
    }

    public function toggleBlock(string $slug, User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas bloquer votre propre compte.');
        }

        $user->is_blocked ? $user->unblock() : $user->block();

        $status = $user->is_blocked ? 'bloqué' : 'débloqué';

        return back()->with('status', "Utilisateur {$status}.");
    }

    public function toggleActive(string $slug, User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('status', "Utilisateur {$status}.");
    }

    public function destroy(string $slug, User $user)
    {
        $this->authorize('delete', $user);

        $instance = CurrentInstance::get();

        $user->delete();

        return redirect()
            ->route('users.index', $instance->slug)
            ->with('status', 'Utilisateur supprimé.');
    }
}
