<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_settings')) {
            Schema::create('ai_settings', function (Blueprint $table) {
                $table->id();
                $table->text('gemini_api_key')->nullable();
                $table->string('gemini_model', 100)->default('gemini-1.5-flash');
                $table->text('groq_api_key')->nullable();
                $table->string('groq_model', 100)->default('llama-3.3-70b-versatile');
                $table->text('openrouter_api_key')->nullable();
                $table->string('openrouter_model', 100)->default('meta-llama/llama-3.3-70b-instruct');
                $table->string('product_generator_provider', 50)->default('gemini');
                $table->string('customer_qa_provider', 50)->default('groq');
                $table->string('failover_provider', 50)->default('openrouter');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('ai_settings') && DB::table('ai_settings')->count() === 0) {
            DB::table('ai_settings')->insert([
                'gemini_api_key' => null,
                'gemini_model' => 'gemini-1.5-flash',
                'groq_api_key' => null,
                'groq_model' => 'llama-3.3-70b-versatile',
                'openrouter_api_key' => null,
                'openrouter_model' => 'meta-llama/llama-3.3-70b-instruct',
                'product_generator_provider' => 'gemini',
                'customer_qa_provider' => 'groq',
                'failover_provider' => 'openrouter',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('permissions')) {
            $exists = DB::table('permissions')->where('name', 'admin.settings.ai')->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => 'admin.settings.ai',
                    'group_name' => 'Settings',
                    'display_name' => 'Manage AI Integrations & API Hub',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'admin.settings.ai')->delete();
        }
    }
};
