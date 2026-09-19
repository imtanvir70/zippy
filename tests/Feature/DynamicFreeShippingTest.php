<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DynamicFreeShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_free_shipping_settings(): void
    {
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@Zippy.com',
            'password' => bcrypt('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'admin_id' => $adminId,
            'admin_logged_in' => true,
            'admin_role' => 'superadmin',
            'admin_name' => 'Admin User'
        ])->post('/admin/settings/update', [
            'shipping_dhaka' => '70',
            'shipping_outside' => '130',
            'free_shipping_enabled' => '1',
            'free_shipping_min_amount' => '3000',
        ]);

        $response->assertStatus(302);

        $this->assertEquals('1', DB::table('settings')->where('key', 'free_shipping_enabled')->value('value'));
        $this->assertEquals('3000', DB::table('settings')->where('key', 'free_shipping_min_amount')->value('value'));
    }

    public function test_admin_can_save_product_with_free_shipping_flag(): void
    {
        $catId = DB::table('categories')->insertGetId([
            'name' => 'Gadgets',
            'name_bn' => 'গ্যাজেট',
            'slug' => 'gadgets',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@Zippy.com',
            'password' => bcrypt('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'admin_id' => $adminId,
            'admin_logged_in' => true,
            'admin_role' => 'superadmin',
            'admin_name' => 'Admin User'
        ])->postJson('/admin/products/ajax-save', [
            'title' => 'Smart Watch Free Shipping',
            'category_id' => $catId,
            'price' => 1500,
            'stock_qty' => 10,
            'is_active' => '1',
            'is_free_shipping' => '1',
        ]);

        $response->assertStatus(200);

        $prod = DB::table('products')->where('title', 'Smart Watch Free Shipping')->first();
        $this->assertNotNull($prod);
        $this->assertEquals(1, $prod->is_free_shipping);
    }

    public function test_checkout_shipping_fee_is_zero_when_global_free_shipping_qualifies(): void
    {
        DB::table('settings')->updateOrInsert(['key' => 'free_shipping_enabled'], ['value' => '1']);
        DB::table('settings')->updateOrInsert(['key' => 'free_shipping_min_amount'], ['value' => '2000']);
        DB::table('settings')->updateOrInsert(['key' => 'shipping_inside_dhaka'], ['value' => '60']);
        DB::table('settings')->updateOrInsert(['key' => 'shipping_outside_dhaka'], ['value' => '120']);

        \App\Services\Frontend\FrontendCacheService::flush();

        $catId = DB::table('categories')->insertGetId(['name' => 'Gear', 'name_bn' => 'গিয়ার', 'slug' => 'gear']);
        $pId = DB::table('products')->insertGetId([
            'category_id' => $catId,
            'title' => 'Expensive Watch',
            'slug' => 'expensive-watch',
            'sku' => 'EXP-01',
            'price' => 2500,
            'stock_qty' => 5,
            'main_image' => 'https://images.unsplash.com/test.jpg',
            'is_free_shipping' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cart = [
            $pId => [
                'id' => $pId,
                'title' => 'Expensive Watch',
                'price' => 2500,
                'qty' => 1,
                'variant' => null
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->get('/checkout');
        $response->assertStatus(200);
        $response->assertViewHas('shippingCost', 0);
        $response->assertViewHas('grandTotal', 2500);

        $orderResponse = $this->withSession(['cart' => $cart])->postJson('/checkout/process', [
            'customer_name' => 'John Doe',
            'customer_phone' => '01711111111',
            'customer_address' => 'Mirpur, Dhaka',
            'district' => 'ঢাকার বাইরে',
            'payment_method' => 'cod',
        ]);

        $orderResponse->assertStatus(200);
        $orderResponse->assertJson(['success' => true]);

        $order = DB::table('orders')->orderByDesc('id')->first();
        $this->assertEquals(0, (float)$order->shipping_cost);
        $this->assertEquals(2500, (float)$order->total);
    }

    public function test_checkout_shipping_fee_is_zero_when_cart_has_product_with_free_shipping(): void
    {
        DB::table('settings')->updateOrInsert(['key' => 'free_shipping_enabled'], ['value' => '0']);
        DB::table('settings')->updateOrInsert(['key' => 'free_shipping_min_amount'], ['value' => '5000']);
        DB::table('settings')->updateOrInsert(['key' => 'shipping_inside_dhaka'], ['value' => '60']);
        DB::table('settings')->updateOrInsert(['key' => 'shipping_outside_dhaka'], ['value' => '120']);

        \App\Services\Frontend\FrontendCacheService::flush();

        $catId = DB::table('categories')->insertGetId(['name' => 'Gear', 'name_bn' => 'গিয়ার', 'slug' => 'gear']);
        $pId = DB::table('products')->insertGetId([
            'category_id' => $catId,
            'title' => 'Free Ship Keyring',
            'slug' => 'free-ship-keyring',
            'sku' => 'FSK-01',
            'price' => 150,
            'stock_qty' => 5,
            'main_image' => 'https://images.unsplash.com/test.jpg',
            'is_free_shipping' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cart = [
            $pId => [
                'id' => $pId,
                'title' => 'Free Ship Keyring',
                'price' => 150,
                'qty' => 1,
                'variant' => null
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->get('/checkout');
        $response->assertStatus(200);
        $response->assertViewHas('shippingCost', 0);
        $response->assertViewHas('grandTotal', 150);

        $orderResponse = $this->withSession(['cart' => $cart])->postJson('/checkout/process', [
            'customer_name' => 'Jane Doe',
            'customer_phone' => '01811111111',
            'customer_address' => 'Chittagong Port',
            'district' => 'ঢাকার বাইরে',
            'payment_method' => 'cod',
        ]);

        $orderResponse->assertStatus(200);
        $order = DB::table('orders')->orderByDesc('id')->first();
        $this->assertEquals(0, (float)$order->shipping_cost);
        $this->assertEquals(150, (float)$order->total);
    }

    public function test_standard_shipping_applies_when_not_qualifying(): void
    {
        DB::table('settings')->updateOrInsert(['key' => 'free_shipping_enabled'], ['value' => '1']);
        DB::table('settings')->updateOrInsert(['key' => 'free_shipping_min_amount'], ['value' => '5000']);
        DB::table('settings')->updateOrInsert(['key' => 'shipping_inside_dhaka'], ['value' => '60']);
        DB::table('settings')->updateOrInsert(['key' => 'shipping_outside_dhaka'], ['value' => '120']);

        \App\Services\Frontend\FrontendCacheService::flush();

        $catId = DB::table('categories')->insertGetId(['name' => 'Gear', 'name_bn' => 'গিয়ার', 'slug' => 'gear']);
        $pId = DB::table('products')->insertGetId([
            'category_id' => $catId,
            'title' => 'Standard Item',
            'slug' => 'standard-item',
            'sku' => 'STD-01',
            'price' => 500,
            'stock_qty' => 5,
            'main_image' => 'https://images.unsplash.com/test.jpg',
            'is_free_shipping' => 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cart = [
            $pId => [
                'id' => $pId,
                'title' => 'Standard Item',
                'price' => 500,
                'qty' => 1,
                'variant' => null
            ]
        ];

        $orderResponse = $this->withSession(['cart' => $cart])->postJson('/checkout/process', [
            'customer_name' => 'Ordinary Customer',
            'customer_phone' => '01911111111',
            'customer_address' => 'Agrabad, Chittagong',
            'district' => 'ঢাকার বাইরে',
            'payment_method' => 'cod',
        ]);

        $orderResponse->assertStatus(200);
        $order = DB::table('orders')->orderByDesc('id')->first();
        $this->assertEquals(120, (float)$order->shipping_cost);
        $this->assertEquals(620, (float)$order->total);
    }
}
