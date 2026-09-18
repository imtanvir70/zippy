<?php

namespace App\Services\Rbac;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class RoutePermissionSyncService
{
    /**
     * Routes that should never be shown in RBAC permission matrix (Public/Auth/Root/Internal utility).
     */
    protected array $exempt = [
        'admin.login',
        'admin.login.post',
        'admin.logout',

        // JSON endpoints & AJAX duplicates (handled implicitly under their parent module permissions)
        'admin.products.json',
        'admin.categories.json',
        'admin.banners.json',
        'admin.rbac.users.json',
        'admin.rbac.user_permissions',
        'admin.rbac.sync',
        'admin.orders.ajax_details',
        'admin.orders.ajax_call_status',
    ];

    /**
     * Map helper AJAX/secondary routes to parent core permission keys.
     * E.g. ajax_delete & delete share the same parent 'delete' permission.
     */
    protected array $routeAliasMap = [
        'admin.products.ajax_save' => 'admin.products.store',
        'admin.products.ajax_delete' => 'admin.products.delete',
        'admin.categories.ajax_save' => 'admin.categories.store',
        'admin.categories.ajax_delete' => 'admin.categories.delete',
        'admin.banners.ajax_save' => 'admin.banners.store',
        'admin.banners.ajax_delete' => 'admin.banners.delete',
        'admin.orders.ajax_status' => 'admin.orders.status',
        'admin.orders.ajax_call_status' => 'admin.orders.status',
        'admin.orders.ajax_details' => 'admin.orders.show',
        'admin.orders.ajax_delete' => 'admin.orders.delete',
        'admin.settings.ajax_update' => 'admin.settings.update',
    ];

    /**
     * Clean, human-readable display titles for functional permissions.
     */
    protected array $actionLabels = [
        'dashboard' => 'View ERP Dashboard & Analytics',
        'orders.show' => 'View Order Details',
        'orders.status' => 'Update Order Status',
        'orders.unlink_device' => 'Unlink Order Device Session',
        'orders.print' => 'Print Order Invoice',
        'orders.packing_slip' => 'Print Packing Slip',
        'orders.delete' => 'Delete Order Records',

        'logistics.index' => 'Logistics Dashboard & History',
        'logistics.dispatch' => 'Dispatch Shipment to Courier',
        'logistics.track' => 'Live Shipment Tracking',

        'refunds.index' => 'View RMA & Refund Requests',
        'refunds.store' => 'Process & Approve Refunds',

        'products.index' => 'View Products Catalog',
        'products.create' => 'Add New Product (Page)',
        'products.store' => 'Save / Create Products',
        'products.edit' => 'Edit Product (Page)',
        'products.update' => 'Update Product Details',
        'products.toggle' => 'Toggle Product Active Status',
        'products.delete' => 'Delete Product Records',
        'products.export_csv' => 'Export Products CSV',
        'products.import_csv' => 'Import Products CSV',

        'categories.index' => 'View Categories List',
        'categories.store' => 'Create / Save Categories',
        'categories.update' => 'Update Category Details',
        'categories.delete' => 'Delete Category Records',

        'banners.index' => 'View Hero Banners',
        'banners.store' => 'Create / Save Hero Banners',
        'banners.update' => 'Update Banner Details',
        'banners.delete' => 'Delete Banner Records',

        'reports.index' => 'View Financial Analytics & Reports',
        'reports.export_csv' => 'Export Financial Reports CSV',
        'audit.index' => 'View System Audit Logs',

        'customers.index' => 'Customer CRM & Profiles',
        'crm.show' => 'View Customer Profile Details',
        'crm.wallet' => 'Adjust Customer Wallet Balance',

        'abandoned.index' => 'View Abandoned Carts',
        'abandoned.recover' => 'Mark Abandoned Cart Recovered',

        'fraud.index' => 'Real-Time Fraud Checker',
        'fraud.evaluate' => 'Run Fraud Evaluation',
        'fraud.status' => 'Update Fraud Review Status',

        'coupons.index' => 'View Coupons & Flash Deals',
        'coupons.save' => 'Create / Update Coupons',
        'coupons.delete' => 'Delete Coupon Codes',

        'support.index' => 'Support Desk Inquiries',
        'support.show' => 'View Support Ticket',
        'support.reply' => 'Send Ticket Reply',

        'rbac.index' => 'Access Management (RBAC)',
        'rbac.roles.store' => 'Create Custom Roles',
        'rbac.roles.delete' => 'Delete Custom Roles',
        'rbac.permissions' => 'Save Role Permissions Matrix',
        'rbac.users.store' => 'Create Staff User Accounts',
        'rbac.users.update' => 'Edit Staff User Accounts',
        'rbac.users.delete' => 'Delete Staff User Accounts',
        'rbac.user_role' => 'Assign Role to User',
        'rbac.user_permissions.sync' => 'Save User Custom Permissions',

        'settings.index' => 'General Store Settings',
        'settings.update' => 'Update Store Settings',
        'settings.couriers' => 'Courier API Integrations',
        'settings.payments' => 'Payment Gateways (bKash/SSL)',
        'settings.fraud' => 'Fraud Detection Engine API',
        'settings.gtm' => 'GTM & Analytics Settings',
        'settings.enterprise' => 'Enterprise Settings (Master View)',
        'settings.enterprise.update' => 'Update Enterprise Settings',
        'settings.backup' => 'Trigger Database Backup',
        'media.upload' => 'Upload & Optimize Media (WebP)',
    ];

    protected array $groupTitles = [
        'orders' => 'Orders & Invoices',
        'logistics' => 'Courier Logistics',
        'refunds' => 'Refunds & Returns',
        'products' => 'Products Catalog',
        'categories' => 'Categories',
        'banners' => 'Banners & Sliders',
        'reports' => 'Financial Reports',
        'audit-logs' => 'Audit Trails',
        'customers' => 'Customer CRM',
        'abandoned-carts' => 'Abandoned Carts',
        'fraud-checker' => 'Fraud Detection',
        'coupons' => 'Marketing & Coupons',
        'support' => 'Customer Support',
        'rbac' => 'RBAC & Security',
        'settings' => 'Store Settings',
        'enterprise-settings' => 'Enterprise Config',
        'media' => 'Media & Storage',
    ];

    /**
     * Clean and synchronize named routes into permissions database.
     */
    public function sync(): array
    {
        $created = 0;
        $skipped = 0;
        $validPermissions = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (!$name || !str_starts_with($name, 'admin.')) {
                continue;
            }

            // Skip exempt utility & json routes
            if (in_array($name, $this->exempt, true) || isset($this->routeAliasMap[$name])) {
                $skipped++;
                continue;
            }

            $uri = $route->uri();
            $segments = explode('/', $uri);
            $groupSegment = $segments[1] ?? 'general';
            $groupName = $this->groupTitles[$groupSegment] ?? ucfirst(str_replace('-', ' ', $groupSegment));

            $shortName = preg_replace('/^admin\./', '', $name);
            $displayName = $this->actionLabels[$shortName]
                ?? ucwords(str_replace(['.', '_', '-'], ' ', $shortName));

            $validPermissions[] = $name;

            $exists = DB::table('permissions')->where('name', $name)->exists();
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'group_name' => $groupName,
                    'display_name' => $displayName,
                    'updated_at' => now(),
                ]
            );

            if (!$exists) {
                $created++;
            }
        }

        // Clean up obsolete/duplicate permission records that are no longer in valid list
        if (!empty($validPermissions)) {
            // Remove permissions that are in exempt or alias list
            DB::table('permissions')
                ->whereIn('name', array_merge($this->exempt, array_keys($this->routeAliasMap)))
                ->delete();
        }

        return [
            'created' => $created,
            'total' => count($validPermissions),
            'skipped' => $skipped,
        ];
    }
}
