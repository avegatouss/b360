<?php

namespace Modules\Users\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\RolePermissionManager;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\Models\Role;

final class RoleController extends Controller
{
    public function __construct(
        private readonly RolePermissionManager $manager,
    ) {}

    public function index(string $slug)
    {
        $instance = CurrentInstance::get();
        $scopeId = $this->scopeId($instance);

        $roles = $this->manager->roles($scopeId);
        $permissionGroups = $this->manager->permissionGroups();

        // Count users per role
        $userCounts = [];
        foreach ($roles as $role) {
            $userCounts[$role->id] = $this->manager->usersWithRole($role);
        }

        return view('users::roles.index', compact('instance', 'roles', 'permissionGroups', 'userCounts'));
    }

    public function create(string $slug)
    {
        $instance = CurrentInstance::get();
        $scopeId = $this->scopeId($instance);
        $permissionGroups = $this->manager->permissionGroups();

        return view('users::roles.form', compact('instance', 'permissionGroups', 'scopeId'));
    }

    public function store(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        $scopeId = $this->scopeId($instance);

        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $this->manager->createRole(
            $request->input('name'),
            $request->input('permissions', []),
            $scopeId,
        );

        return redirect()
            ->route('roles.index', $instance->slug)
            ->with('success', 'Role cree avec succes.');
    }

    public function edit(string $slug, Role $role)
    {
        $instance = CurrentInstance::get();
        $scopeId = $this->scopeId($instance);
        $permissionGroups = $this->manager->permissionGroups();
        $rolePermissions = $this->manager->rolePermissions($role, $scopeId);

        return view('users::roles.form', compact('instance', 'role', 'permissionGroups', 'rolePermissions', 'scopeId'));
    }

    public function update(Request $request, string $slug, Role $role)
    {
        $instance = CurrentInstance::get();
        $scopeId = $this->scopeId($instance);

        if ($role->name === 'super-admin') {
            return back()->with('error', 'Le role super-admin ne peut pas etre modifie.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $this->manager->updateRole($role, $request->input('permissions', []), $scopeId);

        return redirect()
            ->route('roles.index', $instance->slug)
            ->with('success', 'Permissions mises a jour.');
    }

    public function destroy(string $slug, Role $role)
    {
        $instance = CurrentInstance::get();

        if (!$this->manager->deleteRole($role)) {
            return back()->with('error', 'Impossible de supprimer ce role (protege ou utilise).');
        }

        return redirect()
            ->route('roles.index', $instance->slug)
            ->with('success', 'Role supprime.');
    }

    private function scopeId($instance): int
    {
        return $instance->isRoot() ? 0 : (int) $instance->id;
    }
}
