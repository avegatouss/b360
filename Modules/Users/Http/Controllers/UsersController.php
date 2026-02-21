<?php

namespace Modules\Users\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Http\Requests\UserStoreRequest;
use Modules\Users\Http\Requests\UserUpdateRequest;

final class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->on('system')
            ->orderBy('name')
            ->paginate(20);

        return view('users::index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('users::create');
    }

    public function store(UserStoreRequest $request)
    {
        $this->authorize('create', User::class);

        $user = User::query()->on('system')->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        return redirect()->route('users.edit', $user)->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $instances = \App\Instances\Instance::query()->on('system')->orderBy('slug')->get();

        $memberships = \Illuminate\Support\Facades\DB::connection('system')
            ->table('instance_user')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('instance_id');

        return view('users::edit', compact('user', 'instances', 'memberships'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->string('password')->toString());
        }

        $user->setConnection('system');
        $user->update($payload);

        return back()->with('status', 'User updated.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->setConnection('system');
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }
}
