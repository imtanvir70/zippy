<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('theme_settings')) {
            Schema::table('theme_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('theme_settings', 'recent_sales_toast_enabled')) {
                    $table->boolean('recent_sales_toast_enabled')->default(true);
                }
                if (!Schema::hasColumn('theme_settings', 'recent_sales_interval')) {
                    $table->integer('recent_sales_interval')->default(45);
                }
                if (!Schema::hasColumn('theme_settings', 'recent_sales_time_min')) {
                    $table->integer('recent_sales_time_min')->default(2);
                }
                if (!Schema::hasColumn('theme_settings', 'recent_sales_time_max')) {
                    $table->integer('recent_sales_time_max')->default(45);
                }
                if (!Schema::hasColumn('theme_settings', 'live_viewers_enabled')) {
                    $table->boolean('live_viewers_enabled')->default(true);
                }
                if (!Schema::hasColumn('theme_settings', 'live_viewers_min')) {
                    $table->integer('live_viewers_min')->default(8);
                }
                if (!Schema::hasColumn('theme_settings', 'live_viewers_max')) {
                    $table->integer('live_viewers_max')->default(22);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('theme_settings')) {
            Schema::table('theme_settings', function (Blueprint $table) {
                $columns = [
                    'recent_sales_toast_enabled',
                    'recent_sales_interval',
                    'recent_sales_time_min',
                    'recent_sales_time_max',
                    'live_viewers_enabled',
                    'live_viewers_min',
                    'live_viewers_max',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('theme_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
