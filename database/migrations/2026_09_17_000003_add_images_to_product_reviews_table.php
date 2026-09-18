<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_reviews') && !Schema::hasColumn('product_reviews', 'images')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->json('images')->nullable()->after('photo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_reviews') && Schema::hasColumn('product_reviews', 'images')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->dropColumn('images');
            });
        }
    }
};
