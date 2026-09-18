<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'display_mode')) {
                $table->string('display_mode', 20)->default('both')->after('type');
            }
            if (!Schema::hasColumn('banners', 'overlay_enabled')) {
                $table->boolean('overlay_enabled')->default(false)->after('gradient');
            }
            if (!Schema::hasColumn('banners', 'overlay_color')) {
                $table->string('overlay_color', 50)->default('#000000')->after('overlay_enabled');
            }
            if (!Schema::hasColumn('banners', 'overlay_opacity')) {
                $table->integer('overlay_opacity')->default(40)->after('overlay_color');
            }
            if (!Schema::hasColumn('banners', 'text_align')) {
                $table->string('text_align', 20)->default('left')->after('overlay_opacity');
            }
            if (!Schema::hasColumn('banners', 'text_color')) {
                $table->string('text_color', 50)->default('#ffffff')->after('text_align');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn([
                'display_mode',
                'overlay_enabled',
                'overlay_color',
                'overlay_opacity',
                'text_align',
                'text_color'
            ]);
        });
    }
};
