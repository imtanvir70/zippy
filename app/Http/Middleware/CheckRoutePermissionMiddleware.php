<?php

namespace App\Http\Middleware;

use App\Services\Rbac\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermissionMiddleware
{
    protected PermissionService $permissionService;

    protected array $routeAliasMap = [
        'admin.products.json' => 'admin.products.index',
        'admin.products.ajax_save' => 'admin.products.store',
        'admin.products.duplicate' => 'admin.products.store',
        'admin.products.ajax_delete' => 'admin.products.delete',
        'admin.products.ai' => 'admin.products.index',
        'admin.products.ai.generate' => 'admin.products.index',
        'admin.products.ai.save' => 'admin.products.index',
        'admin.products.ai.category.create' => 'admin.products.index',
        'admin.products.ai.seo_description' => 'admin.products.index',
        'admin.product_demands.index' => 'admin.products.index',
        'admin.product_demands.status' => 'admin.products.index',
        'admin.product_demands.notes' => 'admin.products.index',
        'admin.product_demands.destroy' => 'admin.products.delete',
        'admin.categories.json' => 'admin.categories.index',
        'admin.categories.ajax_save' => 'admin.categories.store',
        'admin.categories.ajax_delete' => 'admin.categories.delete',
        'admin.banners.json' => 'admin.banners.index',
        'admin.banners.ajax_save' => 'admin.banners.store',
        'admin.banners.ajax_delete' => 'admin.banners.delete',
        'admin.orders.ajax_status' => 'admin.orders.status',
        'admin.orders.ajax_call_status' => 'admin.orders.status',
        'admin.orders.ajax_details' => 'admin.orders.show',
        'admin.orders.ajax_delete' => 'admin.orders.delete',
        'admin.logistics.bulk_dispatch' => 'admin.logistics.dispatch',
        'admin.settings.ajax_update' => 'admin.settings.update',
        'admin.settings.meta' => 'admin.settings.index',
        'admin.settings.meta.update' => 'admin.settings.update',
        'admin.system.info' => 'admin.settings.manage',
        'admin.system.clear_cache' => 'admin.settings.manage',
        'admin.rbac.users.json' => 'admin.rbac.index',
        'admin.rbac.user_permissions' => 'admin.rbac.index',
        'admin.rbac.sync' => 'admin.rbac.index',
        'admin.bumps.index' => 'admin.products.index',
        'admin.bumps.store' => 'admin.products.store',
        'admin.bumps.toggle' => 'admin.products.store',
        'admin.bumps.destroy' => 'admin.products.delete',
        'admin.blocklist.index' => 'admin.fraud.index',
        'admin.blocklist.store' => 'admin.fraud.index',
        'admin.blocklist.toggle' => 'admin.fraud.index',
        'admin.blocklist.destroy' => 'admin.fraud.index',
        'admin.popups.index' => 'admin.banners.index',
        'admin.popups.store' => 'admin.banners.store',
        'admin.popups.json' => 'admin.banners.index',
        'admin.popups.update' => 'admin.banners.update',
        'admin.popups.toggle' => 'admin.banners.update',
        'admin.popups.destroy' => 'admin.banners.delete',
    ];

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->session()->get('admin_id');

        if (!$adminId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to continue.',
                ], 401);
            }
            return redirect()->route('admin.login')->with('error', 'Please log in to access the Admin Panel.');
        }

        if ($this->permissionService->isSuperAdmin($adminId)) {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        if ($routeName === 'admin.dashboard' || $routeName === 'admin.login' || $routeName === 'admin.logout') {
            return $next($request);
        }

        if ($routeName) {
            $targetPermission = $this->routeAliasMap[$routeName] ?? $routeName;

            if (!$this->permissionService->userCan($adminId, $targetPermission)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Access denied. You do not have permission ('{$targetPermission}') to perform this action.",
                    ], 403);
                }
                abort(403, "Access denied. You do not have permission ('{$targetPermission}') to access this page.");
            }
        }

        return $next($request);
    }
}
