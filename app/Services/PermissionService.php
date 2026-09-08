<?php

namespace App\Services;

use App\Models\AdminRole;
use App\Models\AdminUserPermission;
use App\Models\AspNetUser;
use App\Models\Permission;

/**
 * Computes an admin's effective permission set: Super Admin bypasses the
 * grant system entirely, everyone else gets their Admin Role's baseline
 * permissions with per-user AdminUserPermission overrides (grant/revoke)
 * applied on top. Mirrors Services/PermissionService.cs.
 */
class PermissionService
{
    public function isSuperAdmin(string $userId): bool
    {
        return AspNetUser::query()->find($userId)?->isSuperAdmin() ?? false;
    }

    /** @return array<int, string> */
    public function getEffectivePermissions(string $userId): array
    {
        $user = AspNetUser::query()->find($userId);
        if (! $user) {
            return [];
        }

        if ($user->isSuperAdmin()) {
            return Permission::query()->pluck('Key')->all();
        }

        $granted = [];

        if ($user->AdminRoleId) {
            $granted = AdminRole::query()->find($user->AdminRoleId)
                ?->permissions()->pluck('Key')->all() ?? [];
            $granted = array_flip($granted);
        }

        $overrides = AdminUserPermission::query()->where('UserId', $userId)->with('permission')->get();
        foreach ($overrides as $override) {
            $key = $override->permission?->Key;
            if (! $key) {
                continue;
            }
            if ($override->IsGranted) {
                $granted[$key] = true;
            } else {
                unset($granted[$key]);
            }
        }

        return array_keys($granted);
    }

    public function hasPermission(string $userId, string $permissionKey): bool
    {
        return in_array($permissionKey, $this->getEffectivePermissions($userId), true);
    }
}
