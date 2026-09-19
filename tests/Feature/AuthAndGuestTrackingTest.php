<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthAndGuestTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * 1. Test Customer Registration and Auto Login via Query Builder.
     */
    public function test_customer_registration_and_auto_login(): void
    {
        $deviceToken = 'zb_dev_test_reg_1234567890';

        $response = $this->post('/register', [
            'name' => 'Tanvir Ahmed',
            'email' => 'tanvir@test.com',
            'phone' => '01711002233',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'device_token' => $deviceToken,
        ]);

        $response->assertRedirect(route('customer.account'));
        $this->assertAuthenticated('web');

        $this->assertDatabaseHas('users', [
            'email' => 'tanvir@test.com',
            'phone' => '01711002233',
            'role' => 'customer',
        ]);
    }

    /**
     * 2. Test Customer Login with Email and Phone via Query Builder.
     */
    public function test_customer_login_with_email_and_phone(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Rahim Customer',
            'email' => 'rahim@test.com',
            'phone' => '01888123456',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Login with Email
        $loginRes = $this->post('/login', [
            'login' => 'rahim@test.com',
            'password' => 'password123',
        ]);
        $loginRes->assertRedirect(route('customer.account'));
        $this->assertAuthenticated('web');

        // Logout
        $this->post('/logout');
        $this->assertGuest('web');

        // Login with Phone
        $loginPhoneRes = $this->post('/login', [
            'login' => '01888123456',
            'password' => 'password123',
        ]);
        $loginPhoneRes->assertRedirect(route('customer.account'));
        $this->assertAuthenticated('web');
    }

    /**
     * 3. Test Guest Checkout creates Order with Device Token & updates GuestDevice table.
     */
    public function test_guest_checkout_records_device_token_and_guest_device(): void
    {
        $product = DB::table('products')->first();
        $this->assertNotNull($product);

        // Put item in cart
        $this->withSession([
            'cart' => [
                $product->id => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'image' => $product->main_image,
                    'qty' => 2,
                    'variant' => null,
                ]
            ]
        ]);

        $deviceToken = 'zb_dev_guest_device_998877';

        $response = $this->postJson('/checkout/process', [
            'customer_name' => 'Guest Buyer',
            'customer_phone' => '01999887766',
            'customer_address' => 'House 4, Road 12, Banani, Dhaka',
            'district' => 'ঢাকা',
            'payment_method' => 'cod',
            'device_token' => $deviceToken,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $orderNumber = $response->json('order_number');
        $this->assertNotNull($orderNumber);

        // Check order record in DB
        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
            'device_token' => $deviceToken,
            'is_guest' => 1,
            'customer_phone' => '01999887766',
        ]);

        // Check guest_devices record in DB
        $this->assertDatabaseHas('guest_devices', [
            'device_token' => $deviceToken,
            'last_phone' => '01999887766',
            'total_orders' => 1,
        ]);
    }

    /**
     * 4. Test Guest Order History page and API.
     */
    public function test_guest_order_history_page_and_api(): void
    {
        $deviceToken = 'zb_dev_history_device_112233';

        // Insert order for this device via DB Query Builder
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-TEST-HIST01',
            'device_token' => $deviceToken,
            'is_guest' => 1,
            'customer_name' => 'Device Tester',
            'customer_phone' => '01700998877',
            'customer_address' => 'Mirpur 10, Dhaka',
            'district' => 'ঢাকা',
            'subtotal' => 1500,
            'shipping_cost' => 60,
            'discount' => 0,
            'total' => 1560,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Order item via DB Query Builder
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => 1,
            'product_title' => 'Sample Mechanical Keyboard',
            'unit_price' => 1500,
            'quantity' => 1,
            'total_price' => 1500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Check Web Route
        $response = $this->withCookie('zb_device_token', $deviceToken)->get('/order/history');
        $response->assertStatus(200);
        $response->assertSee('ZB-TEST-HIST01');
        $response->assertSee('Sample Mechanical Keyboard');

        // 2. Check JSON API
        $apiResponse = $this->postJson('/api/device/orders', [
            'device_token' => $deviceToken
        ]);
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment(['order_number' => 'ZB-TEST-HIST01']);
    }

    /**
     * 5. Test Guest Clearing Device History via Query Builder.
     */
    public function test_guest_clearing_device_history(): void
    {
        $deviceToken = 'zb_dev_clear_test_776655';

        // Record guest device via DB Query Builder
        DB::table('guest_devices')->insert([
            'device_token' => $deviceToken,
            'last_phone' => '01711223344',
            'total_orders' => 1,
            'total_spent' => 1200,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clearRes = $this->postJson('/api/device/clear-history', [
            'device_token' => $deviceToken
        ]);

        $clearRes->assertStatus(200);
        $clearRes->assertJson(['success' => true]);

        // Device should be marked as unlinked in DB
        $device = DB::table('guest_devices')->where('device_token', $deviceToken)->first();
        $this->assertNotNull($device->unlinked_at);

        // Fetching orders for that cleared device token should now return empty
        $apiResponse = $this->postJson('/api/device/orders', [
            'device_token' => $deviceToken
        ]);
        $this->assertEquals(0, $apiResponse->json('count'));
    }

    /**
     * 6. Test Auto Claiming of Guest Orders upon Customer Registration via DB Query Builder.
     */
    public function test_claiming_guest_orders_on_registration(): void
    {
        $deviceToken = 'zb_dev_claim_test_554433';
        $phone = '01799882211';

        // Create guest order via DB Query Builder
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-CLAIM-99',
            'device_token' => $deviceToken,
            'is_guest' => 1,
            'user_id' => null,
            'customer_name' => 'Unregistered Guest',
            'customer_phone' => $phone,
            'customer_address' => 'Uttara Sector 3, Dhaka',
            'district' => 'ঢাকা',
            'subtotal' => 2000,
            'shipping_cost' => 60,
            'discount' => 0,
            'total' => 2060,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Register with that phone and device token
        $this->post('/register', [
            'name' => 'Claimed User',
            'email' => 'claimed@test.com',
            'phone' => $phone,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'device_token' => $deviceToken,
        ]);

        $user = DB::table('users')->where('email', 'claimed@test.com')->first();
        $this->assertNotNull($user);

        // Assert order is now linked to user in DB
        $updatedOrder = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals($user->id, $updatedOrder->user_id);
        $this->assertEquals(0, $updatedOrder->is_guest);
    }

    /**
     * 7. Test Admin View of Guest Metadata & Device Unlinking.
     */
    public function test_admin_can_view_guest_order_metadata_and_unlink_session(): void
    {
        $deviceToken = 'zb_dev_admin_unlink_11';

        DB::table('guest_devices')->insert([
            'device_token' => $deviceToken,
            'last_phone' => '01788776655',
            'total_orders' => 1,
            'total_spent' => 950,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-ADMIN-DEV-01',
            'device_token' => $deviceToken,
            'is_guest' => 1,
            'user_id' => null,
            'customer_name' => 'Device Guest',
            'customer_phone' => '01788776655',
            'customer_address' => 'Gulshan 2, Dhaka',
            'district' => 'ঢাকা',
            'subtotal' => 950,
            'shipping_cost' => 60,
            'discount' => 0,
            'total' => 1010,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Login as admin
        $admin = DB::table('users')->first();
        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'admin_email' => $admin->email,
        ]);

        // View order details
        $showRes = $this->get("/admin/orders/{$orderId}");
        $showRes->assertStatus(200);
        $showRes->assertSee('Guest Device Order');
        $showRes->assertSee($deviceToken);

        // Admin unlinks device session
        $unlinkRes = $this->postJson("/admin/orders/unlink-device/{$deviceToken}");
        $unlinkRes->assertStatus(200);
        $unlinkRes->assertJson(['success' => true]);

        $device = DB::table('guest_devices')->where('device_token', $deviceToken)->first();
        $this->assertNotNull($device->unlinked_at);
    }

    /**
     * 8. Test Social Redirect handles unconfigured credentials gracefully.
     */
    public function test_social_redirect_handles_unconfigured_gracefully(): void
    {
        $response = $this->get('/auth/google');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    /**
     * 9. Test Admin Login via main login route redirects to admin dashboard and sets admin session.
     */
    public function test_admin_login_via_main_login_route_redirects_to_admin_dashboard(): void
    {
        $admin = DB::table('users')->where('role', 'admin')->first();
        if (!$admin) {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Admin Boss',
                'email' => 'admin_boss@Zippy.com',
                'password' => Hash::make('secret123'),
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $admin = DB::table('users')->where('id', $adminId)->first();
        }

        $res = $this->post('/login', [
            'login' => $admin->email,
            'password' => 'admin123',
        ]);

        $res->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('web');
        $this->assertTrue(session('admin_logged_in'));
    }

    /**
     * 10. Test Login with Username / Name instead of Email via Query Builder.
     */
    public function test_login_with_username(): void
    {
        DB::table('users')->insert([
            'name' => 'johndoe',
            'email' => 'john@example.com',
            'phone' => '01555555555',
            'password' => Hash::make('secret123'),
            'role' => 'customer',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = $this->post('/login', [
            'login' => 'johndoe',
            'password' => 'secret123',
        ]);

        $res->assertRedirect(route('customer.account'));
        $this->assertAuthenticated('web');
    }
}
