<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-Admin-only Admin Role & Permission Management - mirrors Areas/Admin/
 * Controllers/AdminRolesController. Lets the Super Admin define custom roles
 * (e.g. "Activities Administrator") and pick exactly which permissions each
 * one grants - entirely data-driven, no code changes needed.
 */
class RolesController extends Controller
{
    public function index(): View
    {
        $roles = AdminRole::query()
            ->withCount('permissions')
            ->withCount('users')
            ->orderBy('Name')
            ->get();

        return view('admin.roles.index', ['roles' => $roles]);
    }

    public function create(): View
    {
        return view('admin.roles.form', ['role' => null, 'permissionGroups' => $this->permissionGroups(), 'selectedPermissionIds' => []]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if (AdminRole::query()->where('Name', trim($data['Name']))->exists()) {
            return back()->withErrors(['Name' => 'A role with this name already exists.'])->withInput();
        }

        $role = AdminRole::query()->create([
            'Name' => trim($data['Name']),
            'Description' => ! empty($data['Description']) ? trim($data['Description']) : null,
            'CreatedAt' => now(),
        ]);

        $this->assignPermissions($role, $request->input('SelectedPermissionIds', []));

        return redirect()->route('admin.roles.index')->with('roleMessage', 'Admin role created.');
    }

    public function edit(int $id): View
    {
        $role = AdminRole::query()->find($id);
        if (! $role) {
            abort(404);
        }

        return view('admin.roles.form', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissionIds' => $role->permissions()->pluck('Id')->all(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = AdminRole::query()->find($id);
        if (! $role) {
            abort(404);
        }

        $data = $this->validated($request);

        if (AdminRole::query()->where('Name', trim($data['Name']))->where('Id', '!=', $id)->exists()) {
            return back()->withErrors(['Name' => 'A role with this name already exists.'])->withInput();
        }

        $role->Name = trim($data['Name']);
        $role->Description = ! empty($data['Description']) ? trim($data['Description']) : null;
        $role->save();

        $role->permissions()->detach();
        $this->assignPermissions($role, $request->input('SelectedPermissionIds', []));

        return redirect()->route('admin.roles.index')->with('roleMessage', 'Admin role updated.');
    }

    /** Force delete: blocked while any admin is still assigned to this role, to avoid silently stripping their access. */
    public function destroy(int $id): RedirectResponse
    {
        $role = AdminRole::query()->find($id);
        if (! $role) {
            abort(404);
        }

        $assignedCount = $role->users()->count();
        if ($assignedCount > 0) {
            return redirect()->route('admin.roles.index')
                ->with('roleError', "Cannot delete \"{$role->Name}\" - it is currently assigned to {$assignedCount} admin(s). Reassign them first.");
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')->with('roleMessage', 'Admin role permanently deleted.');
    }

    // ---------------- Helpers ----------------

    private function validated(Request $request): array
    {
        return $request->validate([
            'Name' => ['required', 'max:100'],
            'Description' => ['nullable', 'max:300'],
        ]);
    }

    /** Super-Admin-only permissions (Users & Roles management) are never grantable to a regular Admin role, filtered out server-side. */
    private function assignPermissions(AdminRole $role, array $permissionIds): void
    {
        $permissionIds = array_map('intval', $permissionIds);
        $superAdminOnlyIds = Permission::query()->where('IsSuperAdminOnly', true)->pluck('Id')->all();
        $safeIds = array_values(array_diff(array_unique($permissionIds), $superAdminOnlyIds));

        $role->permissions()->attach($safeIds);
    }

    private function permissionGroups()
    {
        return Permission::query()
            ->where('IsSuperAdminOnly', false)
            ->orderBy('Module')->orderBy('DisplayOrder')
            ->get()
            ->groupBy('Module');
    }
}
