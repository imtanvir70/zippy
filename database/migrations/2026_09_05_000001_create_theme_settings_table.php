<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->text('hero_typing_words')->nullable();
            $table->string('primary_color', 30)->default('#0f172a');
            $table->string('accent_color', 30)->default('#ff385c');
            $table->string('font_family', 100)->default('Outfit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
