<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('greenweb');
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('sender_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('notify_on_order_placed')->default(true);
            $table->boolean('notify_on_order_shipped')->default(true);
            $table->boolean('notify_on_order_delivered')->default(true);
            $table->text('order_placed_template')->nullable();
            $table->text('order_shipped_template')->nullable();
            $table->text('order_delivered_template')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
