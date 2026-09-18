<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('orders', 'device_token')) {
                $table->string('device_token', 100)->nullable()->index()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'is_guest')) {
                $table->boolean('is_guest')->default(true)->after('device_token');
            }
            if (!Schema::hasColumn('orders', 'guest_email')) {
                $table->string('guest_email', 150)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('orders', 'device_fingerprint')) {
                $table->string('device_fingerprint', 255)->nullable()->after('user_agent');
            }
        });

        if (!Schema::hasTable('guest_devices')) {
            Schema::create('guest_devices', function (Blueprint $table) {
                $table->id();
                $table->string('device_token', 100)->unique()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('last_phone', 30)->nullable()->index();
                $table->string('last_name', 150)->nullable();
                $table->integer('total_orders')->default(0);
                $table->decimal('total_spent', 12, 2)->default(0.00);
                $table->timestamp('last_active_at')->nullable();
                $table->boolean('is_blocked')->default(false);
                $table->timestamp('unlinked_at')->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['user_id', 'device_token', 'is_guest', 'guest_email', 'device_fingerprint'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('guest_devices');
    }
};
