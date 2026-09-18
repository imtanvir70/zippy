<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // High-speed composite indexes for 500,000+ products
            $table->index(['is_active', 'is_featured', 'id'], 'idx_products_active_featured_id');
            $table->index(['is_active', 'is_flash_deal', 'id'], 'idx_products_active_flash_id');
            $table->index(['is_active', 'category_id', 'id'], 'idx_products_active_cat_id');
            $table->index(['is_active', 'price'], 'idx_products_active_price');
            $table->index(['is_active', 'rating'], 'idx_products_active_rating');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['customer_phone', 'created_at'], 'idx_orders_phone_created');
            $table->index(['order_status', 'created_at'], 'idx_orders_status_created');
            $table->index(['courier_tracking_code'], 'idx_orders_tracking_code');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'idx_categories_active_sort');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->index(['is_active', 'type', 'sort_order'], 'idx_banners_active_type_sort');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_featured_id');
            $table->dropIndex('idx_products_active_flash_id');
            $table->dropIndex('idx_products_active_cat_id');
            $table->dropIndex('idx_products_active_price');
            $table->dropIndex('idx_products_active_rating');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_phone_created');
            $table->dropIndex('idx_orders_status_created');
            $table->dropIndex('idx_orders_tracking_code');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_active_sort');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('idx_banners_active_type_sort');
        });
    }
};