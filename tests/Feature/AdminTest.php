<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Administrator User
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@Zippy.com',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed initial category and product for tests
        $catId = DB::table('categories')->insertGetId([
            'name' => 'Smart Watches',
            'name_bn' => 'স্মার্ট ওয়াচ',
            'slug' => 'smart-watches',
            'icon' => 'fa-solid fa-clock',
            'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30',
            'sort_order' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'category_id' => $catId,
            'title' => 'Test Smart Watch Ultra',
            'slug' => 'test-smart-watch-ultra',
            'sku' => 'SW-ULTRA-01',
            'price' => 1250.00,
            'old_price' => 1850.00,
            'stock_qty' => 25,
            'rating' => 4.8,
            'reviews_count' => 12,
            'tag' => 'Best Seller',
            'badge_type' => 'best_seller',
            'is_flash_deal' => 1,
            'is_featured' => 1,
            'is_active' => 1,
            'main_image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30',
            'variants' => json_encode([
                ['name' => 'Black', 'price' => 1250, 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30', 'stock' => 15],
                ['name' => 'Silver', 'price' => 1350, 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30', 'stock' => 10]
            ]),
            'specifications' => json_encode(['Bluetooth' => '5.3', 'Battery' => '300mAh']),
            'short_desc' => 'Test description',
            'description' => 'Detailed test description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->insert([
            ['key' => 'store_name', 'value' => 'Zippy', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_phone', 'value' => '01700000000', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_whatsapp', 'value' => '01700000000', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'shipping_dhaka', 'value' => '60', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'shipping_outside', 'value' => '120', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    protected function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_name' => 'Administrator',
            'admin_email' => 'admin@Zippy.com'
        ]);
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_page_loads_with_prefilled_credentials()
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('admin@Zippy.com');
        $response->assertSee('admin123');
        $response->assertSee('Solve IT');
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@Zippy.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_logged_in'));
    }

    public function test_admin_login_fails_with_invalid_credentials()
    {
        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@Zippy.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(session()->has('admin_logged_in'));
    }

    public function test_admin_can_logout()
    {
        $response = $this->actingAsAdmin()->post(route('admin.logout'));
        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(session()->has('admin_logged_in'));
    }

    public function test_admin_dashboard_loads_for_authenticated_user()
    {
        $response = $this->actingAsAdmin()->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('প্রজেক্ট ওভারভিউ');
    }

    public function test_admin_dashboard_filters_by_custom_date_range()
    {
        $response = $this->actingAsAdmin()->get(route('admin.dashboard', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Custom Range Filter');
    }

    public function test_admin_orders_index_loads()
    {
        $response = $this->actingAsAdmin()->get(route('admin.orders.index'));
        $response->assertStatus(200);
        $response->assertSee('Customer Orders Management');

        // Test DataTables AJAX endpoint
        $dtResponse = $this->actingAsAdmin()->getJson(route('admin.orders.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtResponse->assertStatus(200);
        $dtResponse->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_admin_orders_ajax_details_and_call_status()
    {
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ORD-TEST-' . uniqid(),
            'customer_name' => 'Tanvir Ahmed',
            'customer_phone' => '01711998877',
            'customer_address' => 'House 12, Road 4, Dhanmondi',
            'district' => 'Dhaka',
            'subtotal' => 1200.00,
            'shipping_cost' => 60.00,
            'discount' => 0.00,
            'total' => 1260.00,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'call_status' => 'pending_call',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => 1,
            'product_title' => 'Test Product',
            'unit_price' => 600.00,
            'quantity' => 2,
            'total_price' => 1200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detailsRes = $this->actingAsAdmin()->getJson(route('admin.orders.ajax_details', $orderId));
        $detailsRes->assertStatus(200);
        $detailsRes->assertJson(['success' => true]);
        $detailsRes->assertJsonPath('order.customer_name', 'Tanvir Ahmed');
        $detailsRes->assertJsonPath('order.call_status', 'pending_call');
        $this->assertCount(1, $detailsRes->json('items'));

        $callRes = $this->actingAsAdmin()->postJson(route('admin.orders.ajax_call_status', $orderId), [
            'call_status' => 'confirmed',
            'call_note' => 'Customer confirmed delivery today'
        ]);
        $callRes->assertStatus(200);
        $callRes->assertJson([
            'success' => true,
            'call_status' => 'confirmed',
            'call_note' => 'Customer confirmed delivery today'
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'call_status' => 'confirmed',
            'call_note' => 'Customer confirmed delivery today'
        ]);

        $filterDtRes = $this->actingAsAdmin()->getJson(route('admin.orders.index', ['call_status' => 'confirmed']), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $filterDtRes->assertStatus(200);
    }

    public function test_admin_products_index_loads()
    {
        $response = $this->actingAsAdmin()->get(route('admin.products.index'));
        $response->assertStatus(200);
        $response->assertSee('Products Catalog');

        // Test DataTables AJAX endpoint
        $dtResponse = $this->actingAsAdmin()->getJson(route('admin.products.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtResponse->assertStatus(200);
        $dtResponse->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_admin_categories_index_loads()
    {
        $response = $this->actingAsAdmin()->get(route('admin.categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Category Management');

        // Test DataTables AJAX endpoint
        $dtResponse = $this->actingAsAdmin()->getJson(route('admin.categories.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtResponse->assertStatus(200);
        $dtResponse->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_admin_banners_index_loads()
    {
        $response = $this->actingAsAdmin()->get(route('admin.banners.index'));
        $response->assertStatus(200);
        $response->assertSee('Hero Banners');
    }

    public function test_admin_customers_index_loads()
    {
        $response = $this->actingAsAdmin()->get(route('admin.customers.index'));
        $response->assertStatus(200);
        $response->assertSee('Customers Directory');

        // Test DataTables AJAX endpoint
        $dtResponse = $this->actingAsAdmin()->getJson(route('admin.customers.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtResponse->assertStatus(200);
        $dtResponse->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_admin_settings_index_loads()
    {
        $response = $this->actingAsAdmin()->get(route('admin.settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Store Settings');
    }

    public function test_admin_can_toggle_product_status()
    {
        $product = DB::table('products')->first();
        $response = $this->actingAsAdmin()->postJson(route('admin.products.toggle', $product->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_product_json_endpoint()
    {
        $product = DB::table('products')->first();
        $response = $this->actingAsAdmin()->getJson(route('admin.products.json', $product->id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'product',
            'variants',
            'specifications',
            'gallery_text'
        ]);
        $this->assertEquals('Test Smart Watch Ultra', $response->json('product.title'));
    }

    public function test_admin_product_ajax_save_create_and_update()
    {
        $cat = DB::table('categories')->first();

        // 1. AJAX Create
        $createResponse = $this->actingAsAdmin()->postJson(route('admin.products.ajax_save'), [
            'title' => 'AJAX Created Gadget Pro',
            'category_id' => $cat->id,
            'price' => 999.00,
            'stock_qty' => 30,
            'main_image' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12',
            'var_name' => ['Midnight Black', 'Glossy White'],
            'var_price' => [999, 1099],
            'var_stock' => [15, 15],
            'spec_key' => ['Bluetooth', 'Battery'],
            'spec_val' => ['5.3', '40 Hours'],
            'is_active' => 'on'
        ]);

        $createResponse->assertStatus(200);
        $createResponse->assertJson(['success' => true]);
        $newProductId = $createResponse->json('product.id');
        $this->assertNotNull($newProductId);

        // 2. AJAX Update
        $updateResponse = $this->actingAsAdmin()->postJson(route('admin.products.ajax_save'), [
            'id' => $newProductId,
            'title' => 'AJAX Updated Gadget Pro Max',
            'category_id' => $cat->id,
            'price' => 1199.00,
            'stock_qty' => 45,
            'main_image' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12',
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        $updated = DB::table('products')->where('id', $newProductId)->first();
        $this->assertEquals('AJAX Updated Gadget Pro Max', $updated->title);
        $this->assertEquals(1199.00, $updated->price);
    }

    public function test_admin_product_ajax_delete()
    {
        $product = DB::table('products')->first();
        $response = $this->actingAsAdmin()->deleteJson(route('admin.products.ajax_delete', $product->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNull(DB::table('products')->where('id', $product->id)->first());
    }

    public function test_verify_otp_ajax_valid()
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'admin@Zippy.com'],
            ['token' => Hash::make('123456'), 'created_at' => now()]
        );

        $response = $this->postJson(route('admin.forgot_password.verify_otp_ajax'), [
            'email' => 'admin@Zippy.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'message' => 'Code verified successfully!',
        ]);
    }

    public function test_verify_otp_ajax_invalid()
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'admin@Zippy.com'],
            ['token' => Hash::make('123456'), 'created_at' => now()]
        );

        $response = $this->postJson(route('admin.forgot_password.verify_otp_ajax'), [
            'email' => 'admin@Zippy.com',
            'otp' => '999999',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
            'message' => 'The verification code entered is invalid.',
        ]);
    }

    public function test_verify_otp_ajax_expired()
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'admin@Zippy.com'],
            ['token' => Hash::make('123456'), 'created_at' => now()->subMinutes(20)]
        );

        $response = $this->postJson(route('admin.forgot_password.verify_otp_ajax'), [
            'email' => 'admin@Zippy.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
            'message' => 'Verification code has expired. Please request a new code.',
        ]);
    }
}
