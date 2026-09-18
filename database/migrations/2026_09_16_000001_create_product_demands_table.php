<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_demands', function (Blueprint $table) {
            $table->id();
            $table->string('query', 255);
            $table->string('normalized_query', 255)->index();
            $table->string('category_hint', 100)->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedInteger('hits_count')->default(1);
            $table->enum('status', ['pending', 'under_review', 'planned', 'stocked', 'rejected'])->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('last_requested_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_demands');
    }
};
