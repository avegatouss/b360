<?php

namespace Modules\Users\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Users\Http\Requests\UserStoreRequest;
use Modules\Users\Http\Requests\UserUpdateRequest;
use Modules\Users\Services\MembershipService;

final class UserController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request, string $slug)
    {
        $this->authorize('viewAny', User::class);

        $instance = CurrentInstance::get();

        $userIds = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->pluck('user_id');

        $query = User::query()
            ->on('system')
            ->whereIn('id', $userIds);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('full_name')->paginate(20)->withQueryString();

        return view('users::index', compact('users', 'instance'));
    }

    public function create(string $slug)
    {
        $this->authorize('create', User::class);

        $instance = CurrentInstance::get();

        return view('users::create', compact('instance'));
    }

    public function store(UserStoreRequest $request, string $slug, MembershipService $memberships)
    {
        $this->authorize('create', User::class);

        $instance = CurrentInstance::get();

        $user = User::query()->on('system')->create([
            'full_name' => $request->string('full_name')->toString(),
            'username' => $request->string('username')->toString() ?: null,
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        $memberships->addToInstance($user, $instance->id, 'active');

        return redirect()
            ->route('users.edit', [$instance->slug, $user])
            ->with('status', 'Utilisateur créé.');
    }

    public function edit(string $slug, User $user)
    {
        $this->authorize('update', $user);

        $instance = CurrentInstance::get();

        $instances = \App\Instances\Instance::query()->on('system')->orderBy('slug')->get();

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

        return view('users::edit', compact('user', 'instance', 'instances', 'memberships', 'userRoles'));
    }

    public function update(UserUpdateRequest $request, string $slug, User $user)
    {
        $this->authorize('update', $user);

        $payload = [
            'full_name' => $request->string('full_name')->toString(),
            'username' => $request->string('username')->toString() ?: null,
            'email' => $request->string('email')->toString(),
            'is_active' => $request->boolean('is_active'),
            'is_blocked' => $request->boolean('is_blocked'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->string('password')->toString();
        }

        $user->setConnection('system');
        $user->update($payload);

        return back()->with('status', 'Utilisateur mis à jour.');
    }

    public function destroy(string $slug, User $user)
    {
        $this->authorize('delete', $user);

        $instance = CurrentInstance::get();

        $user->setConnection('system');
        $user->delete();

        return redirect()
            ->route('users.index', $instance->slug)
            ->with('status', 'Utilisateur supprimé.');
    }
}
