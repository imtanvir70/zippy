<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConversionBoostersTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_setting_helper_returns_default_when_not_set(): void
    {
        $value = theme_setting('non_existent_key', 'fallback_val');
        $this->assertEquals('fallback_val', $value);
    }

    public function test_admin_can_update_conversion_booster_toggles(): void
    {
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Super Admin',
            'email' => 'admin@zippybd.com',
            'password' => bcrypt('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'admin_id' => $adminId,
            'admin_logged_in' => true,
            'admin_role' => 'superadmin',
            'admin_name' => 'Super Admin'
        ])->post('/admin/theme-settings/update', [
            'primary_color' => '#0f172a',
            'accent_color' => '#ff385c',
            'font_family' => 'Outfit',
            'recent_sales_toast_enabled' => '1',
            'live_viewers_enabled' => '1',
            'live_viewers_min' => '10',
            'live_viewers_max' => '25',
        ]);

        $response->assertStatus(302);

        $theme = DB::table('theme_settings')->first();
        $this->assertNotNull($theme);
        $this->assertEquals(1, $theme->recent_sales_toast_enabled);
        $this->assertEquals(1, $theme->live_viewers_enabled);
        $this->assertEquals(10, $theme->live_viewers_min);
        $this->assertEquals(25, $theme->live_viewers_max);
    }
}
