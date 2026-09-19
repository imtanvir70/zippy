<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Services\Media\ImageOptimizerService;

class ProductDuplicateImageCloneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('settings')->insertOrIgnore([
            ['key' => 'store_name', 'value' => 'Zippy', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('users')->insertOrIgnore([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@Zippy.com',
            'phone' => '01700000000',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('categories')->insert([
            'id' => 1,
            'name' => 'Smartwatches',
            'name_bn' => 'স্মার্টওয়াচ',
            'slug' => 'smartwatches',
            'is_active' => 1,
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
            'admin_email' => 'admin@Zippy.com',
        ]);
    }

    public function test_product_duplication_clones_physical_images_and_protects_original(): void
    {
        $optimizer = new ImageOptimizerService();
        $fakeImg = UploadedFile::fake()->image('original_prod.png', 400, 400);
        $prod1MainImage = $optimizer->convertProductImageToWebp($fakeImg, 'products', 400, 80);

        $prod1GalleryImg = $optimizer->convertProductImageToWebp(UploadedFile::fake()->image('gallery_item.png', 400, 400), 'products', 400, 80);

        $prod1VariantImg = $optimizer->convertProductImageToWebp(UploadedFile::fake()->image('variant_item.png', 400, 400), 'products/variants', 400, 80);

        $prod1DiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod1MainImage));
        $this->assertTrue(File::exists($prod1DiskPath));

        $prod1Id = DB::table('products')->insertGetId([
            'category_id' => 1,
            'title' => 'Product 1 Original',
            'slug' => 'product-1-original',
            'sku' => 'ZB-ORIG01',
            'price' => 1000,
            'stock_qty' => 10,
            'main_image' => $prod1MainImage,
            'gallery_images' => json_encode([$prod1MainImage, $prod1GalleryImg]),
            'variants' => json_encode([
                ['name' => 'Black', 'price' => 1000, 'stock' => 5, 'image' => $prod1VariantImg]
            ]),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAsAdmin()->postJson("/admin/products/{$prod1Id}/duplicate");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $prod2Id = $response->json('id');
        $this->assertNotNull($prod2Id);
        $this->assertNotEquals($prod1Id, $prod2Id);

        $prod2 = DB::table('products')->where('id', $prod2Id)->first();
        $this->assertNotNull($prod2);

        $this->assertNotEquals($prod1MainImage, $prod2->main_image);
        $this->assertStringContainsString('_copy_', $prod2->main_image);

        $prod2DiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod2->main_image));
        $this->assertTrue(File::exists($prod2DiskPath));
        $this->assertTrue(File::exists($prod1DiskPath));

        $prod2Gallery = json_decode($prod2->gallery_images, true);
        $this->assertCount(2, $prod2Gallery);
        $this->assertEquals($prod2->main_image, $prod2Gallery[0]);
        $this->assertNotEquals($prod1GalleryImg, $prod2Gallery[1]);

        $prod2GalleryDiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod2Gallery[1]));
        $this->assertTrue(File::exists($prod2GalleryDiskPath));

        $prod2Variants = json_decode($prod2->variants, true);
        $this->assertNotEquals($prod1VariantImg, $prod2Variants[0]['image']);
        $prod2VariantDiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod2Variants[0]['image']));
        $this->assertTrue(File::exists($prod2VariantDiskPath));

        $deleteResponse = $this->actingAsAdmin()->deleteJson("/admin/products/{$prod2Id}/ajax-delete");
        $deleteResponse->assertStatus(200);

        $this->assertFalse(File::exists($prod2DiskPath));
        $this->assertFalse(File::exists($prod2GalleryDiskPath));
        $this->assertFalse(File::exists($prod2VariantDiskPath));

        $this->assertTrue(File::exists($prod1DiskPath));
        $prod1GalleryDiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod1GalleryImg));
        $this->assertTrue(File::exists($prod1GalleryDiskPath));
        $prod1VariantDiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod1VariantImg));
        $this->assertTrue(File::exists($prod1VariantDiskPath));

        $optimizer->deleteMedia($prod1MainImage);
        $optimizer->deleteMedia($prod1GalleryImg);
        $optimizer->deleteMedia($prod1VariantImg);
    }

    public function test_updating_duplicated_product_image_does_not_affect_original_image(): void
    {
        $optimizer = new ImageOptimizerService();
        $fakeImg = UploadedFile::fake()->image('prod1_thumb.png', 400, 400);
        $prod1MainImage = $optimizer->convertProductImageToWebp($fakeImg, 'products', 400, 80);
        $prod1DiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod1MainImage));

        $prod1Id = DB::table('products')->insertGetId([
            'category_id' => 1,
            'title' => 'Product Master',
            'slug' => 'product-master',
            'sku' => 'ZB-MSTR01',
            'price' => 1500,
            'stock_qty' => 10,
            'main_image' => $prod1MainImage,
            'gallery_images' => json_encode([$prod1MainImage]),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dupResp = $this->actingAsAdmin()->postJson("/admin/products/{$prod1Id}/duplicate");
        $dupResp->assertStatus(200);
        $prod2Id = $dupResp->json('id');

        $prod2 = DB::table('products')->where('id', $prod2Id)->first();
        $prod2DiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod2->main_image));
        $this->assertTrue(File::exists($prod1DiskPath));
        $this->assertTrue(File::exists($prod2DiskPath));

        $newThumb = UploadedFile::fake()->image('new_prod2_thumb.png', 400, 400);
        $updateResp = $this->actingAsAdmin()->postJson("/admin/products/ajax-save", [
            'id' => $prod2Id,
            'title' => 'Product Master (Copy Updated)',
            'category_id' => 1,
            'price' => 1500,
            'stock_qty' => 10,
            'main_image_file' => $newThumb,
            'existing_gallery' => [],
        ]);
        $updateResp->assertStatus(200);

        $this->assertFalse(File::exists($prod2DiskPath));

        $this->assertTrue(File::exists($prod1DiskPath));

        $prod2Updated = DB::table('products')->where('id', $prod2Id)->first();
        $prod2NewDiskPath = storage_path('app/public/' . str_replace('/storage/', '', $prod2Updated->main_image));
        $this->assertTrue(File::exists($prod2NewDiskPath));

        $optimizer->deleteMedia($prod1MainImage);
        $optimizer->deleteMedia($prod2Updated->main_image);
    }
}
