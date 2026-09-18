<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\Ai\AiService;

class AiIntegrationController extends Controller
{
    public function index(AiService $aiService)
    {
        $aiSettings = DB::table('ai_settings')->first();

        if (!$aiSettings) {
            $id = DB::table('ai_settings')->insertGetId([
                'gemini_api_key' => null,
                'gemini_model' => 'gemini-2.5-flash',
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

            $aiSettings = DB::table('ai_settings')->where('id', $id)->first();
        }

        $geminiModels = [
            'gemini-3.6-flash' => 'Gemini 3.6 Flash (Recommended - Latest Flagship)',
            'gemini-2.5-flash' => 'Gemini 2.5 Flash',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        ];

        $groqModels = [
            'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile (Ultra-Fast Response)',
            'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant (Instantaneous TTFT)',
            'mixtral-8x7b-32768' => 'Mixtral 8x7B (MoE Long Context)',
        ];

        $openRouterModels = [
            'meta-llama/llama-3.3-70b-instruct' => 'Meta Llama 3.3 70B Instruct',
            'google/gemini-2.0-flash-001' => 'Google Gemini 2.0 Flash',
            'deepseek/deepseek-r1' => 'DeepSeek R1 (Advanced Reasoning)',
            'anthropic/claude-3.5-sonnet' => 'Anthropic Claude 3.5 Sonnet',
        ];

        $providers = [
            'gemini' => 'Google Gemini',
            'groq' => 'Groq Cloud',
            'openrouter' => 'OpenRouter',
        ];

        return view('backend.settings.ai', compact(
            'aiSettings',
            'geminiModels',
            'groqModels',
            'openRouterModels',
            'providers'
        ));
    }

    public function update(Request $request, AiService $aiService)
    {
        $validated = $request->validate([
            'gemini_api_key' => 'nullable|string',
            'gemini_model' => 'required|string|max:100',
            'groq_api_key' => 'nullable|string',
            'groq_model' => 'required|string|max:100',
            'openrouter_api_key' => 'nullable|string',
            'openrouter_model' => 'required|string|max:100',
            'product_generator_provider' => 'required|string|in:gemini,groq,openrouter',
            'customer_qa_provider' => 'required|string|in:gemini,groq,openrouter',
            'failover_provider' => 'required|string|in:gemini,groq,openrouter',
            'is_active' => 'nullable',
        ]);

        $isActive = $request->boolean('is_active');
        $existing = DB::table('ai_settings')->first();

        $geminiKey = $request->boolean('remove_gemini_key')
            ? null
            : ($request->filled('gemini_api_key') ? trim((string) $request->input('gemini_api_key')) : ($existing->gemini_api_key ?? null));

        $groqKey = $request->boolean('remove_groq_key')
            ? null
            : ($request->filled('groq_api_key') ? trim((string) $request->input('groq_api_key')) : ($existing->groq_api_key ?? null));

        $openRouterKey = $request->boolean('remove_openrouter_key')
            ? null
            : ($request->filled('openrouter_api_key') ? trim((string) $request->input('openrouter_api_key')) : ($existing->openrouter_api_key ?? null));

        $data = [
            'gemini_api_key' => $geminiKey,
            'gemini_model' => trim($validated['gemini_model']),
            'groq_api_key' => $groqKey,
            'groq_model' => trim($validated['groq_model']),
            'openrouter_api_key' => $openRouterKey,
            'openrouter_model' => trim($validated['openrouter_model']),
            'product_generator_provider' => $validated['product_generator_provider'],
            'customer_qa_provider' => $validated['customer_qa_provider'],
            'failover_provider' => $validated['failover_provider'],
            'is_active' => $isActive,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('ai_settings')->where('id', $existing->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('ai_settings')->insert($data);
        }

        Cache::forget('ai_integrations');
        Cache::forget('ai_settings');
        $aiService->clearCache();
        $aiService->refreshCache();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'AI Hub configurations and role routings saved successfully.',
            ]);
        }

        return redirect()->route('admin.settings.ai')->with('success', 'AI Hub configurations updated successfully.');
    }

    public function testConnection(Request $request, AiService $aiService)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:gemini,groq,openrouter',
            'api_key' => 'nullable|string',
            'model' => 'nullable|string|max:100',
        ]);

        $provider = $validated['provider'];
        $apiKey = !empty($validated['api_key']) ? trim($validated['api_key']) : null;
        $model = !empty($validated['model']) ? trim($validated['model']) : null;

        $result = $aiService->testProvider($provider, $apiKey, $model);

        return response()->json($result);
    }

    public function playgroundProduct(Request $request, AiService $aiService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'specs' => 'nullable|string',
        ]);

        $result = $aiService->generateProductDetails($validated);

        return response()->json($result);
    }

    public function playgroundChat(Request $request, AiService $aiService)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $result = $aiService->chatCustomerQa($validated['message']);

        return response()->json($result);
    }

    public function syncModels(Request $request, AiService $aiService)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:gemini,groq,openrouter',
            'api_key' => 'nullable|string',
        ]);

        $provider = $validated['provider'];
        $apiKey = !empty($validated['api_key']) ? trim($validated['api_key']) : null;

        $models = $aiService->fetchLiveModels($provider, $apiKey);
        $recommended = match ($provider) {
            'groq' => $aiService->resolveLatestGroqModel($apiKey, 'auto'),
            'gemini' => 'gemini-3.6-flash',
            'openrouter' => 'meta-llama/llama-3.3-70b-instruct',
            default => 'auto',
        };

        return response()->json([
            'success' => true,
            'provider' => $provider,
            'models' => $models,
            'recommended' => $recommended,
            'message' => 'Active models synchronized successfully from ' . strtoupper($provider) . '.',
        ]);
    }
}
