<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Routing\Controller as BaseController;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:settings.roles');
    }

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('settings.roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $allPermissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('settings.roles.edit', compact('role', 'allPermissions', 'rolePermissions'));
    }

    public function update(\Illuminate\Http\Request $request, Role $role)
    {
        $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('settings.roles.index')
            ->with('success', "Hak akses role {$role->name} berhasil diperbarui.");
    }
}
