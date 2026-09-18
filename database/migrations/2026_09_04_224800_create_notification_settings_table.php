<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('whatsapp_api_url')->nullable();
            $table->string('whatsapp_api_token')->nullable();
            $table->string('whatsapp_from_phone')->nullable();
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('sms_provider')->default('greenweb');
            $table->string('sms_api_url')->nullable();
            $table->string('sms_api_key')->nullable();
            $table->string('sms_sender_id')->nullable();
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('notify_on_order_status')->default(true);
            $table->boolean('notify_on_abandoned_cart')->default(true);
            $table->text('order_status_template')->nullable();
            $table->text('abandoned_cart_template')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
