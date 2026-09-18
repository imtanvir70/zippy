<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class AiService
{
    public const CACHE_KEY = 'ai_settings';

    public function getSettings(): ?array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $record = DB::table('ai_settings')->first();
            if (!$record) {
                return null;
            }
            return json_decode(json_encode($record), true);
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('ai_integrations');
    }

    public function refreshCache(): ?array
    {
        $this->clearCache();
        return $this->getSettings();
    }

    public function getProviderCredentials(string $provider): array
    {
        $settings = $this->getSettings();
        if (!$settings) {
            return ['key' => null, 'model' => null];
        }

        switch (strtolower(trim($provider))) {
            case 'gemini':
                return [
                    'key' => !empty($settings['gemini_api_key']) ? trim($settings['gemini_api_key']) : null,
                    'model' => (!empty($settings['gemini_model']) && !in_array(trim($settings['gemini_model']), ['auto', 'gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-3.6-flash']))
                        ? trim($settings['gemini_model'])
                        : 'gemini-flash-lite-latest',
                ];
            case 'groq':
                return [
                    'key' => !empty($settings['groq_api_key']) ? trim($settings['groq_api_key']) : null,
                    'model' => (!empty($settings['groq_model']) && !in_array(trim($settings['groq_model']), ['auto', 'canopylabs/orpheus-arabic-saudi', 'llama-3.3-70b-specdec']) && !str_contains(trim($settings['groq_model']), 'canopylabs'))
                        ? trim($settings['groq_model'])
                        : 'llama-3.3-70b-versatile',
                ];
            case 'openrouter':
                return [
                    'key' => !empty($settings['openrouter_api_key']) ? trim($settings['openrouter_api_key']) : null,
                    'model' => (!empty($settings['openrouter_model']) && trim($settings['openrouter_model']) !== 'auto')
                        ? trim($settings['openrouter_model'])
                        : 'meta-llama/llama-3.3-70b-instruct',
                ];
            default:
                return ['key' => null, 'model' => null];
        }
    }

    public function callProvider(
        string $provider,
        string $prompt,
        ?string $systemPrompt = null,
        ?string $apiKey = null,
        ?string $model = null,
        ?array $inlineData = null
    ): string {
        $provider = strtolower(trim($provider));
        $creds = $this->getProviderCredentials($provider);

        $resolvedKey = !empty($apiKey) ? trim($apiKey) : ($creds['key'] ?? null);
        $resolvedModel = !empty($model) ? trim($model) : ($creds['model'] ?? null);

        if (empty($resolvedKey)) {
            throw new Exception("API Key is missing for provider [{$provider}]. Please configure it in AI Integrations settings.");
        }

        switch ($provider) {
            case 'gemini':
                return $this->callGemini($prompt, $systemPrompt, $resolvedKey, $resolvedModel, $inlineData);
            case 'groq':
                return $this->callGroq($prompt, $systemPrompt, $resolvedKey, $resolvedModel, $inlineData);
            case 'openrouter':
                return $this->callOpenRouter($prompt, $systemPrompt, $resolvedKey, $resolvedModel, $inlineData);
            default:
                throw new Exception("Unsupported AI provider: [{$provider}]");
        }
    }

    public function callGemini(
        string $prompt,
        ?string $systemPrompt = null,
        ?string $apiKey = null,
        ?string $model = null,
        ?array $inlineData = null
    ): string {
        $creds = $this->getProviderCredentials('gemini');
        $resolvedKey = !empty($apiKey) ? trim($apiKey) : ($creds['key'] ?? null);
        $resolvedModel = !empty($model) ? trim($model) : (!empty($creds['model']) ? trim($creds['model']) : 'gemini-flash-lite-latest');
        if ($resolvedModel === 'auto' || in_array($resolvedModel, ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-3.6-flash'])) {
            $resolvedModel = 'gemini-flash-lite-latest';
        }

        if (empty($resolvedKey)) {
            throw new Exception("Gemini API Key is missing. Please configure it in AI Integrations settings.");
        }

        $parts = [];
        if (!empty($inlineData)) {
            if (isset($inlineData['data']) && isset($inlineData['mime_type'])) {
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $inlineData['mime_type'],
                        'data' => $inlineData['data'],
                    ],
                ];
            } elseif (is_array($inlineData)) {
                foreach ($inlineData as $img) {
                    if (is_array($img) && !empty($img['data']) && !empty($img['mime_type'])) {
                        $parts[] = [
                            'inline_data' => [
                                'mime_type' => $img['mime_type'],
                                'data' => $img['data'],
                            ],
                        ];
                    }
                }
            }
        }

        $fullPrompt = $systemPrompt ? ($systemPrompt . "\n\n" . $prompt) : $prompt;
        $parts[] = ['text' => $fullPrompt];

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
            ],
        ];

        $candidateModels = array_values(array_unique(array_filter([
            $resolvedModel,
            'gemini-flash-lite-latest',
            'gemini-3.5-flash-lite',
            'gemini-3.1-flash-lite',
            'gemini-flash-latest',
        ])));

        $lastError = null;

        foreach ($candidateModels as $candidateModel) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$candidateModel}:generateContent?key={$resolvedKey}";
            $response = Http::timeout(25)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                if (!empty($text)) {
                    if ($candidateModel !== $resolvedModel) {
                        $this->updateSavedProviderModel('gemini', $candidateModel);
                    }
                    return trim($text);
                }
            }

            $errorMsg = $response->json('error.message') ?? $response->body();
            $lastError = new Exception("Gemini API Error ({$response->status()}): {$errorMsg}");

            if (!in_array($response->status(), [400, 404, 429, 503])) {
                throw $lastError;
            }
        }

        throw $lastError ?? new Exception("Gemini returned an empty response.");
    }

    public function isDecommissionedModelError(string $error): bool
    {
        $needles = [
            'decommissioned',
            'no longer supported',
            'model_decommissioned',
            'does not exist',
            'not found',
            'model not found',
            'deprecated',
            'no longer available',
            'not available',
            'requires terms acceptance',
            'terms acceptance',
        ];
        $lower = strtolower($error);
        foreach ($needles as $needle) {
            if (str_contains($lower, $needle)) {
                return true;
            }
        }
        return false;
    }

    public function updateSavedProviderModel(string $provider, string $newModel): void
    {
        try {
            $field = strtolower(trim($provider)) . '_model';
            DB::table('ai_settings')->update([
                $field => $newModel,
                'updated_at' => now(),
            ]);
            $this->clearCache();
        } catch (\Throwable $e) {
        }
    }

    public function fetchActiveGroqModels(?string $apiKey = null): array
    {
        $creds = $this->getProviderCredentials('groq');
        $resolvedKey = !empty($apiKey) ? trim($apiKey) : ($creds['key'] ?? null);
        if (empty($resolvedKey)) {
            return [
                'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile (Recommended)',
                'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant (Ultra-Fast)',
                'mixtral-8x7b-32768' => 'Mixtral 8x7B (MoE Long Context)',
            ];
        }

        $cacheKey = 'ai_groq_active_models_' . substr(md5($resolvedKey), 0, 12);
        return Cache::remember($cacheKey, 86400, function () use ($resolvedKey) {
            try {
                $response = Http::timeout(10)
                    ->withToken($resolvedKey)
                    ->get('https://api.groq.com/openai/v1/models');

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    $models = [];
                    $excluded = [
                        'whisper',
                        'guard',
                        'embed',
                        'specdec',
                        'canopylabs',
                        'orpheus',
                        'tts',
                        'voice',
                        'audio',
                        'speech',
                        'transcribe',
                        'vision',
                        'preview'
                    ];
                    foreach ($data as $item) {
                        $id = $item['id'] ?? '';
                        $active = $item['active'] ?? true;
                        if (!$active || empty($id)) continue;
                        $lowerId = strtolower($id);
                        $skip = false;
                        foreach ($excluded as $ex) {
                            if (str_contains($lowerId, $ex)) {
                                $skip = true;
                                break;
                            }
                        }
                        if ($skip) continue;
                        $models[$id] = $id;
                    }
                    if (!empty($models)) {
                        $preferredOrder = [
                            'llama-3.3-70b-versatile',
                            'deepseek-r1-distill-llama-70b',
                            'llama-3.1-8b-instant',
                            'mixtral-8x7b-32768',
                            'gemma2-9b-it',
                        ];
                        $sorted = [];
                        foreach ($preferredOrder as $pref) {
                            if (isset($models[$pref])) {
                                $sorted[$pref] = $models[$pref];
                                unset($models[$pref]);
                            }
                        }
                        return array_merge($sorted, $models);
                    }
                }
            } catch (\Throwable $e) {
            }

            return [
                'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile (Recommended)',
                'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant (Ultra-Fast)',
                'mixtral-8x7b-32768' => 'Mixtral 8x7B (MoE Long Context)',
            ];
        });
    }

    public function resolveLatestGroqModel(?string $apiKey = null, ?string $currentModel = null): string
    {
        $decommissioned = [
            'llama-3.3-70b-specdec',
            'llama-3.1-70b-versatile',
            'llama-3.1-70b-specdec',
            'llama3-70b-8192',
            'llama3-8b-8192',
            'openai/gpt-oss-120b',
            'canopylabs/orpheus-arabic-saudi',
        ];

        $cur = strtolower(trim((string) $currentModel));
        if (!empty($cur) && $cur !== 'auto' && !in_array($cur, $decommissioned) && !str_contains($cur, 'canopylabs') && !str_contains($cur, 'orpheus')) {
            return trim($currentModel);
        }

        $activeModels = $this->fetchActiveGroqModels($apiKey);

        $preferred = [
            'llama-3.3-70b-versatile',
            'deepseek-r1-distill-llama-70b',
            'llama-3.1-8b-instant',
            'mixtral-8x7b-32768',
            'gemma2-9b-it',
        ];
        foreach ($preferred as $p) {
            if (isset($activeModels[$p])) {
                return $p;
            }
        }

        foreach ($activeModels as $modelId => $label) {
            if (!in_array($modelId, $decommissioned) && !str_contains($modelId, 'canopylabs') && !str_contains($modelId, 'orpheus')) {
                return $modelId;
            }
        }

        return 'llama-3.3-70b-versatile';
    }

    public function fetchLiveModels(string $provider, ?string $apiKey = null): array
    {
        $provider = strtolower(trim($provider));
        $creds = $this->getProviderCredentials($provider);
        $resolvedKey = !empty($apiKey) ? trim($apiKey) : ($creds['key'] ?? null);

        if ($provider === 'groq') {
            return $this->fetchActiveGroqModels($resolvedKey);
        }

        if ($provider === 'gemini') {
            if (empty($resolvedKey)) {
                return [
                    'gemini-3.6-flash' => 'Gemini 3.6 Flash (Recommended)',
                    'gemini-2.5-flash' => 'Gemini 2.5 Flash',
                    'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash',
                ];
            }
            return Cache::remember('ai_gemini_active_models_' . substr(md5($resolvedKey), 0, 12), 86400, function () use ($resolvedKey) {
                try {
                    $response = Http::timeout(10)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$resolvedKey}");
                    if ($response->successful()) {
                        $modelsList = $response->json('models') ?? [];
                        $found = [];
                        foreach ($modelsList as $m) {
                            $name = str_replace('models/', '', $m['name'] ?? '');
                            $methods = $m['supportedGenerationMethods'] ?? [];
                            if (in_array('generateContent', $methods) && !str_contains($name, 'embedding') && !str_contains($name, 'aqa')) {
                                $found[$name] = $m['displayName'] ?? $name;
                            }
                        }
                        if (!empty($found)) {
                            if (isset($found['gemini-3.6-flash'])) {
                                $found = ['gemini-3.6-flash' => $found['gemini-3.6-flash']] + $found;
                            }
                            return $found;
                        }
                    }
                } catch (\Throwable $e) {
                }
                return [
                    'gemini-3.6-flash' => 'Gemini 3.6 Flash (Recommended)',
                    'gemini-2.5-flash' => 'Gemini 2.5 Flash',
                    'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash',
                ];
            });
        }

        return [
            'meta-llama/llama-3.3-70b-instruct' => 'Meta Llama 3.3 70B Instruct',
            'google/gemini-2.0-flash-001' => 'Google Gemini 2.0 Flash',
            'deepseek/deepseek-r1' => 'DeepSeek R1',
            'anthropic/claude-3.5-sonnet' => 'Anthropic Claude 3.5 Sonnet',
        ];
    }

    public function callGroq(
        string $prompt,
        ?string $systemPrompt = null,
        ?string $apiKey = null,
        ?string $model = null,
        ?array $inlineData = null
    ): string {
        $creds = $this->getProviderCredentials('groq');
        $resolvedKey = !empty($apiKey) ? trim($apiKey) : ($creds['key'] ?? null);
        $configuredModel = !empty($model) ? trim($model) : (!empty($creds['model']) ? trim($creds['model']) : 'llama-3.3-70b-versatile');
        $resolvedModel = $this->resolveLatestGroqModel($resolvedKey, $configuredModel);

        if (empty($resolvedKey)) {
            throw new Exception("Groq API Key is missing. Please configure it in AI Integrations settings.");
        }

        $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

        $messages = [];
        if (!empty($systemPrompt)) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $userContent = [];
        $userContent[] = ['type' => 'text', 'text' => $prompt];
        if (!empty($inlineData)) {
            if (isset($inlineData['data']) && isset($inlineData['mime_type'])) {
                $userContent[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:{$inlineData['mime_type']};base64,{$inlineData['data']}",
                    ],
                ];
            } elseif (is_array($inlineData)) {
                foreach ($inlineData as $img) {
                    if (is_array($img) && !empty($img['data']) && !empty($img['mime_type'])) {
                        $userContent[] = [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$img['mime_type']};base64,{$img['data']}",
                            ],
                        ];
                    }
                }
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => count($userContent) > 1 ? $userContent : $prompt,
        ];

        $payload = [
            'model' => $resolvedModel,
            'messages' => $messages,
            'max_tokens' => 3500,
            'temperature' => 0.5,
        ];

        $response = Http::timeout(30)
            ->withToken($resolvedKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        if ($response->status() === 400 || $response->status() === 404) {
            $errorDetail = $response->json('error.message') ?? $response->body();
            if ($this->isDecommissionedModelError($errorDetail)) {
                Cache::forget('ai_groq_active_models_' . substr(md5($resolvedKey), 0, 12));
                $freshModel = $this->resolveLatestGroqModel($resolvedKey, 'auto');
                if ($freshModel !== $resolvedModel) {
                    $this->updateSavedProviderModel('groq', $freshModel);
                    $payload['model'] = $freshModel;
                    $response = Http::timeout(30)
                        ->withToken($resolvedKey)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($endpoint, $payload);
                }
            }
        }

        if (!$response->successful()) {
            $errorDetail = $response->json('error.message') ?? $response->body();
            throw new Exception("Groq API Error ({$response->status()}): {$errorDetail}");
        }

        $text = $response->json('choices.0.message.content');
        if (empty($text)) {
            $reasoning = $response->json('choices.0.message.reasoning');
            if (!empty($reasoning)) {
                $text = $reasoning;
            } else {
                throw new Exception("Groq returned an empty response.");
            }
        }

        return trim($text);
    }

    public function chatGroq(
        array $messages,
        ?string $systemPrompt = null,
        ?string $apiKey = null,
        ?string $model = null,
        int $maxTokens = 1000,
        float $temperature = 0.5
    ): string {
        $creds = $this->getProviderCredentials('groq');
        $resolvedKey = !empty($apiKey) ? trim($apiKey) : ($creds['key'] ?? null);
        $configuredModel = !empty($model) ? trim($model) : (!empty($creds['model']) ? trim($creds['model']) : 'llama-3.3-70b-versatile');
        $resolvedModel = $this->resolveLatestGroqModel($resolvedKey, $configuredModel);

        if (empty($resolvedKey)) {
            throw new Exception("Groq API Key is missing. Please configure it in AI Integrations settings.");
        }

        $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

        $payloadMessages = [];
        if (!empty($systemPrompt)) {
            $payloadMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $msg) {
            if (isset($msg['role']) && isset($msg['content']) && in_array($msg['role'], ['system', 'user', 'assistant'])) {
                $payloadMessages[] = [
                    'role' => $msg['role'],
                    'content' => (string) $msg['content'],
                ];
            }
        }

        $payload = [
            'model' => $resolvedModel,
            'messages' => $payloadMessages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        $response = Http::timeout(30)
            ->withToken($resolvedKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        if ($response->status() === 429) {
            sleep(3);
            $response = Http::timeout(30)
                ->withToken($resolvedKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);
        }

        if ($response->status() === 400 || $response->status() === 404) {
            $errorDetail = $response->json('error.message') ?? $response->body();
            if ($this->isDecommissionedModelError($errorDetail)) {
                Cache::forget('ai_groq_active_models_' . substr(md5($resolvedKey), 0, 12));
                $freshModel = $this->resolveLatestGroqModel($resolvedKey, 'auto');
                if ($freshModel !== $resolvedModel) {
                    $this->updateSavedProviderModel('groq', $freshModel);
                    $payload['model'] = $freshModel;
                    $response = Http::timeout(30)
                        ->withToken($resolvedKey)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($endpoint, $payload);
                }
            }
        }

        if (!$response->successful()) {
            $errorDetail = $response->json('error.message') ?? $response->body();
            throw new Exception("Groq API Error ({$response->status()}): {$errorDetail}");
        }

        $text = $response->json('choices.0.message.content');
        if (empty($text)) {
            $reasoning = $response->json('choices.0.message.reasoning');
            if (!empty($reasoning)) {
                $text = $reasoning;
            } else {
                throw new Exception("Groq returned an empty response.");
            }
        }

        return trim($text);
    }

    public function callOpenRouter(
        string $prompt,
        ?string $systemPrompt = null,
        ?string $apiKey = null,
        ?string $model = null,
        ?array $inlineData = null
    ): string {
        $creds = $this->getProviderCredentials('openrouter');
        $resolvedKey = !empty($apiKey) ? trim($apiKey) : ($creds['key'] ?? null);
        $resolvedModel = !empty($model) && $model !== 'auto' ? trim($model) : (!empty($creds['model']) && $creds['model'] !== 'auto' ? trim($creds['model']) : 'meta-llama/llama-3.3-70b-instruct');

        if (empty($resolvedKey)) {
            throw new Exception("OpenRouter API Key is missing. Please configure it in AI Integrations settings.");
        }

        $endpoint = 'https://openrouter.ai/api/v1/chat/completions';

        $messages = [];
        if (!empty($systemPrompt)) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $userContent = [];
        $userContent[] = ['type' => 'text', 'text' => $prompt];
        if (!empty($inlineData)) {
            if (isset($inlineData['data']) && isset($inlineData['mime_type'])) {
                $userContent[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:{$inlineData['mime_type']};base64,{$inlineData['data']}",
                    ],
                ];
            } elseif (is_array($inlineData)) {
                foreach ($inlineData as $img) {
                    if (is_array($img) && !empty($img['data']) && !empty($img['mime_type'])) {
                        $userContent[] = [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$img['mime_type']};base64,{$img['data']}",
                            ],
                        ];
                    }
                }
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => count($userContent) > 1 ? $userContent : $prompt,
        ];

        $payload = [
            'model' => $resolvedModel,
            'messages' => $messages,
            'temperature' => 0.5,
        ];

        $response = Http::timeout(35)
            ->withToken($resolvedKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'https://zippybd.com'),
                'X-Title' => 'ZippyBD AI Engine',
            ])
            ->post($endpoint, $payload);

        if ($response->status() === 400 || $response->status() === 404) {
            $errorDetail = $response->json('error.message') ?? $response->body();
            if ($this->isDecommissionedModelError($errorDetail)) {
                $fallbackOpenRouter = 'meta-llama/llama-3.3-70b-instruct';
                if ($resolvedModel !== $fallbackOpenRouter) {
                    $this->updateSavedProviderModel('openrouter', $fallbackOpenRouter);
                    $payload['model'] = $fallbackOpenRouter;
                    $response = Http::timeout(35)
                        ->withToken($resolvedKey)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'HTTP-Referer' => config('app.url', 'https://zippybd.com'),
                            'X-Title' => 'ZippyBD AI Engine',
                        ])
                        ->post($endpoint, $payload);
                }
            }
        }

        if (!$response->successful()) {
            $errorDetail = $response->json('error.message') ?? $response->body();
            throw new Exception("OpenRouter API Error ({$response->status()}): {$errorDetail}");
        }

        $text = $response->json('choices.0.message.content');
        if (empty($text)) {
            throw new Exception("OpenRouter returned an empty response.");
        }

        return trim($text);
    }

    public function testProvider(string $provider, ?string $apiKey = null, ?string $model = null): array
    {
        $startTime = microtime(true);
        $provider = strtolower(trim($provider));

        try {
            $prompt = 'Ping test. Reply with the single word: OK.';
            $result = $this->callProvider($provider, $prompt, null, $apiKey, $model);
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $creds = $this->getProviderCredentials($provider);
            $activeModel = !empty($model) ? trim($model) : ($creds['model'] ?? 'default');

            return [
                'success' => true,
                'provider' => $provider,
                'model' => $activeModel,
                'latency_ms' => $latency,
                'message' => 'Connection verified successfully.',
                'response' => $result,
            ];
        } catch (Exception $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $creds = $this->getProviderCredentials($provider);
            $activeModel = !empty($model) ? trim($model) : ($creds['model'] ?? 'default');

            return [
                'success' => false,
                'provider' => $provider,
                'model' => $activeModel,
                'latency_ms' => $latency,
                'message' => $e->getMessage(),
                'response' => null,
            ];
        }
    }

    public function executeWithFailover(string $role, callable $operation): array
    {
        $settings = $this->getSettings();
        if (!$settings || empty($settings['is_active'])) {
            return [
                'success' => false,
                'error' => 'AI services are currently disabled in store settings.',
                'provider_used' => null,
                'was_failover' => false,
                'data' => null,
            ];
        }

        $primaryProvider = match ($role) {
            'product_generator' => !empty($settings['product_generator_provider']) ? $settings['product_generator_provider'] : 'gemini',
            'customer_qa' => !empty($settings['customer_qa_provider']) ? $settings['customer_qa_provider'] : 'groq',
            default => 'gemini',
        };

        $failoverProvider = !empty($settings['failover_provider']) ? $settings['failover_provider'] : 'openrouter';

        $providersToTry = [];
        if (!empty($primaryProvider)) {
            $providersToTry[] = $primaryProvider;
        }
        if (!empty($failoverProvider) && !in_array($failoverProvider, $providersToTry)) {
            $providersToTry[] = $failoverProvider;
        }
        $candidates = ['groq', 'gemini', 'openrouter'];
        foreach ($candidates as $cand) {
            if (!in_array($cand, $providersToTry)) {
                $creds = $this->getProviderCredentials($cand);
                if (!empty($creds['key'])) {
                    $providersToTry[] = $cand;
                }
            }
        }

        $errors = [];
        $failedPrimary = null;

        foreach ($providersToTry as $idx => $provider) {
            try {
                $result = $operation($provider);
                return [
                    'success' => true,
                    'provider_used' => $provider,
                    'failed_primary' => $failedPrimary,
                    'was_failover' => $idx > 0,
                    'data' => $result,
                ];
            } catch (Exception $e) {
                if ($idx === 0) {
                    $failedPrimary = $provider;
                }
                $errors[] = "Provider [{$provider}]: " . $e->getMessage();
                Log::warning("AI Provider [{$provider}] failed for role [{$role}]: " . $e->getMessage());
            }
        }

        return [
            'success' => false,
            'error' => "All configured AI providers failed. " . implode(' | ', $errors),
            'provider_used' => null,
            'was_failover' => true,
            'data' => null,
        ];
    }

    public function generateProductDetails(array $input): array
    {
        $systemPrompt = "Analyze the provided product image carefully. Extract the exact product name, category, inferred specifications, and features visible in the image. Do NOT use generic templates or hardcoded fallback products like t-shirts unless shown.

CRITICAL MERCHANDISING INSTRUCTIONS:
1. STRICT VISUAL FIDELITY: Every single field must describe the exact physical product, brand, model, material, color, and design elements visible in the image. Never invent unrelated items or use generic apparel templates when electronic/gadget items or other distinct items are provided.
2. BANGLADESH MARKET PRICING (in BDT ৳):
- price / selling_price: Realistic retail price in Bangladesh Taka for this item.
- mrp_price: Realistic original/strikethrough retail price (15-30% above selling price).
- purchase_price: Estimated merchant wholesale acquisition cost.
- stock_quantity: Realistic initial inventory count (e.g. 20-50).
3. 3-TIER HIERARCHICAL CATEGORY:
- parent_category: Primary top-level category (e.g., 'Electronics & Gadgets', 'Fashion & Apparel', 'Home & Living').
- sub_category: Mid-level category (e.g., 'Smart Wearables', 'Audio & Sound', 'Footwear').
- child_category / category: Specific granular category (e.g., 'Smartwatches', 'TWS Earbuds', 'Sneakers').
4. DETAILED DESCRIPTION: Provide rich semantic HTML (<p>, <h4>, <ul>, <li>, <strong>) covering Product Overview, Key Visible Features, Technical Specifications, and Box Contents.
5. SPECIFICATIONS: Realistic key-value pairs deduced from the product (Brand, Model, Connectivity, Battery/Power, Display/Material, Dimensions, Warranty).

You must return ONLY a raw valid JSON object without markdown code blocks, with these exact keys:
{
  \"title\": \"Exact Brand and Model Name\",
  \"category\": \"Specific Product Category\",
  \"parent_category\": \"Top-level Category\",
  \"sub_category\": \"Sub Category\",
  \"child_category\": \"Specific Product Category\",
  \"sku\": \"ZB-...\",
  \"price\": 1450,
  \"selling_price\": 1450,
  \"mrp_price\": 1850,
  \"purchase_price\": 950,
  \"stock_quantity\": 50,
  \"short_desc\": \"2-3 conversion-focused sentences about this exact product.\",
  \"short_summary\": \"2-3 conversion-focused sentences about this exact product.\",
  \"description\": \"Rich HTML description detailing product overview, highlights, and specifications.\",
  \"detailed_html_description\": \"Rich HTML description detailing product overview, highlights, and specifications.\",
  \"specifications\": [
    {\"key\": \"Brand\", \"value\": \"...\"},
    {\"key\": \"Model\", \"value\": \"...\"}
  ],
  \"variants\": [
    {\"color_name\": \"...\", \"price\": 1450, \"stock\": 50}
  ],
  \"url_slug\": \"...\",
  \"meta_title\": \"...\",
  \"meta_description\": \"...\",
  \"meta_keywords\": \"...\"
}";

        $language = strtolower(trim($input['language'] ?? 'mixed'));
        if (str_contains($language, 'bengali') && !str_contains($language, 'mixed')) {
            $langInstruction = "LANGUAGE REQUIREMENT: Generate all customer-facing text fields (title, short_summary, detailed_html_description, specifications values, variant color names, and SEO meta tags) strictly in natural, high-converting Bengali (বাংলা). Keep JSON keys in English.";
        } elseif (str_contains($language, 'english')) {
            $langInstruction = "LANGUAGE REQUIREMENT: Generate all text fields (title, short_summary, detailed_html_description, specifications, variant color names, and SEO meta tags) strictly in fluent, persuasive English. Keep JSON keys in English.";
        } else {
            $langInstruction = "LANGUAGE REQUIREMENT: Generate content using a mixed English & Bengali format typical for Bangladesh e-commerce: keep brand names, titles, and technical specifications primarily in English, while writing engaging explanations, short summary, and detailed HTML descriptions in fluent Bengali (বাংলা). Keep JSON keys in English.";
        }
        $systemPrompt .= "\n\n" . $langInstruction;

        $hint = $input['hint'] ?? ($input['name'] ?? '');
        $category = $input['category'] ?? '';
        $specs = $input['specs'] ?? '';
        $extra = $input['extra'] ?? '';

        $promptParts = [];
        $promptParts[] = $langInstruction;
        if (!empty($hint)) {
            $promptParts[] = "Context / Hint: {$hint}";
        }
        if (!empty($category)) {
            $promptParts[] = "Target Category: {$category}";
        }
        if (!empty($specs)) {
            $promptParts[] = "Known Specs: {$specs}";
        }
        if (!empty($extra)) {
            $promptParts[] = "Additional Instructions: {$extra}";
        }
        $images = [];
        if (!empty($input['images']) && is_array($input['images'])) {
            foreach ($input['images'] as $img) {
                if (is_array($img) && !empty($img['data']) && !empty($img['mime_type'])) {
                    $images[] = [
                        'mime_type' => $img['mime_type'],
                        'data' => $img['data'],
                    ];
                }
            }
        }
        if (!empty($input['image_base64']) && !empty($input['image_mime'])) {
            $images[] = [
                'mime_type' => $input['image_mime'],
                'data' => $input['image_base64'],
            ];
        }
        if (!empty($input['gallery_base64']) && is_array($input['gallery_base64'])) {
            foreach ($input['gallery_base64'] as $gItem) {
                if (is_array($gItem) && !empty($gItem['data']) && !empty($gItem['mime_type'])) {
                    $images[] = $gItem;
                } elseif (is_string($gItem) && !empty($gItem)) {
                    $images[] = [
                        'mime_type' => 'image/jpeg',
                        'data' => $gItem,
                    ];
                }
            }
        }
        if (!empty($input['image_url']) && filter_var($input['image_url'], FILTER_VALIDATE_URL)) {
            try {
                $imgResponse = Http::timeout(8)->get($input['image_url']);
                if ($imgResponse->successful()) {
                    $cType = $imgResponse->header('Content-Type') ?: 'image/jpeg';
                    $mime = explode(';', $cType)[0];
                    $images[] = [
                        'mime_type' => trim($mime),
                        'data' => base64_encode($imgResponse->body()),
                    ];
                }
            } catch (Exception $e) {
                $promptParts[] = "Product Image URL: " . $input['image_url'];
            }
        }
        if (!empty($input['gallery_urls']) && is_array($input['gallery_urls'])) {
            foreach ($input['gallery_urls'] as $gUrl) {
                if (!empty($gUrl) && filter_var($gUrl, FILTER_VALIDATE_URL)) {
                    try {
                        $gResponse = Http::timeout(8)->get($gUrl);
                        if ($gResponse->successful()) {
                            $cType = $gResponse->header('Content-Type') ?: 'image/jpeg';
                            $mime = explode(';', $cType)[0];
                            $images[] = [
                                'mime_type' => trim($mime),
                                'data' => base64_encode($gResponse->body()),
                            ];
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }

        $imageCount = count($images);
        if ($imageCount > 1) {
            $promptParts[] = "Multiple product photos ({$imageCount} images) provided: includes primary featured shot plus supplementary gallery angles, packaging, labels, or detail close-ups. Inspect all {$imageCount} photos carefully to deduce full specifications, color variants, textures, dimensions, and craftsmanship.";
        }
        $promptParts[] = "Inspect all provided product images and context. Deduce all product details, strict 3-tier categories (parent_category, sub_category, child_category), suggested market pricing in BDT (৳), and generate full valid JSON.";
        $userPrompt = implode("\n", $promptParts);

        $inlineData = !empty($images) ? $images : null;

        return $this->executeWithFailover('product_generator', function (string $provider) use ($userPrompt, $systemPrompt, $inlineData, $hint) {
            $rawResponse = $this->callProvider($provider, $userPrompt, $systemPrompt, null, null, $inlineData);
            $cleanJson = trim($rawResponse);
            if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/i', $cleanJson, $matches)) {
                $cleanJson = trim($matches[1]);
            } elseif (preg_match('/(\{[\s\S]*\})/s', $cleanJson, $matches)) {
                $cleanJson = trim($matches[1]);
            }
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

            $decoded = json_decode($cleanJson, true);
            if (!is_array($decoded)) {
                $defaultTitle = !empty($hint) ? $hint : 'Premium Imported Product';
                $defaultSlug = Str::slug($defaultTitle);
                $sku = 'ZB-' . strtoupper(Str::random(6));
                return [
                    'title' => $defaultTitle,
                    'parent_category' => 'Electronics & Gadgets',
                    'sub_category' => 'Smart Devices',
                    'child_category' => 'Accessories',
                    'suggested_category' => 'Accessories',
                    'category_suggestion' => 'Accessories',
                    'sku' => $sku,
                    'purchase_price' => 950,
                    'selling_price' => 1450,
                    'mrp_price' => 1850,
                    'stock_quantity' => 50,
                    'price' => 1450,
                    'old_price' => 1850,
                    'cost_price' => 950,
                    'stock_qty' => 50,
                    'variants' => [
                        ['color_name' => 'Default', 'price' => 1450, 'stock' => 50],
                    ],
                    'specifications' => [
                        ['key' => 'Material', 'value' => 'Premium Grade'],
                        ['key' => 'Warranty', 'value' => 'Store Warranty'],
                    ],
                    'short_summary' => substr($rawResponse, 0, 160),
                    'short_description' => substr($rawResponse, 0, 160),
                    'detailed_html_description' => '<p>' . nl2br(htmlspecialchars($rawResponse)) . '</p>',
                    'description_html' => '<p>' . nl2br(htmlspecialchars($rawResponse)) . '</p>',
                    'url_slug' => $defaultSlug,
                    'meta_title' => $defaultTitle,
                    'meta_description' => substr($rawResponse, 0, 160),
                    'meta_keywords' => str_replace('-', ' ', $defaultSlug),
                ];
            }

            $sellingPrice = (int) ($decoded['price'] ?? ($decoded['selling_price'] ?? 1500));
            $purchasePrice = (int) ($decoded['purchase_price'] ?? ($decoded['cost_price'] ?? round($sellingPrice * 0.65)));
            $mrpPrice = (int) ($decoded['mrp_price'] ?? ($decoded['old_price'] ?? round($sellingPrice * 1.25)));
            $stockQty = (int) ($decoded['stock_quantity'] ?? ($decoded['stock_qty'] ?? 50));
            $title = !empty($decoded['title']) ? trim($decoded['title']) : (!empty($hint) ? $hint : 'Imported Product');

            $category = !empty($decoded['category']) ? trim((string) $decoded['category']) : (!empty($decoded['child_category']) ? trim((string) $decoded['child_category']) : 'General');
            $parentCategory = !empty($decoded['parent_category']) ? trim((string) $decoded['parent_category']) : 'General';
            $subCategory = !empty($decoded['sub_category']) ? trim((string) $decoded['sub_category']) : $category;
            $childCategory = !empty($decoded['child_category']) ? trim((string) $decoded['child_category']) : $category;

            $sku = !empty($decoded['sku']) ? trim($decoded['sku']) : ('ZB-' . strtoupper(substr(md5($title), 0, 6)));
            $urlSlug = !empty($decoded['url_slug']) ? Str::slug($decoded['url_slug']) : Str::slug($title);
            $shortSummary = $decoded['short_desc'] ?? ($decoded['short_summary'] ?? ($decoded['short_description'] ?? ''));
            $detailedDesc = $decoded['description'] ?? ($decoded['detailed_html_description'] ?? ($decoded['description_html'] ?? ''));
            $metaTitle = $decoded['meta_title'] ?? $title;
            $metaDesc = $decoded['meta_description'] ?? substr(strip_tags($detailedDesc ?: $shortSummary), 0, 160);
            $metaKeywords = !empty($decoded['meta_keywords']) ? trim($decoded['meta_keywords']) : str_replace('-', ' ', $urlSlug);

            $specs = [];
            if (!empty($decoded['specifications'])) {
                if (is_array($decoded['specifications'])) {
                    foreach ($decoded['specifications'] as $k => $v) {
                        if (is_array($v) && isset($v['key']) && isset($v['value'])) {
                            $specs[] = ['key' => (string) $v['key'], 'value' => (string) $v['value']];
                        } elseif (!is_numeric($k)) {
                            $specs[] = ['key' => (string) $k, 'value' => (string) $v];
                        }
                    }
                }
            }
            if (empty($specs)) {
                $specs = [
                    ['key' => 'Product', 'value' => $title],
                    ['key' => 'Category', 'value' => $childCategory],
                ];
            }

            $variants = [];
            if (!empty($decoded['variants']) && is_array($decoded['variants'])) {
                foreach ($decoded['variants'] as $var) {
                    if (is_array($var) && !empty($var['color_name'])) {
                        $variants[] = [
                            'color_name' => (string) $var['color_name'],
                            'price' => (float) ($var['price'] ?? $sellingPrice),
                            'stock' => (int) ($var['stock'] ?? round($stockQty / max(1, count($decoded['variants'])))),
                        ];
                    }
                }
            }
            if (empty($variants)) {
                $variants = [
                    ['color_name' => 'Standard', 'price' => $sellingPrice, 'stock' => $stockQty],
                ];
            }

            $decoded['title'] = $title;
            $decoded['category'] = $childCategory;
            $decoded['parent_category'] = $parentCategory;
            $decoded['sub_category'] = $subCategory;
            $decoded['child_category'] = $childCategory;
            $decoded['suggested_category'] = $childCategory;
            $decoded['category_suggestion'] = $childCategory;
            $decoded['sku'] = $sku;
            $decoded['price'] = $sellingPrice;
            $decoded['selling_price'] = $sellingPrice;
            $decoded['mrp_price'] = $mrpPrice;
            $decoded['old_price'] = $mrpPrice;
            $decoded['purchase_price'] = $purchasePrice;
            $decoded['cost_price'] = $purchasePrice;
            $decoded['stock_quantity'] = $stockQty;
            $decoded['stock_qty'] = $stockQty;
            $decoded['short_desc'] = $shortSummary;
            $decoded['short_summary'] = $shortSummary;
            $decoded['short_description'] = $shortSummary;
            $decoded['description'] = $detailedDesc;
            $decoded['detailed_html_description'] = $detailedDesc;
            $decoded['description_html'] = $detailedDesc;
            $decoded['url_slug'] = $urlSlug;
            $decoded['meta_title'] = $metaTitle;
            $decoded['meta_description'] = $metaDesc;
            $decoded['meta_keywords'] = $metaKeywords;
            $decoded['specifications'] = $specs;
            $decoded['variants'] = $variants;

            return $decoded;
        });
    }

    public function chatCustomerQa(string $message, array $history = []): array
    {
        $systemPrompt = "You are Zippy AI, the intelligent, friendly customer support assistant for ZippyBD, an e-commerce platform in Bangladesh. Help customers with product questions, shopping advice, delivery timeframes (Dhaka: 24-48h, Nationwide: 48-72h), cash on delivery, and store policies. Respond courteously, accurately, and concisely in the customer's language (Bengali or English).";

        $historyPrompt = '';
        if (!empty($history)) {
            foreach ($history as $chat) {
                $role = $chat['role'] === 'user' ? 'Customer' : 'Zippy AI';
                $content = $chat['content'] ?? '';
                $historyPrompt .= "{$role}: {$content}\n";
            }
            $historyPrompt .= "\n";
        }

        $userPrompt = $historyPrompt . "Customer: " . $message . "\nZippy AI:";

        return $this->executeWithFailover('customer_qa', function (string $provider) use ($userPrompt, $systemPrompt) {
            return $this->callProvider($provider, $userPrompt, $systemPrompt);
        });
    }

    public function chatWithFailover(
        array $messages,
        string $systemPrompt,
        string $role = 'customer_qa',
        int $maxTokens = 800,
        float $temperature = 0.4
    ): string {
        $exec = $this->executeWithFailover($role, function (string $provider) use ($messages, $systemPrompt, $maxTokens, $temperature) {
            $provider = strtolower(trim($provider));
            if ($provider === 'groq') {
                return $this->chatGroq($messages, $systemPrompt, null, null, $maxTokens, $temperature);
            }

            $conversation = '';
            foreach ($messages as $msg) {
                $speaker = ($msg['role'] ?? '') === 'user' ? 'Customer' : 'Zippy AI Assistant';
                $content = $msg['content'] ?? '';
                $conversation .= "{$speaker}: {$content}\n";
            }

            return $this->callProvider($provider, $conversation, $systemPrompt);
        });

        if (!empty($exec['success']) && !empty($exec['data'])) {
            return (string) $exec['data'];
        }

        throw new Exception($exec['error'] ?? 'AI service unavailable.');
    }

    public function generateSeoAndDescription(array $input): array
    {
        $systemPrompt = "You are an Elite E-Commerce Copywriter & Technical SEO Expert for ZippyBD (zippybd.com) in Bangladesh.
Your task is to write high-converting, realistic product descriptions and SEO metadata.

STRICT RULES:
1. OUTPUT FORMAT: You MUST return ONLY a strictly valid JSON object. No markdown formatting, no code blocks (like ```json), no explanations.
2. LANGUAGE & TONE: Use highly persuasive, natural Bengali (বাংলা) for descriptions and summaries. Keep brand names, technical specs, URL slugs, and JSON keys in English.
3. SHORT SUMMARY (short_desc): 2-3 engaging, benefit-driven sentences in Bengali answering 'Why buy this?'.
4. DETAILED DESCRIPTION (detailed_html_description): Generate clean, responsive HTML. Use <h4>, <p>, <ul>, <li>, <strong>. Structure:
   - Engaging Intro Paragraph.
   - <h4>প্রধান বৈশিষ্ট্যসমূহ</h4> (4-6 bullet points of key benefits).
   - <h4>স্পেসিফিকেশন</h4> (Technical details if any).
   - ZippyBD Guarantee Note (১০০% অরিজিনাল প্রোডাক্ট, দ্রুত ক্যাশ অন ডেলিভারি, সহজ রিটার্ন).
5. SEO METADATA:
   - meta_title: Catchy, exactly under 60 chars (e.g., '{Name} - Best Price in BD | ZippyBD').
   - meta_description: Click-worthy SERP snippet under 155 chars in Bengali/English.
   - meta_keywords: 6-8 highly searched comma-separated LSI keywords.
   - url_slug: Clean, lowercase, English, hyphen-separated.

JSON SCHEMA TO RETURN:
{
  \"short_desc\": \"...\",
  \"detailed_html_description\": \"...\",
  \"meta_title\": \"...\",
  \"meta_description\": \"...\",
  \"meta_keywords\": \"...\",
  \"url_slug\": \"...\"
}";

        $title = trim($input['title'] ?? ($input['name'] ?? ''));
        $category = trim($input['category'] ?? '');
        $price = $input['price'] ?? '';
        $oldPrice = $input['old_price'] ?? '';
        $tag = trim($input['tag'] ?? '');
        $specs = $input['specs'] ?? '';

        $promptParts = [];
        $promptParts[] = "Product Title: {$title}";
        if (!empty($category)) {
            $promptParts[] = "Category: {$category}";
        }
        if (!empty($price)) {
            $promptParts[] = "Selling Price: ৳" . number_format((float) $price, 0);
        }
        if (!empty($oldPrice)) {
            $promptParts[] = "Original MRP: ৳" . number_format((float) $oldPrice, 0);
        }
        if (!empty($tag)) {
            $promptParts[] = "Highlight Tag: {$tag}";
        }
        if (!empty($specs)) {
            if (is_array($specs)) {
                $specLines = [];
                foreach ($specs as $s) {
                    if (is_array($s) && !empty($s['key'])) {
                        $specLines[] = "- {$s['key']}: " . ($s['value'] ?? '');
                    }
                }
                if (!empty($specLines)) {
                    $promptParts[] = "Known Specs:\n" . implode("\n", $specLines);
                }
            } else {
                $promptParts[] = "Known Specs:\n" . trim((string) $specs);
            }
        }

        $userPrompt = implode("\n", $promptParts);

        return $this->executeWithFailover('product_generator', function (string $provider) use ($userPrompt, $systemPrompt, $title) {
            $rawResponse = $this->callProvider($provider, $userPrompt, $systemPrompt);

            $cleanJson = trim($rawResponse);
            if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/i', $cleanJson, $matches)) {
                $cleanJson = trim($matches[1]);
            } elseif (preg_match('/(\{[\s\S]*\})/s', $cleanJson, $matches)) {
                $cleanJson = trim($matches[1]);
            }
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

            $decoded = json_decode($cleanJson, true);

            if (!is_array($decoded)) {
                $defaultSlug = Str::slug($title ?: 'product');
                return [
                    'short_desc' => mb_substr($rawResponse, 0, 180),
                    'detailed_html_description' => '<p>' . nl2br(htmlspecialchars($rawResponse)) . '</p>',
                    'meta_title' => mb_substr($title, 0, 58),
                    'meta_description' => mb_substr($rawResponse, 0, 155),
                    'meta_keywords' => str_replace('-', ' ', $defaultSlug),
                    'url_slug' => $defaultSlug,
                ];
            }

            $slug = !empty($decoded['url_slug']) ? Str::slug($decoded['url_slug']) : Str::slug($title);

            return [
                'short_desc' => trim($decoded['short_desc'] ?? ''),
                'detailed_html_description' => trim($decoded['detailed_html_description'] ?? ''),
                'meta_title' => trim($decoded['meta_title'] ?? mb_substr($title, 0, 58)),
                'meta_description' => trim($decoded['meta_description'] ?? ''),
                'meta_keywords' => trim($decoded['meta_keywords'] ?? ''),
                'url_slug' => $slug,
            ];
        });
    }
}
