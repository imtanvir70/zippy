<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blocklists')) {
            Schema::create('blocklists', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['phone', 'ip']);
                $table->string('value', 100)->index();
                $table->string('reason', 255)->nullable();
                $table->boolean('is_blocked')->default(1)->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blocklists');
    }
};
