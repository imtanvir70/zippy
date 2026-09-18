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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->index()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar', 500)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('customer')->index()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id', 100)->nullable()->index()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id', 100)->nullable()->index()->after('google_id');
            }
            if (!Schema::hasColumn('users', 'auth_provider')) {
                $table->string('auth_provider', 50)->nullable()->after('facebook_id');
            }
            if (!Schema::hasColumn('users', 'auth_provider_id')) {
                $table->string('auth_provider_id', 150)->nullable()->after('auth_provider');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('auth_provider_id');
            }
            if (!Schema::hasColumn('users', 'district')) {
                $table->string('district', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'device_token')) {
                $table->string('device_token', 100)->nullable()->index()->after('district');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'phone',
                'avatar',
                'role',
                'is_active',
                'google_id',
                'facebook_id',
                'auth_provider',
                'auth_provider_id',
                'address',
                'district',
                'device_token'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
