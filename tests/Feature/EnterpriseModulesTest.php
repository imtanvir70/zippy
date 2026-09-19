<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnterpriseModulesTest extends TestCase
{
    use RefreshDatabase;

    protected int $adminUserId;
    protected int $orderId;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Admin User
        $this->adminUserId = DB::table('users')->insertGetId([
            'name' => 'Administrator',
            'email' => 'admin@Zippy.com',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Category & Product
        $catId = DB::table('categories')->insertGetId([
            'name' => 'Smart Watches',
            'name_bn' => 'স্মার্ট ওয়াচ',
            'slug' => 'smart-watches',
            'sort_order' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'category_id' => $catId,
            'title' => 'Apple Watch Series Ultra',
            'slug' => 'apple-watch-series-ultra',
            'sku' => 'AW-ULTRA-01',
            'price' => 3500.00,
            'stock_qty' => 50,
            'is_active' => 1,
            'main_image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Seed Order & Order Items
        $this->orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-TEST-9901',
            'customer_name' => 'Tanvir Ahmed',
            'customer_phone' => '01712345678',
            'customer_address' => 'House 12, Road 5, Dhanmondi',
            'district' => 'Dhaka',
            'subtotal' => 7000.00,
            'shipping_cost' => 60.00,
            'discount' => 0.00,
            'total' => 7060.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'fraud_score' => 10,
            'fraud_status' => 'safe',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $this->orderId,
            'product_id' => $productId,
            'product_title' => 'Apple Watch Series Ultra',
            'unit_price' => 3500.00,
            'quantity' => 2,
            'total_price' => 7000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Seed Enterprise Settings
        DB::table('settings')->insert([
            ['key' => 'store_name', 'value' => 'Zippy', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'courier_default', 'value' => 'steadfast', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'gtm_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'gtm_container_id', 'value' => 'GTM-TEST123', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Seed Roles and Permissions
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'super_admin',
            'display_name' => 'Super Administrator',
            'description' => 'Full unrestricted access to all modules',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permId = DB::table('permissions')->insertGetId([
            'name' => 'dashboard.view',
            'group_name' => 'Dashboard',
            'display_name' => 'View Dashboard Analytics',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permId
        ]);
    }

    protected function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $this->adminUserId,
            'admin_name' => 'Administrator',
            'admin_email' => 'admin@Zippy.com'
        ]);
    }

    public function test_courier_dispatch_and_tracking_lookup()
    {
        // 1. Dispatch order to Steadfast
        $response = $this->actingAsAdmin()->postJson(route('admin.logistics.dispatch', $this->orderId), [
            'provider' => 'steadfast'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $trackingCode = $response->json('tracking_code');
        $this->assertNotEmpty($trackingCode);

        // Check order status changed to shipped
        $updatedOrder = DB::table('orders')->where('id', $this->orderId)->first();
        $this->assertEquals('shipped', $updatedOrder->order_status);
        $this->assertEquals('steadfast', $updatedOrder->courier_provider);
        $this->assertEquals($trackingCode, $updatedOrder->courier_tracking_code);

        // 2. Track Consignment
        $trackResponse = $this->actingAsAdmin()->getJson(route('admin.logistics.track', $trackingCode));
        $trackResponse->assertStatus(200);
        $trackResponse->assertJson(['success' => true, 'tracking_code' => $trackingCode]);
    }

    public function test_rma_refund_processing_with_stock_restoration()
    {
        $initialStock = DB::table('products')->where('slug', 'apple-watch-series-ultra')->value('stock_qty');

        $response = $this->actingAsAdmin()->postJson(route('admin.refunds.store', $this->orderId), [
            'amount' => 7060.00,
            'reason' => 'Defective screen on arrival',
            'refund_type' => 'full',
            'restock_inventory' => 'on'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify refund recorded
        $refund = DB::table('refunds')->where('order_id', $this->orderId)->first();
        $this->assertNotNull($refund);
        $this->assertEquals(7060.00, $refund->amount);

        // Verify order status cancelled and refunded amount updated
        $order = DB::table('orders')->where('id', $this->orderId)->first();
        $this->assertEquals('cancelled', $order->order_status);
        $this->assertEquals(7060.00, $order->refunded_amount);

        // Verify inventory restored by +2
        $newStock = DB::table('products')->where('slug', 'apple-watch-series-ultra')->value('stock_qty');
        $this->assertEquals($initialStock + 2, $newStock);
    }

    public function test_real_time_fraud_scoring_engine()
    {
        \Illuminate\Support\Facades\Http::fake([
            '*courier-check*' => \Illuminate\Support\Facades\Http::response([
                'status' => 'success',
                'data' => [
                    'summary' => [
                        'total_parcel' => 10,
                        'success_parcel' => 9,
                        'cancelled_parcel' => 1,
                        'success_ratio' => 90.0
                    ]
                ],
                'reports' => []
            ], 200)
        ]);

        $fraudService = new \App\Services\Fraud\FraudDetectionService();

        // Safe order test
        $safeResult = $fraudService->evaluateOrder([
            'customer_phone' => '01711223344',
            'customer_name' => 'Karim Hasan',
            'customer_address' => 'House 4, Road 2, Banani, Dhaka',
            'district' => 'Dhaka',
            'total' => 1200,
            'payment_method' => 'cod'
        ]);
        $this->assertEquals('safe', $safeResult['fraud_status']);
        $this->assertLessThan(35, $safeResult['fraud_score']);

        // Suspicious / Junk address test
        $fraudResult = $fraudService->evaluateOrder([
            'customer_phone' => '01711223344',
            'customer_name' => 'asdf fake user',
            'customer_address' => 'test test 1234',
            'district' => 'Dhaka',
            'total' => 15000,
            'payment_method' => 'cod'
        ]);
        $this->assertGreaterThanOrEqual(35, $fraudResult['fraud_score']);
        $this->assertContains('suspicious', [$fraudResult['fraud_status'], 'flagged_fraud']);
    }

    public function test_financial_report_and_csv_generation()
    {
        $response = $this->actingAsAdmin()->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Gross Sales Revenue');

        $csvResponse = $this->actingAsAdmin()->get(route('admin.reports.export_csv'));
        $csvResponse->assertStatus(200);
        $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_product_catalog_csv_export_and_import()
    {
        // 1. Export CSV
        $exportResponse = $this->actingAsAdmin()->get(route('admin.products.export_csv'));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // 2. Import CSV
        $importService = new \App\Services\Inventory\ProductImportExportService();
        $tempCsv = tempnam(sys_get_temp_dir(), 'csv_test');
        file_put_contents($tempCsv, "ID,SKU,Title,Category,Price,Old Price,Stock Quantity,Rating,Reviews Count,Tag,Badge Type,Is Flash Deal,Is Featured,Is Active,Main Image,Short Description\n,ZB-CSV-01,Test CSV Imported Earphones,Smart Watches,1500,2000,40,5.0,8,Trending,hot,0,1,1,/images/product-placeholder.svg,Great sound quality\n");

        $importResult = $importService->importProductsCsv($tempCsv);
        unlink($tempCsv);

        $this->assertTrue($importResult['success']);
        $this->assertTrue(DB::table('products')->where('sku', 'ZB-CSV-01')->exists());
    }

    public function test_coupon_validation_and_application()
    {
        // Seed test coupon
        DB::table('coupons')->insert([
            'code' => 'TEST50',
            'type' => 'fixed',
            'value' => 50.00,
            'min_order_amount' => 500.00,
            'usage_limit' => 10,
            'used_count' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $couponService = new \App\Services\Marketing\CouponService();

        // 1. Valid application
        $result = $couponService->validateAndApply('TEST50', 1000.00);
        $this->assertTrue($result['success']);
        $this->assertEquals(50.00, $result['discount']);

        // 2. Min order condition failure
        $failResult = $couponService->validateAndApply('TEST50', 300.00);
        $this->assertFalse($failResult['success']);
    }

    public function test_customer_crm_and_wallet_adjustment()
    {
        $response = $this->actingAsAdmin()->get(route('admin.customers.index'));
        $response->assertStatus(200);
        $response->assertSee('Customers Directory');

        // Test DataTables AJAX
        $dtResponse = $this->actingAsAdmin()->getJson(route('admin.customers.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtResponse->assertStatus(200);
        $dtResponse->assertSee('Tanvir Ahmed');

        // Adjust Wallet
        $adjustResponse = $this->actingAsAdmin()->post(route('admin.crm.wallet', '01712345678'), [
            'type' => 'credit',
            'amount' => 250.00,
            'points' => 50,
            'description' => 'Promotional cashback bonus'
        ]);

        $adjustResponse->assertSessionHas('success');
        $wallet = DB::table('customer_wallets')->where('customer_phone', '01712345678')->first();
        $this->assertEquals(250.00, $wallet->balance);
        $this->assertEquals(50, $wallet->reward_points);
    }

    public function test_abandoned_cart_and_support_tickets()
    {
        // 1. Seed Abandoned Cart
        $cartId = DB::table('abandoned_carts')->insertGetId([
            'session_id' => 'sess_test_123',
            'customer_name' => 'Habibullah',
            'customer_phone' => '01811223344',
            'cart_data' => json_encode([['title' => 'Watch', 'price' => 1200, 'qty' => 1]]),
            'total_amount' => 1200.00,
            'is_recovered' => 0,
            'last_activity_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $abandonedResponse = $this->actingAsAdmin()->get(route('admin.abandoned.index'));
        $abandonedResponse->assertStatus(200);
        $abandonedResponse->assertSee('Abandoned Carts Recovery Hub');

        // Test DataTables AJAX
        $dtResponse = $this->actingAsAdmin()->getJson(route('admin.abandoned.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtResponse->assertStatus(200);
        $dtResponse->assertSee('Habibullah');

        // Recover Cart
        $recoverResponse = $this->actingAsAdmin()->postJson(route('admin.abandoned.recover', $cartId));
        $recoverResponse->assertStatus(200);
        $this->assertEquals(1, DB::table('abandoned_carts')->where('id', $cartId)->value('is_recovered'));

        // 2. Support Ticket Reply
        $ticketId = DB::table('support_tickets')->insertGetId([
            'ticket_number' => 'TCK-TEST-99',
            'customer_name' => 'Nusrat Jahan',
            'customer_phone' => '01911223344',
            'subject' => 'Payment Confirmation Query',
            'message' => 'Did my bKash payment go through?',
            'priority' => 'high',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $replyResponse = $this->actingAsAdmin()->post(route('admin.support.reply', $ticketId), [
            'status' => 'resolved',
            'admin_reply' => 'Yes, your payment has been verified successfully.'
        ]);

        $replyResponse->assertSessionHas('success');
        $updatedTicket = DB::table('support_tickets')->where('id', $ticketId)->first();
        $this->assertEquals('resolved', $updatedTicket->status);
        $this->assertNotEmpty($updatedTicket->admin_reply);
    }

    public function test_rbac_and_enterprise_settings()
    {
        // 1. RBAC Index
        $rbacResponse = $this->actingAsAdmin()->get(route('admin.rbac.index'));
        $rbacResponse->assertStatus(200);
        $rbacResponse->assertSee('Super Administrator');

        // 2. Enterprise Settings View & Update
        $settingsResponse = $this->actingAsAdmin()->get(route('admin.settings.enterprise'));
        $settingsResponse->assertStatus(200);
        $settingsResponse->assertSee('BD Courier Integrations API');

        $updateResponse = $this->actingAsAdmin()->postJson(route('admin.settings.enterprise.update'), [
            'bkash_merchant_number' => '01711998877',
            'gtm_container_id' => 'GTM-UPDATED99'
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);
        $this->assertEquals('01711998877', DB::table('settings')->where('key', 'bkash_merchant_number')->value('value'));
    }
}
