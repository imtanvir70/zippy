<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'call_status')) {
                $table->string('call_status', 50)->default('pending_call')->index()->after('order_status');
            }
            if (!Schema::hasColumn('orders', 'call_note')) {
                $table->text('call_note')->nullable()->after('call_status');
            }
            if (!Schema::hasColumn('orders', 'called_at')) {
                $table->timestamp('called_at')->nullable()->after('call_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['call_status', 'call_note', 'called_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
