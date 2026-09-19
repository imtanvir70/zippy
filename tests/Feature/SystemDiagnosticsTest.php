<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Services\Media\ImageOptimizerService;

class SystemDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('settings')->insertOrIgnore([
            ['key' => 'store_name', 'value' => 'Zippy', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_phone', 'value' => '01700000000', 'created_at' => now(), 'updated_at' => now()],
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

    public function test_product_image_is_standardized_to_square_webp(): void
    {
        $optimizer = new ImageOptimizerService();
        $fakeImage = UploadedFile::fake()->image('custom_aspect_ratio_product.jpg', 1200, 600);

        $url = $optimizer->convertProductImageToWebp($fakeImage, 'products', 800, 85, 'ffffff');

        $this->assertStringStartsWith('/storage/products/', $url);
        $this->assertStringEndsWith('.webp', $url);

        $relativePath = ltrim(str_replace('/storage/', '', $url), '/');
        $fullPath = storage_path('app/public/' . $relativePath);
        $this->assertFileExists($fullPath);

        [$width, $height] = getimagesize($fullPath);
        $this->assertEquals(800, $width);
        $this->assertEquals(800, $height);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    public function test_system_info_requires_authentication(): void
    {
        $response = $this->get(route('admin.system.info'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_system_info_page_renders_telemetry_for_admin(): void
    {
        $response = $this->actingAsAdmin()->get(route('admin.system.info'));
        $response->assertStatus(200);
        $response->assertSee('System &amp; Server Diagnostics', false);
        $response->assertSee(app()->version());
        $response->assertSee(PHP_VERSION);
    }

    public function test_clear_cache_endpoint_purges_cache_successfully(): void
    {
        $response = $this->actingAsAdmin()->postJson(route('admin.system.clear_cache'));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'duration_ms',
        ]);
    }
}
