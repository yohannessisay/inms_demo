<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Inertia\Inertia;

class RoleController extends Controller
{
    private function permissionOptions(): array
    {
        return [
            ['key' => 'articles.view_all', 'label' => 'View all articles'],
            ['key' => 'articles.create', 'label' => 'Create articles'],
            ['key' => 'articles.edit', 'label' => 'Edit own articles'],
            ['key' => 'articles.edit_all', 'label' => 'Edit any article'],
            ['key' => 'articles.review', 'label' => 'Submit to review'],
            ['key' => 'articles.approve', 'label' => 'Approve articles'],
            ['key' => 'articles.manage', 'label' => 'Manage all article actions'],
            ['key' => 'users.manage', 'label' => 'Manage users'],
            ['key' => 'roles.manage', 'label' => 'Manage roles'],
        ];
    }

    public function index()
    {
        $this->authorize('manageRoles');

        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $isSystem = in_array($role->slug, ['admin', 'editor', 'reporter'], true);
                $canDelete = ! $isSystem && $role->users_count === 0;

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'users_count' => $role->users_count,
                    'permissions' => $role->permissions ?? [],
                    'can_delete' => $canDelete,
                    'is_system' => $isSystem,
                ];
            });

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $this->permissionOptions(),
        ]);
    }

    public function create()
    {
        $this->authorize('manageRoles');

        return Inertia::render('Roles/Create', [
            'permissions' => $this->permissionOptions(),
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        Role::create([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'permissions' => $request->input('permissions', []),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        $this->authorize('manageRoles');

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'permissions' => $role->permissions ?? [],
            ],
            'permissions' => $this->permissionOptions(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'permissions' => $request->input('permissions', []),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('manageRoles');

        $systemRoles = ['admin', 'editor', 'reporter'];

        if (in_array($role->slug, $systemRoles, true)) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Reassign users before deleting this role.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }
}
