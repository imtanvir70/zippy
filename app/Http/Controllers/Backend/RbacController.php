<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditLoggerService;
use App\Services\Rbac\PermissionService;
use App\Services\Rbac\RoutePermissionSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RbacController extends Controller
{
    protected AuditLoggerService $auditLogger;
    protected PermissionService $permissionService;

    public function __construct(AuditLoggerService $auditLogger, PermissionService $permissionService)
    {
        $this->auditLogger = $auditLogger;
        $this->permissionService = $permissionService;
    }

    /**
     * RBAC Dashboard: Roles, Permissions Matrix, and User-Specific Overrides.
     */
    public function index()
    {
        $roles = DB::table('roles')->get();
        $permissions = $this->permissionService->getGroupedPermissions();
        $users = DB::table('users')->get();

        // Role → Permission mapping
        $rolePermissions = [];
        $rpRows = DB::table('role_permissions')->get();
        foreach ($rpRows as $rp) {
            $rolePermissions[$rp->role_id][] = $rp->permission_id;
        }

        // User → Direct Permission mapping
        $userPermissions = $this->permissionService->getAllUserPermissionsMap();

        // User → Role mapping (for display)
        $userRoles = [];
        $urRows = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->select('user_roles.user_id', 'roles.display_name as role_name')
            ->get();
        foreach ($urRows as $ur) {
            $userRoles[$ur->user_id] = $ur->role_name;
        }

        // Auto-assign Admin display for user ID 1 or single user
        $totalUsersCount = $users->count();
        foreach ($users as $u) {
            if (!isset($userRoles[$u->id]) && ($u->id === 1 || $totalUsersCount <= 1)) {
                $userRoles[$u->id] = 'Super Admin';
            }
        }

        return view('backend.rbac.index', compact(
            'roles',
            'permissions',
            'users',
            'rolePermissions',
            'userPermissions',
            'userRoles'
        ));
    }

    /**
     * Update role-level permissions (existing functionality).
     */
    public function updateRolePermissions(Request $request, $roleId)
    {
        $permissionIds = $request->input('permissions', []);

        DB::transaction(function () use ($roleId, $permissionIds) {
            DB::table('role_permissions')->where('role_id', $roleId)->delete();

            $insertData = [];
            foreach ($permissionIds as $pid) {
                $insertData[] = ['role_id' => $roleId, 'permission_id' => $pid];
            }

            if (!empty($insertData)) {
                DB::table('role_permissions')->insert($insertData);
            }
        });

        $this->auditLogger->logAction('update', 'rbac', $roleId, null, ['permissions_count' => count($permissionIds)], "Updated permissions for Role ID {$roleId}");

        return response()->json(['success' => true, 'message' => 'Role permissions updated successfully!']);
    }

    /**
     * GET: Fetch a specific user's direct permissions data (AJAX endpoint).
     * Returns JSON with grouped permissions and the user's current direct permission IDs.
     */
    public function userPermissions($userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        $directPermissionIds = $this->permissionService->getUserDirectPermissionIds($userId);
        $rolePermissionIds = $this->permissionService->getUserRolePermissionIds($userId);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'grouped_permissions' => $groupedPermissions,
            'direct_permission_ids' => $directPermissionIds,
            'role_permission_ids' => $rolePermissionIds,
        ]);
    }

    /**
     * POST: Sync direct permissions for a specific user.
     * Accepts an array of permission IDs and replaces all existing direct permissions.
     */
    public function syncUserPermissions(Request $request, $userId)
    {
        // Validate user exists
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // Validate permission IDs
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        // Sync via service
        $this->permissionService->syncUserPermissions($userId, $permissionIds);

        // Audit trail
        $this->auditLogger->logAction(
            'update',
            'rbac',
            (string) $userId,
            null,
            ['direct_permissions_count' => count($permissionIds)],
            "Synced direct permissions for User '{$user->name}' (ID: {$userId})"
        );

        return response()->json([
            'success' => true,
            'message' => "Direct permissions for '{$user->name}' updated successfully!",
        ]);
    }

    /**
     * POST: Sync every named /admin route into the permissions table.
     * This is the "Sync Routes → Permissions" button on the RBAC page.
     * Permission name = route name (e.g. admin.orders.index), so the
     * route-name enforcement middleware can match them 1:1.
     */
    public function syncPermissions(RoutePermissionSyncService $syncService)
    {
        $result = $syncService->sync();

        $this->auditLogger->logAction(
            'create',
            'rbac',
            'sync',
            null,
            $result,
            "Synced route permissions: {$result['created']} created, {$result['total']} total mapped"
        );

        return response()->json([
            'success' => true,
            'message' => "Sync complete! {$result['created']} new permissions created ({$result['total']} routes mapped, {$result['skipped']} auth routes skipped).",
            'stats' => $result,
        ]);
    }

    /**
     * POST: Assign (replace) a single role for a user.
     */
    public function assignUserRole(Request $request, $userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $validated = $request->validate([
            'role_id' => 'nullable|integer|exists:roles,id',
        ]);

        DB::transaction(function () use ($userId, $validated) {
            DB::table('user_roles')->where('user_id', $userId)->delete();

            if (!empty($validated['role_id'])) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => (int) $validated['role_id'],
                ]);
            }
        });

        $this->auditLogger->logAction(
            'update',
            'rbac',
            (string) $userId,
            null,
            ['role_id' => $validated['role_id'] ?? null],
            "Assigned role to user '{$user->name}' (ID: {$userId})"
        );

        return response()->json([
            'success' => true,
            'message' => "Role updated for '{$user->name}' successfully!",
        ]);
    }

    /**
     * POST: Create a new user/staff account with optional role and initial direct permissions.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role_id' => 'nullable|integer|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $newUserId = DB::transaction(function () use ($validated) {
            $userId = DB::table('users')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign role if specified
            if (!empty($validated['role_id'])) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => (int) $validated['role_id'],
                ]);
            }

            // Assign direct permissions if specified
            if (!empty($validated['permissions'])) {
                $permInserts = [];
                foreach ($validated['permissions'] as $pid) {
                    $permInserts[] = [
                        'user_id' => $userId,
                        'permission_id' => (int) $pid,
                    ];
                }
                DB::table('user_permissions')->insert($permInserts);
            }

            return $userId;
        });

        $this->auditLogger->logAction(
            'create',
            'users',
            (string) $newUserId,
            null,
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $validated['role_id'] ?? null,
                'permissions_count' => count($validated['permissions'] ?? []),
            ],
            "Created new staff/admin user '{$validated['name']}' (ID: {$newUserId})"
        );

        return response()->json([
            'success' => true,
            'message' => "User '{$validated['name']}' created successfully!",
            'user_id' => $newUserId,
        ]);
    }

    /**
     * POST: Create a new custom role with initial permissions.
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ], [
            'name.unique' => 'A role with this slug/code identifier already exists.',
            'name.required' => 'Role system slug is required (e.g. support_executive).',
            'display_name.required' => 'Role display title is required.',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name'], '_');

        $roleId = DB::transaction(function () use ($validated, $slug) {
            $id = DB::table('roles')->insertGetId([
                'name' => $slug,
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($validated['permissions'])) {
                $rpInserts = [];
                foreach ($validated['permissions'] as $pid) {
                    $rpInserts[] = [
                        'role_id' => $id,
                        'permission_id' => (int) $pid,
                    ];
                }
                DB::table('role_permissions')->insert($rpInserts);
            }

            return $id;
        });

        $this->auditLogger->logAction(
            'create',
            'rbac',
            (string) $roleId,
            null,
            ['name' => $slug, 'display_name' => $validated['display_name']],
            "Created new Role '{$validated['display_name']}' (#{$roleId})"
        );

        return response()->json([
            'success' => true,
            'message' => "Role '{$validated['display_name']}' created successfully!",
            'role_id' => $roleId,
        ]);
    }

    /**
     * DELETE: Delete a role and detach relationships.
     */
    public function deleteRole($roleId)
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
        }

        if ($role->name === 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Super Administrator role cannot be deleted.'], 422);
        }

        DB::transaction(function () use ($roleId) {
            DB::table('role_permissions')->where('role_id', $roleId)->delete();
            DB::table('user_roles')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        });

        $this->auditLogger->logAction(
            'delete',
            'rbac',
            (string) $roleId,
            ['name' => $role->name],
            null,
            "Deleted Role '{$role->display_name}' (#{$roleId})"
        );

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->display_name}' deleted successfully!",
        ]);
    }

    /**
     * GET: Fetch user data for editing.
     */
    public function getUserJson($userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $userRole = DB::table('user_roles')->where('user_id', $userId)->value('role_id');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'district' => $user->district ?? '',
                'role_id' => $userRole,
            ]
        ]);
    }

    /**
     * POST: Update user details & role.
     */
    public function updateUser(Request $request, $userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => "required|email|max:191|unique:users,email,{$userId}",
            'phone' => 'nullable|string|max:30',
            'district' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|integer|exists:roles,id',
        ]);

        DB::transaction(function () use ($userId, $validated) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'district' => $validated['district'] ?? null,
                'updated_at' => now(),
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            DB::table('users')->where('id', $userId)->update($updateData);

            // Update user role
            DB::table('user_roles')->where('user_id', $userId)->delete();
            if (!empty($validated['role_id'])) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => (int) $validated['role_id'],
                ]);
            }
        });

        $this->auditLogger->logAction(
            'update',
            'users',
            (string) $userId,
            null,
            ['name' => $validated['name'], 'email' => $validated['email'], 'role_id' => $validated['role_id'] ?? null],
            "Updated user '{$validated['name']}' (ID: {$userId})"
        );

        return response()->json([
            'success' => true,
            'message' => "User '{$validated['name']}' updated successfully!",
        ]);
    }

    /**
     * DELETE: Delete user account.
     */
    public function deleteUser($userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // Prevent self deletion or super admin deletion if logged in as same
        if (session('admin_id') == $userId) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own active admin account.'], 422);
        }

        DB::transaction(function () use ($userId) {
            DB::table('user_permissions')->where('user_id', $userId)->delete();
            DB::table('user_roles')->where('user_id', $userId)->delete();
            DB::table('users')->where('id', $userId)->delete();
        });

        $this->auditLogger->logAction(
            'delete',
            'users',
            (string) $userId,
            ['name' => $user->name, 'email' => $user->email],
            null,
            "Deleted user '{$user->name}' (ID: {$userId})"
        );

        return response()->json([
            'success' => true,
            'message' => "User '{$user->name}' deleted successfully!",
        ]);
    }
}
