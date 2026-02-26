<?php

namespace Modules\Users\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Support\CurrentInstance;
use Modules\Users\Http\Requests\UserStoreRequest;
use Modules\Users\Http\Requests\UserUpdateRequest;

final class UserController extends Controller
{
    public function index(string $slug)
    {
        $this->authorize('viewAny', User::class);

        $instance = CurrentInstance::get();

        $userIds = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('status', 'active')
            ->pluck('user_id');

        $users = User::query()
            ->on('system')
            ->whereIn('id', $userIds)
            ->orderBy('full_name')
            ->paginate(20);

        return view('users::index', compact('users', 'instance'));
    }

    public function create(string $slug)
    {
        $this->authorize('create', User::class);

        $instance = CurrentInstance::get();

        return view('users::create', compact('instance'));
    }

    public function store(UserStoreRequest $request, string $slug)
    {
        $this->authorize('create', User::class);

        $instance = CurrentInstance::get();

        $user = User::query()->on('system')->create([
            'full_name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

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

        return view('users::edit', compact('user', 'instance', 'instances', 'memberships'));
    }

    public function update(UserUpdateRequest $request, string $slug, User $user)
    {
        $this->authorize('update', $user);

        $instance = CurrentInstance::get();

        $payload = [
            'full_name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
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
