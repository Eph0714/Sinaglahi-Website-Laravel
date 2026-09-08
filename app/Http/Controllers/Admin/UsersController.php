<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\AdminUserPermission;
use App\Models\AspNetRole;
use App\Models\AspNetUser;
use App\Models\Permission;
use App\Models\SuperAdminAssignment;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Super-Admin-only User Management - mirrors Areas/Admin/Controllers/
 * UserManagementController: create/edit/disable/delete Admin accounts,
 * assign roles, and grant/revoke direct permission overrides. A regular
 * Admin never reaches this controller at all (the `superadmin` route
 * middleware requires the Super Admin role outright, not merely a
 * "Users" permission).
 */
class UsersController extends Controller
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function index(): View
    {
        $superAdminUserId = SuperAdminAssignment::query()->value('UserId');
        $adminRoleId = AspNetRole::query()->where('NormalizedName', 'ADMIN')->value('Id');
        $adminUserIds = $adminRoleId
            ? DB::table('aspnetuserroles')->where('RoleId', $adminRoleId)->pluck('UserId')
            : collect();

        $users = AspNetUser::query()->with('adminRole')
            ->where(function ($query) use ($superAdminUserId, $adminUserIds) {
                $query->where('Id', $superAdminUserId)->orWhereIn('Id', $adminUserIds);
            })
            ->orderByDesc('CreatedAt')
            ->get()
            ->map(fn (AspNetUser $user) => (object) [
                'id' => $user->Id,
                'email' => $user->Email,
                'displayName' => $user->DisplayName,
                'isSuperAdmin' => $user->Id === $superAdminUserId,
                'adminRoleName' => $user->adminRole?->Name,
                'isActive' => (bool) $user->IsActive,
                'createdAt' => $user->CreatedAt,
                'lastLoginAt' => $user->LastLoginAt,
            ]);

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roleOptions' => $this->roleOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Email' => ['required', 'email', 'max:200'],
            'DisplayName' => ['nullable', 'max:150'],
            'Password' => ['required', 'min:8', 'confirmed'],
            'AdminRoleId' => ['required', 'exists:adminroles,Id'],
        ]);

        if (AspNetUser::query()->where('NormalizedEmail', mb_strtoupper($data['Email']))->exists()) {
            return back()->withErrors(['Email' => 'An account with this email already exists.'])->withInput();
        }

        $userId = (string) Str::uuid();
        AspNetUser::query()->create([
            'Id' => $userId,
            'IsActive' => true,
            'CreatedAt' => now(),
            'UserName' => $data['Email'],
            'NormalizedUserName' => mb_strtoupper($data['Email']),
            'Email' => $data['Email'],
            'NormalizedEmail' => mb_strtoupper($data['Email']),
            'EmailConfirmed' => true,
            'PasswordHash' => Hash::make($data['Password']),
            'SecurityStamp' => (string) Str::uuid(),
            'ConcurrencyStamp' => (string) Str::uuid(),
            'PhoneNumberConfirmed' => false,
            'TwoFactorEnabled' => false,
            'LockoutEnabled' => true,
            'AccessFailedCount' => 0,
            'DisplayName' => ! empty($data['DisplayName']) ? trim($data['DisplayName']) : null,
            'AdminRoleId' => $data['AdminRoleId'],
        ]);

        $adminRole = AspNetRole::query()->where('NormalizedName', 'ADMIN')->first();
        if ($adminRole) {
            DB::table('aspnetuserroles')->insert(['UserId' => $userId, 'RoleId' => $adminRole->Id]);
        }

        return redirect()->route('admin.users.index')->with('userMessage', 'Admin account created.');
    }

    public function edit(string $id): View|RedirectResponse
    {
        $user = AspNetUser::query()->find($id);
        if (! $user) {
            abort(404);
        }
        if ($this->permissions->isSuperAdmin($id)) {
            return redirect()->route('admin.users.index')
                ->with('userError', "The Super Admin's role and permissions cannot be edited here. Use Transfer Super Admin instead.");
        }

        return view('admin.users.edit', $this->editViewData($user));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $user = AspNetUser::query()->find($id);
        if (! $user) {
            abort(404);
        }
        if ($this->permissions->isSuperAdmin($id)) {
            return redirect()->route('admin.users.index')
                ->with('userError', "The Super Admin's role and permissions cannot be edited here.");
        }

        $data = $request->validate([
            'DisplayName' => ['nullable', 'max:150'],
            'AdminRoleId' => ['required', 'exists:adminroles,Id'],
        ]);

        $user->DisplayName = ! empty($data['DisplayName']) ? trim($data['DisplayName']) : null;
        $user->AdminRoleId = $data['AdminRoleId'];
        $user->save();

        // Reconcile direct overrides against the (possibly just-changed) role baseline.
        $roleBaseline = AdminRole::query()->find($data['AdminRoleId'])
            ?->permissions()->pluck('Id')->all() ?? [];

        $grantedPermissionIds = array_map('intval', $request->input('grantedPermissionIds', []));
        $allPermissionIds = Permission::query()->pluck('Id')->all();

        AdminUserPermission::query()->where('UserId', $id)->delete();

        foreach ($allPermissionIds as $permId) {
            $wantsGranted = in_array($permId, $grantedPermissionIds, true);
            $roleGrantsIt = in_array($permId, $roleBaseline, true);
            if ($wantsGranted !== $roleGrantsIt) {
                AdminUserPermission::query()->create([
                    'UserId' => $id,
                    'PermissionId' => $permId,
                    'IsGranted' => $wantsGranted,
                ]);
            }
        }

        return redirect()->route('admin.users.index')->with('userMessage', 'Admin account updated.');
    }

    public function toggleActive(string $id): RedirectResponse
    {
        if ($this->permissions->isSuperAdmin($id)) {
            return redirect()->route('admin.users.index')->with('userError', 'The Super Admin account cannot be disabled.');
        }

        $user = AspNetUser::query()->find($id);
        if ($user) {
            $user->IsActive = ! $user->IsActive;
            $user->save();
        }

        return redirect()->route('admin.users.index');
    }

    /** Force delete: permanently removes the admin account. */
    public function destroy(string $id): RedirectResponse
    {
        if ($this->permissions->isSuperAdmin($id)) {
            return redirect()->route('admin.users.index')->with('userError', 'The Super Admin account can never be deleted.');
        }

        $user = AspNetUser::query()->find($id);
        if ($user) {
            DB::table('aspnetuserroles')->where('UserId', $id)->delete();
            AdminUserPermission::query()->where('UserId', $id)->delete();
            $user->delete();
        }

        return redirect()->route('admin.users.index')->with('userMessage', 'Admin account permanently deleted.');
    }

    public function resetPasswordForm(string $id): View
    {
        $user = AspNetUser::query()->find($id);
        if (! $user) {
            abort(404);
        }

        return view('admin.users.reset-password', ['userId' => $id, 'email' => $user->Email]);
    }

    public function resetPassword(Request $request, string $id): RedirectResponse
    {
        $user = AspNetUser::query()->find($id);
        if (! $user) {
            abort(404);
        }

        $request->validate([
            'NewPassword' => ['required', 'min:8', 'confirmed'],
        ]);

        $user->PasswordHash = Hash::make($request->input('NewPassword'));
        $user->SecurityStamp = (string) Str::uuid();
        $user->save();

        return redirect()->route('admin.users.index')->with('userMessage', 'Password reset successfully.');
    }

    // ---------------- Transfer Super Admin ----------------

    public function transferSuperAdminForm(): View
    {
        return view('admin.users.transfer-super-admin', ['candidates' => $this->adminCandidates()]);
    }

    public function transferSuperAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'TargetUserId' => ['required', 'exists:aspnetusers,Id'],
            'CurrentPassword' => ['required'],
        ]);

        $currentUser = $request->user();

        if (! Hash::check($data['CurrentPassword'], $currentUser->getAuthPassword())) {
            return back()->withErrors(['CurrentPassword' => 'Incorrect password.'])->withInput();
        }

        $target = AspNetUser::query()->find($data['TargetUserId']);
        $adminRoleId = AspNetRole::query()->where('NormalizedName', 'ADMIN')->value('Id');
        $isTargetAdmin = $adminRoleId && DB::table('aspnetuserroles')
            ->where('UserId', $data['TargetUserId'])->where('RoleId', $adminRoleId)->exists();

        if (! $target || ! $isTargetAdmin) {
            return back()->withErrors(['TargetUserId' => 'Please select a valid Admin account.'])->withInput();
        }

        $superAdminRoleId = AspNetRole::query()->where('NormalizedName', 'SUPERADMIN')->value('Id');

        // Atomically move the singleton guard row and swap Identity roles so
        // exactly one Super Admin ever exists, even mid-transfer.
        DB::transaction(function () use ($currentUser, $target, $adminRoleId, $superAdminRoleId) {
            SuperAdminAssignment::query()->update(['UserId' => $target->Id, 'AssignedAt' => now()]);

            $target->AdminRoleId = null; // the Super Admin isn't scoped to a custom role
            $target->save();
            $currentUser->AdminRoleId = null; // demoted account starts with no role until Super Admin assigns one
            $currentUser->save();

            if ($superAdminRoleId) {
                DB::table('aspnetuserroles')->where('UserId', $currentUser->Id)->where('RoleId', $superAdminRoleId)->delete();
                DB::table('aspnetuserroles')->insert(['UserId' => $target->Id, 'RoleId' => $superAdminRoleId]);
            }
            if ($adminRoleId) {
                DB::table('aspnetuserroles')->where('UserId', $target->Id)->where('RoleId', $adminRoleId)->delete();
                DB::table('aspnetuserroles')->insert(['UserId' => $currentUser->Id, 'RoleId' => $adminRoleId]);
            }
        });

        return redirect()->route('admin.users.index')
            ->with('userMessage', "Super Admin transferred to {$target->Email}. You are now an Admin.");
    }

    // ---------------- Helpers ----------------

    /** @return array<int, AdminRole> */
    private function roleOptions()
    {
        return AdminRole::query()->orderBy('Name')->get(['Id', 'Name']);
    }

    private function adminCandidates()
    {
        $superAdminUserId = SuperAdminAssignment::query()->value('UserId');
        $adminRoleId = AspNetRole::query()->where('NormalizedName', 'ADMIN')->value('Id');
        $adminUserIds = $adminRoleId
            ? DB::table('aspnetuserroles')->where('RoleId', $adminRoleId)->pluck('UserId')
            : collect();

        return AspNetUser::query()
            ->whereIn('Id', $adminUserIds)
            ->where('Id', '!=', $superAdminUserId)
            ->where('IsActive', true)
            ->get(['Id', 'Email', 'DisplayName']);
    }

    private function editViewData(AspNetUser $user): array
    {
        $effective = $this->permissions->getEffectivePermissions($user->Id);
        $roleBaseline = $user->AdminRoleId
            ? AdminRole::query()->find($user->AdminRoleId)?->permissions()->pluck('Key')->all() ?? []
            : [];

        $allPermissions = Permission::query()->orderBy('Module')->orderBy('DisplayOrder')->get();

        $permissions = $allPermissions->map(fn (Permission $p) => (object) [
            'id' => $p->Id,
            'key' => $p->Key,
            'module' => $p->Module,
            'label' => $p->Label,
            'isGrantedByRole' => in_array($p->Key, $roleBaseline, true),
            'isEffectivelyGranted' => in_array($p->Key, $effective, true),
        ]);

        return [
            'user' => $user,
            'roleOptions' => $this->roleOptions(),
            'permissions' => $permissions,
        ];
    }
}
