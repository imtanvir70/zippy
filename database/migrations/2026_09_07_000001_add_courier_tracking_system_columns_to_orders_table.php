<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'courier_name')) {
                $table->string('courier_name')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'consignment_id')) {
                $table->string('consignment_id')->nullable()->after('courier_name');
            }
            if (!Schema::hasColumn('orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable()->after('consignment_id');
            }
            if (!Schema::hasColumn('orders', 'courier_status')) {
                $table->string('courier_status')->nullable()->after('tracking_code');
            }
            if (!Schema::hasColumn('orders', 'delivery_status')) {
                $table->string('delivery_status')->default('pending')->after('courier_status');
            }
            if (!Schema::hasColumn('orders', 'courier_history')) {
                $table->json('courier_history')->nullable()->after('delivery_status');
            }
        });

        DB::statement("UPDATE orders SET courier_name = courier_provider WHERE courier_name IS NULL AND courier_provider IS NOT NULL");
        DB::statement("UPDATE orders SET consignment_id = courier_consignment_id WHERE consignment_id IS NULL AND courier_consignment_id IS NOT NULL");
        DB::statement("UPDATE orders SET tracking_code = courier_tracking_code WHERE tracking_code IS NULL AND courier_tracking_code IS NOT NULL");
        DB::statement("UPDATE orders SET delivery_status = 'delivered' WHERE order_status = 'delivered'");
        DB::statement("UPDATE orders SET delivery_status = 'cancelled' WHERE order_status = 'cancelled'");
        DB::statement("UPDATE orders SET delivery_status = 'in_transit' WHERE order_status = 'shipped' AND delivery_status = 'pending'");
        DB::statement("UPDATE orders SET delivery_status = 'processing' WHERE order_status IN ('confirmed', 'processing') AND delivery_status = 'pending'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('orders', 'courier_history')) {
                $cols[] = 'courier_history';
            }
            if (Schema::hasColumn('orders', 'delivery_status')) {
                $cols[] = 'delivery_status';
            }
            if (Schema::hasColumn('orders', 'tracking_code')) {
                $cols[] = 'tracking_code';
            }
            if (Schema::hasColumn('orders', 'consignment_id')) {
                $cols[] = 'consignment_id';
            }
            if (Schema::hasColumn('orders', 'courier_name')) {
                $cols[] = 'courier_name';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
