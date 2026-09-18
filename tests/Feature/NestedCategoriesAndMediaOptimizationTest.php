<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Services\Media\ImageOptimizerService;

class NestedCategoriesAndMediaOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed basic settings
        DB::table('settings')->insertOrIgnore([
            ['key' => 'store_name', 'value' => 'ZippyBD', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_phone', 'value' => '01700000000', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'delivery_fee_dhaka', 'value' => '60', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'delivery_fee_outside', 'value' => '120', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('users')->insertOrIgnore([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@zippybd.com',
            'phone' => '01700000000',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function actingAsAdmin(): static
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_name' => 'Administrator',
            'admin_email' => 'admin@zippybd.com',
        ]);
    }

    public function test_image_optimizer_service_converts_image_to_webp(): void
    {
        $optimizer = new ImageOptimizerService();

        // Create a fake test image (PNG)
        $fakeImage = UploadedFile::fake()->image('test_product.png', 800, 600);

        $url = $optimizer->convertToWebp($fakeImage, 'products', 600, 85);

        $this->assertStringStartsWith('/storage/products/', $url);
        $this->assertStringEndsWith('.webp', $url);

        $diskPath = storage_path('app/public/' . str_replace('/storage/', '', $url));
        $this->assertTrue(File::exists($diskPath));

        // Cleanup
        $optimizer->deleteMedia($url);
        $this->assertFalse(File::exists($diskPath));
    }

    public function test_can_create_parent_and_nested_subcategories_via_ajax(): void
    {
        // 1. Create Parent Root Category
        $parentResp = $this->actingAsAdmin()->postJson(route('admin.categories.ajax_save'), [
            'name' => 'Electronics & Gadgets',
            'name_bn' => 'ইলেকট্রনিক্স ও গ্যাজেটস',
            'icon' => 'fa-solid fa-laptop',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $parentResp->assertStatus(200);
        $parentResp->assertJson(['success' => true]);
        $parentId = $parentResp->json('category.id');
        $this->assertNotNull($parentId);

        // 2. Create Child Subcategory under parent
        $subResp = $this->actingAsAdmin()->postJson(route('admin.categories.ajax_save'), [
            'name' => 'Smart Watches',
            'name_bn' => 'স্মার্ট ওয়াচ',
            'parent_id' => $parentId,
            'icon' => 'fa-solid fa-clock',
            'sort_order' => 2,
            'is_active' => 1,
        ]);

        $subResp->assertStatus(200);
        $subResp->assertJson(['success' => true]);
        $subId = $subResp->json('category.id');

        // Verify in DB via Query Builder
        $subDb = DB::table('categories')->where('id', $subId)->first();
        $this->assertEquals($parentId, $subDb->parent_id);
        $this->assertEquals('smart-watches', $subDb->slug);

        // 3. Verify Admin Categories Page loads hierarchy
        $indexResp = $this->actingAsAdmin()->get(route('admin.categories.index'));
        $indexResp->assertStatus(200);
        $indexResp->assertSee('Category Management');
        $indexResp->assertSee('Electronics &amp; Gadgets', false);

        // 4. Verify DataTables AJAX returns subcategory
        $dtResp = $this->actingAsAdmin()->getJson(route('admin.categories.index'), ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
        $dtResp->assertStatus(200);
        $dtResp->assertSee('Smart Watches');
    }

    public function test_category_file_upload_is_converted_to_webp(): void
    {
        $fakeImg = UploadedFile::fake()->image('cat_banner.jpg', 500, 500);

        $response = $this->actingAsAdmin()->post(route('admin.categories.ajax_save'), [
            'name' => 'Audio & Sound',
            'name_bn' => 'অডিও ও সাউন্ড',
            'image_file' => $fakeImg,
            'is_active' => 1,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $category = DB::table('categories')->where('slug', 'audio-sound')->first();
        $this->assertNotNull($category);
        $this->assertStringStartsWith('/storage/categories/', $category->image);
        $this->assertStringEndsWith('.webp', $category->image);
    }

    public function test_parent_category_catalog_shows_products_from_subcategories(): void
    {
        // 1. Create Parent Category
        $parentId = DB::table('categories')->insertGetId([
            'name' => 'Tech Essentials',
            'name_bn' => 'টেক এসেনশিয়াল',
            'slug' => 'tech-essentials',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Child Subcategory
        $subId = DB::table('categories')->insertGetId([
            'parent_id' => $parentId,
            'name' => 'Headphones & Earbuds',
            'name_bn' => 'হেডফোন ও ইয়ারবাডস',
            'slug' => 'headphones-earbuds',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Insert Product in Parent and Product in Child Subcategory
        $p1 = DB::table('products')->insertGetId([
            'category_id' => $parentId,
            'title' => 'Parent Level Laptop Stand',
            'slug' => 'parent-level-laptop-stand',
            'sku' => 'ZB-LS01',
            'price' => 1200,
            'stock_qty' => 10,
            'main_image' => 'https://placehold.co/400',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $p2 = DB::table('products')->insertGetId([
            'category_id' => $subId,
            'title' => 'Subcategory Wireless Buds X',
            'slug' => 'subcategory-wireless-buds-x',
            'sku' => 'ZB-BUDSX',
            'price' => 2500,
            'stock_qty' => 15,
            'main_image' => 'https://placehold.co/400',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Visit Parent Category Page - both products must be returned
        $resp = $this->get(route('category.show', 'tech-essentials'));
        $resp->assertStatus(200);
        $resp->assertSee('Parent Level Laptop Stand');
        $resp->assertSee('Subcategory Wireless Buds X');
    }

    public function test_banner_upload_is_converted_to_webp(): void
    {
        $fakeBanner = UploadedFile::fake()->image('mega_sale.jpg', 1920, 600);

        $response = $this->actingAsAdmin()->post(route('admin.banners.ajax_save'), [
            'title' => 'Mega Summer Sale',
            'title_bn' => 'মেগা সামার সেল',
            'subtitle' => 'Save up to 50% on gadgets',
            'image_file' => $fakeBanner,
            'button_text' => 'Shop Deals',
            'link' => '/category/tech-essentials',
            'is_active' => 1,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $banner = DB::table('banners')->where('title', 'Mega Summer Sale')->first();
        $this->assertNotNull($banner);
        $this->assertStringStartsWith('/storage/banners/', $banner->image_url);
        $this->assertStringEndsWith('.webp', $banner->image_url);
    }

    public function test_async_media_upload_endpoint(): void
    {
        $fakeFile = UploadedFile::fake()->image('async_upload.png', 400, 400);

        $resp = $this->actingAsAdmin()->post(route('admin.media.upload'), [
            'file' => $fakeFile,
            'folder' => 'general',
        ], ['Accept' => 'application/json']);

        $resp->assertStatus(200);
        $resp->assertJson(['success' => true]);
        $this->assertStringStartsWith('/storage/general/', $resp->json('url'));
        $this->assertStringEndsWith('.webp', $resp->json('url'));
    }
}
