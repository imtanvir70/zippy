<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('bn_name')->index();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name')->index();
            $table->string('bn_name')->index();
            $table->timestamps();

            $table->index(['division_id', 'name']);
            $table->index(['division_id', 'bn_name']);
        });

        Schema::create('upazilas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name')->index();
            $table->string('bn_name')->index();
            $table->timestamps();

            $table->index(['district_id', 'name']);
            $table->index(['district_id', 'bn_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upazilas');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('divisions');
    }
};
