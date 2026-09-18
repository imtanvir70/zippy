<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\Ai\AiService;

class AiIntegrationTest extends TestCase
{
    protected static ?array $originalDatabaseSettings = null;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'EcommerceNew',
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        if (self::$originalDatabaseSettings === null) {
            $record = DB::table('ai_settings')->first();
            if ($record) {
                self::$originalDatabaseSettings = (array) $record;
            }
        }
    }

    protected function tearDown(): void
    {
        DB::table('products')->where('title', 'like', 'AI Generated Wireless Headphone%')->delete();

        if (self::$originalDatabaseSettings !== null) {
            $id = self::$originalDatabaseSettings['id'] ?? 1;
            $data = self::$originalDatabaseSettings;
            unset($data['id']);
            DB::table('ai_settings')->where('id', $id)->update($data);
            app(AiService::class)->clearCache();
        }

        parent::tearDown();
    }

    protected function getAdminSession(): array
    {
        $admin = DB::table('users')->where('role', 'super_admin')->first();
        if (!$admin) {
            $admin = DB::table('users')->first();
        }

        return [
            'admin_id' => $admin->id ?? 1,
            'admin_logged_in' => true,
        ];
    }

    public function test_admin_can_view_ai_settings_page(): void
    {
        $response = $this->withSession($this->getAdminSession())->get(route('admin.settings.ai'));

        $response->assertStatus(200);
        $response->assertSee('AI Integrations');
        $response->assertSee('Google Gemini');
        $response->assertSee('Groq Cloud');
        $response->assertSee('OpenRouter');
        $response->assertSee('Dynamic Role Assignment');
    }

    public function test_admin_can_update_ai_settings_via_ajax(): void
    {
        Cache::forget(AiService::CACHE_KEY);

        $payload = [
            'gemini_api_key' => 'test-gemini-key-12345',
            'gemini_model' => 'gemini-1.5-flash',
            'groq_api_key' => 'test-groq-key-67890',
            'groq_model' => 'llama-3.3-70b-versatile',
            'openrouter_api_key' => 'test-openrouter-key-99999',
            'openrouter_model' => 'meta-llama/llama-3.3-70b-instruct',
            'product_generator_provider' => 'gemini',
            'customer_qa_provider' => 'groq',
            'failover_provider' => 'openrouter',
            'is_active' => '1',
        ];

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.settings.ai.update'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $dbRecord = DB::table('ai_settings')->first();
        $this->assertNotNull($dbRecord);
        $this->assertEquals('test-gemini-key-12345', $dbRecord->gemini_api_key);
        $this->assertEquals('test-groq-key-67890', $dbRecord->groq_api_key);
        $this->assertEquals('test-openrouter-key-99999', $dbRecord->openrouter_api_key);
        $this->assertEquals('gemini', $dbRecord->product_generator_provider);
        $this->assertEquals('groq', $dbRecord->customer_qa_provider);
        $this->assertEquals('openrouter', $dbRecord->failover_provider);
        $this->assertEquals(1, $dbRecord->is_active);

        $cached = Cache::get(AiService::CACHE_KEY);
        $this->assertNotNull($cached);
        $this->assertEquals('test-gemini-key-12345', is_array($cached) ? $cached['gemini_api_key'] : $cached->gemini_api_key);
    }

    public function test_ai_service_automatic_failover_mechanism(): void
    {
        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'gemini_api_key' => 'primary-key-failing',
                'gemini_model' => 'gemini-1.5-flash',
                'openrouter_api_key' => 'backup-key-working',
                'openrouter_model' => 'meta-llama/llama-3.3-70b-instruct',
                'product_generator_provider' => 'gemini',
                'failover_provider' => 'openrouter',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        $aiService = app(AiService::class);
        $aiService->clearCache();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Rate limit reached']], 429),
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Failover Automated Listing',
                                'short_description' => 'Generated via OpenRouter hot backup engine.',
                                'description_html' => '<p>High quality catalog item.</p>',
                                'specifications' => ['Material' => 'Aluminum'],
                                'tags' => ['failover', 'ecommerce'],
                                'meta_title' => 'Failover Listing',
                                'meta_keywords' => ['backup', 'zippy'],
                            ])
                        ]
                    ]
                ]
            ], 200),
        ]);

        $result = $aiService->generateProductDetails([
            'name' => 'Executive Office Chair',
            'category' => 'Furniture',
            'specs' => 'Ergonomic mesh, class 4 lift',
        ]);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['was_failover']);
        $this->assertEquals('openrouter', $result['provider_used']);
        $this->assertEquals('gemini', $result['failed_primary']);
        $this->assertEquals('Failover Automated Listing', $result['data']['title']);
    }

    public function test_test_connection_endpoint(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'OK'
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.settings.ai.test'), [
                'provider' => 'groq',
                'api_key' => 'fake-groq-key',
                'model' => 'llama-3.3-70b-versatile',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'provider' => 'groq',
        ]);
    }

    public function test_admin_can_view_catalog_ai_generator_page(): void
    {
        $response = $this->withSession($this->getAdminSession())->get(route('admin.products.ai'));

        $response->assertStatus(200);
        $response->assertSee('Vision AI Product Studio');
        $response->assertSee('Catalog');
    }

    public function test_catalog_ai_generate_details_endpoint(): void
    {
        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'gemini_api_key' => 'valid-gemini-key',
                'gemini_model' => 'gemini-1.5-flash',
                'product_generator_provider' => 'gemini',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        app(AiService::class)->clearCache();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'title' => 'Ergonomic Desk Chair Pro',
                                        'short_description' => 'Premium ergonomic seating',
                                        'description_html' => '<p>Designed for all-day comfort.</p>',
                                        'specifications' => ['Color' => 'Black'],
                                        'tags' => ['chair', 'office'],
                                        'meta_title' => 'Ergonomic Chair Pro',
                                        'meta_keywords' => ['chair', 'ergonomic'],
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.products.ai.generate'), [
                'name' => 'Ergonomic Desk Chair Pro',
                'category' => 'Furniture',
                'specs' => 'Korean mesh',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Ergonomic Desk Chair Pro',
            ],
        ]);
    }

    public function test_catalog_ai_save_product_endpoint(): void
    {
        $category = DB::table('categories')->first();
        $catId = $category->id ?? 1;

        $payload = [
            'title' => 'AI Generated Wireless Headphone ' . uniqid(),
            'category_id' => $catId,
            'price' => 2999.00,
            'old_price' => 3999.00,
            'cost_price' => 1800.00,
            'stock_qty' => 45,
            'short_desc' => 'ANC Wireless Headphone with 40h battery.',
            'description' => '<p>High-fidelity audio with active noise cancellation.</p>',
            'main_image' => '/images/product-placeholder.svg',
            'meta_title' => 'ANC Wireless Headphone',
            'meta_description' => 'Best wireless headphone in BD',
            'meta_keywords' => 'audio, anc, headphone',
            'tag' => 'NEW',
            'specifications' => ['Driver' => '40mm', 'Battery' => '40 Hours'],
        ];

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.products.ai.save'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('products', [
            'title' => $payload['title'],
            'price' => 2999.00,
            'stock_qty' => 45,
            'tag' => 'NEW',
        ]);

        DB::table('products')->where('title', $payload['title'])->delete();
    }

    public function test_playground_chat_endpoint(): void
    {
        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'groq_api_key' => 'valid-groq-key',
                'groq_model' => 'llama-3.3-70b-versatile',
                'customer_qa_provider' => 'groq',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        app(AiService::class)->clearCache();

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Yes, we provide Cash on Delivery across all 64 districts in Bangladesh.'
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.settings.ai.playground.chat'), [
                'message' => 'Do you provide cash on delivery?',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'provider_used' => 'groq',
        ]);
        $this->assertStringContainsString('Cash on Delivery', $response->json('data'));
    }

    public function test_both_providers_fail_handled_gracefully(): void
    {
        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'gemini_api_key' => 'failing-key-1',
                'gemini_model' => 'gemini-1.5-flash',
                'openrouter_api_key' => 'failing-key-2',
                'openrouter_model' => 'meta-llama/llama-3.3-70b-instruct',
                'product_generator_provider' => 'gemini',
                'failover_provider' => 'openrouter',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        app(AiService::class)->clearCache();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'API down'], 500),
            'openrouter.ai/*' => Http::response(['error' => 'API also down'], 502),
        ]);

        $aiService = app(AiService::class);
        $result = $aiService->generateProductDetails(['name' => 'Failing Item']);

        $this->assertFalse($result['success']);
        $this->assertNull($result['provider_used']);
        $this->assertStringContainsString('Both primary [gemini] and backup [openrouter] providers failed', $result['error']);
    }

    public function test_groq_recovers_from_decommissioned_model_error(): void
    {
        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'groq_api_key' => 'test-groq-key',
                'groq_model' => 'future-model-x',
                'customer_qa_provider' => 'groq',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        app(AiService::class)->clearCache();

        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response([
                'data' => [
                    ['id' => 'llama-3.3-70b-versatile', 'active' => true],
                    ['id' => 'llama-3.1-8b-instant', 'active' => true],
                ]
            ], 200),
            'https://api.groq.com/openai/v1/chat/completions' => function ($request) {
                $payload = $request->data();
                if (($payload['model'] ?? '') === 'future-model-x') {
                    return Http::response([
                        'error' => [
                            'message' => 'The model `future-model-x` has been decommissioned and is no longer supported.',
                            'type' => 'invalid_request_error',
                            'code' => 'model_decommissioned',
                        ]
                    ], 400);
                }

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Auto-healed response successfully received.'
                            ]
                        ]
                    ]
                ], 200);
            },
        ]);

        $aiService = app(AiService::class);
        $result = $aiService->callGroq('Hello AI', null, 'test-groq-key', 'future-model-x');

        $this->assertEquals('Auto-healed response successfully received.', $result);

        $dbRecord = DB::table('ai_settings')->where('id', 1)->first();
        $this->assertEquals('llama-3.3-70b-versatile', $dbRecord->groq_model);
    }

    public function test_admin_can_sync_live_models(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response([
                'data' => [
                    ['id' => 'llama-3.3-70b-versatile', 'active' => true],
                    ['id' => 'deepseek-r1-distill-llama-70b', 'active' => true],
                ]
            ], 200),
        ]);

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.settings.ai.sync_models'), [
                'provider' => 'groq',
                'api_key' => 'valid-test-key',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'provider' => 'groq',
            'recommended' => 'llama-3.3-70b-versatile',
        ]);
        $response->assertJsonStructure([
            'models' => [
                'llama-3.3-70b-versatile',
            ],
        ]);
    }
}
