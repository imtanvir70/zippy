<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@zippybd.com'],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'is_active' => 1,
                'password' => Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->call([
            SettingSeeder::class,
            CategorySeeder::class,
            BannerSeeder::class,
            EnterpriseSeeder::class,
            BangladeshGeoSeeder::class,
        ]);
    }
}
