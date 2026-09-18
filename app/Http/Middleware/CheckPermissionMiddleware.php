<?php

namespace App\Http\Middleware;

use App\Services\Rbac\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * Checks whether the currently logged-in admin user has the required permission
     * (via direct assignment OR role inheritance). Super Admins bypass all checks.
     *
     * Usage in routes:
     *   ->middleware('check.permission:products.view')
     *   ->middleware('check.permission:orders.edit')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The permission name to check (e.g., 'banners.edit')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $adminId = $request->session()->get('admin_id');

        // If not logged in as admin, deny access
        if (!$adminId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to continue.',
                ], 401);
            }
            return redirect()->route('admin.login')->with('error', 'Please log in to access the Admin Panel.');
        }

        // Super Admins bypass all permission checks
        if ($this->permissionService->isSuperAdmin($adminId)) {
            return $next($request);
        }

        // Check if user has the required permission (direct OR via role)
        if (!$this->permissionService->userCan($adminId, $permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Access denied. You do not have the '{$permission}' permission.",
                ], 403);
            }
            abort(403, "Access denied. You do not have the '{$permission}' permission.");
        }

        return $next($request);
    }
}
