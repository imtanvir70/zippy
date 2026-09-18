<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AdminModulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_all_backend_admin_modules_respond_cleanly()
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'EcommerceNew',
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        $admin = DB::table('users')->where('role', 'super_admin')->first();
        if (!$admin) {
            $admin = DB::table('users')->first();
        }

        $sessionData = [
            'admin_id' => $admin->id ?? 1,
            'admin_logged_in' => true,
        ];

        $routes = [
            'admin.dashboard',
            'admin.orders.index',
            'admin.logistics.index',
            'admin.refunds.index',
            'admin.products.index',
            'admin.categories.index',
            'admin.banners.index',
            'admin.reports.index',
            'admin.audit.index',
            'admin.customers.index',
            'admin.abandoned.index',
            'admin.fraud.index',
            'admin.coupons.index',
            'admin.support.index',
            'admin.rbac.index',
            'admin.settings.index',
            'admin.settings.enterprise',
        ];

        foreach ($routes as $route) {
            $response = $this->withSession($sessionData)->get(route($route));
            $this->assertContains(
                $response->status(),
                [200, 302],
                "Route [{$route}] failed with status: " . $response->status() . " - " . substr($response->getContent(), 0, 300)
            );
        }

        // Test Order Show, Invoice & Slip via order_number
        $order = DB::table('orders')->first();
        if ($order) {
            $showRes = $this->withSession($sessionData)->get(route('admin.orders.show', $order->order_number));
            $this->assertEquals(200, $showRes->status(), "admin.orders.show with order_number failed");

            $invoiceRes = $this->withSession($sessionData)->get(route('admin.orders.invoice', $order->order_number));
            $this->assertEquals(200, $invoiceRes->status(), "admin.orders.invoice with order_number failed");

            $slipRes = $this->withSession($sessionData)->get(route('admin.orders.packing_slip', $order->order_number));
            $this->assertEquals(200, $slipRes->status(), "admin.orders.packing_slip with order_number failed");
        }

        // Test New Modules (Inventory, SMS, Pages, Reviews)
        $newModuleRoutes = [
            'admin.inventory.index',
            'admin.sms.index',
            'admin.pages.index',
            'admin.reviews.index',
        ];
        foreach ($newModuleRoutes as $mRoute) {
            $mRes = $this->withSession($sessionData)->get(route($mRoute));
            $this->assertEquals(200, $mRes->status(), "Route [{$mRoute}] failed with status: " . $mRes->status());
        }

        // Test CRM Customer Show
        $customerOrder = DB::table('orders')->whereNotNull('customer_phone')->first();
        if ($customerOrder) {
            $crmRes = $this->withSession($sessionData)->get(route('admin.crm.show', $customerOrder->customer_phone));
            $this->assertEquals(200, $crmRes->status(), "admin.crm.show failed");
        }
    }
}
