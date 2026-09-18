<?php

namespace App\Services\Rbac;

use Illuminate\Support\Facades\DB;

class PermissionService
{
    /**
     * Fetch all permissions from the database, grouped by their module (group_name).
     *
     * @return \Illuminate\Support\Collection  Keyed by group_name, values are collections of permission objects.
     */
    public function getGroupedPermissions(): \Illuminate\Support\Collection
    {
        return DB::table('permissions')
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');
    }

    /**
     * Get the IDs of permissions directly assigned to a specific user.
     *
     * @param int $userId
     * @return array<int>
     */
    public function getUserDirectPermissionIds(int $userId): array
    {
        return DB::table('user_permissions')
            ->where('user_id', $userId)
            ->pluck('permission_id')
            ->toArray();
    }

    /**
     * Get the names of permissions directly assigned to a specific user.
     *
     * @param int $userId
     * @return array<string>
     */
    public function getUserDirectPermissionNames(int $userId): array
    {
        return DB::table('user_permissions')
            ->join('permissions', 'permissions.id', '=', 'user_permissions.permission_id')
            ->where('user_permissions.user_id', $userId)
            ->pluck('permissions.name')
            ->toArray();
    }

    /**
     * Get the IDs of permissions inherited from the user's assigned role(s).
     *
     * @param int $userId
     * @return array<int>
     */
    public function getUserRolePermissionIds(int $userId): array
    {
        return DB::table('role_permissions')
            ->join('user_roles', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->where('user_roles.user_id', $userId)
            ->pluck('role_permissions.permission_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get the merged (union) effective permission IDs for a user.
     * This combines both direct permissions and role-inherited permissions.
     *
     * @param int $userId
     * @return array<int>
     */
    public function getUserEffectivePermissionIds(int $userId): array
    {
        $directIds = $this->getUserDirectPermissionIds($userId);
        $roleIds = $this->getUserRolePermissionIds($userId);

        return array_values(array_unique(array_merge($directIds, $roleIds)));
    }

    /**
     * Sync (replace) all direct permissions for a specific user.
     * Uses an atomic DB transaction to ensure consistency.
     *
     * @param int   $userId
     * @param array $permissionIds  Array of permission IDs to assign directly.
     * @return void
     */
    public function syncUserPermissions(int $userId, array $permissionIds): void
    {
        DB::transaction(function () use ($userId, $permissionIds) {
            // Remove all existing direct permissions
            DB::table('user_permissions')->where('user_id', $userId)->delete();

            // Insert new direct permissions
            $insertData = [];
            foreach ($permissionIds as $pid) {
                $insertData[] = [
                    'user_id' => $userId,
                    'permission_id' => (int) $pid,
                ];
            }

            if (!empty($insertData)) {
                DB::table('user_permissions')->insert($insertData);
            }
        });
    }

    /**
     * Check if a user has a specific permission by name.
     * Checks BOTH direct permissions (user_permissions) and
     * role-inherited permissions (role_permissions via user_roles).
     *
     * Priority: Direct permissions and role permissions have EQUAL weight.
     * If the permission exists in EITHER source, access is GRANTED.
     *
     * @param int    $userId
     * @param string $permissionName  e.g. 'banners.edit', 'orders.view'
     * @return bool
     */
    public function userCan(int $userId, string $permissionName): bool
    {
        $count = DB::table('permissions as p')
            ->where('p.name', $permissionName)
            ->where(function ($query) use ($userId) {
                // Check direct user permissions
                $query->whereExists(function ($sub) use ($userId) {
                    $sub->select(DB::raw(1))
                        ->from('user_permissions as up')
                        ->whereColumn('up.permission_id', 'p.id')
                        ->where('up.user_id', $userId);
                })
                    // OR check role-inherited permissions
                    ->orWhereExists(function ($sub) use ($userId) {
                        $sub->select(DB::raw(1))
                            ->from('role_permissions as rp')
                            ->join('user_roles as ur', 'ur.role_id', '=', 'rp.role_id')
                            ->whereColumn('rp.permission_id', 'p.id')
                            ->where('ur.user_id', $userId);
                    });
            })
            ->count();

        return $count > 0;
    }

    /**
     * Check if a user has the super_admin role (bypasses all permission checks).
     * Rule: If the user ID is 1, OR if there is only 1 user in the entire system,
     * OR if assigned the 'super_admin' role, they automatically have Super Admin bypass.
     *
     * @param int $userId
     * @return bool
     */
    public function isSuperAdmin(int $userId): bool
    {
        // 1. Root user ID 1 is always Super Admin
        if ($userId === 1) {
            return true;
        }

        // 2. If this is the sole user in the system, grant Super Admin
        $totalUsers = DB::table('users')->count();
        if ($totalUsers <= 1) {
            return true;
        }

        // 3. Check explicit database role assignment
        return DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->where('roles.name', 'super_admin')
            ->exists();
    }

    /**
     * Get all direct permission assignments for multiple users at once.
     * Returns a map: [user_id => [permission_id, permission_id, ...]]
     *
     * @return array<int, array<int>>
     */
    public function getAllUserPermissionsMap(): array
    {
        $rows = DB::table('user_permissions')->get();
        $map = [];

        foreach ($rows as $row) {
            $map[$row->user_id][] = $row->permission_id;
        }

        return $map;
    }

    /**
     * Get all effective permission names (both direct and role-inherited) for a user.
     *
     * @param int $userId
     * @return array<string>
     */
    public function getUserEffectivePermissionNames(int $userId): array
    {
        // 1. Direct permission names
        $directPerms = DB::table('permissions as p')
            ->join('user_permissions as up', 'up.permission_id', '=', 'p.id')
            ->where('up.user_id', $userId)
            ->pluck('p.name')
            ->toArray();

        // 2. Role-inherited permission names
        $rolePerms = DB::table('permissions as p')
            ->join('role_permissions as rp', 'rp.permission_id', '=', 'p.id')
            ->join('user_roles as ur', 'ur.role_id', '=', 'rp.role_id')
            ->where('ur.user_id', $userId)
            ->pluck('p.name')
            ->toArray();

        return array_values(array_unique(array_merge($directPerms, $rolePerms)));
    }
}
