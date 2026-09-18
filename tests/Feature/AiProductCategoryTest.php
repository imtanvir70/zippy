<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Services\Ai\AiService;

class AiProductCategoryTest extends TestCase
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
        if (self::$originalDatabaseSettings !== null) {
            $id = self::$originalDatabaseSettings['id'] ?? 1;
            $data = self::$originalDatabaseSettings;
            unset($data['id']);
            DB::table('ai_settings')->where('id', $id)->update($data);
            app(AiService::class)->clearCache();
        }

        DB::table('categories')->where('slug', 'like', 'test-%')->delete();
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

    public function test_ai_generate_returns_matched_category_when_similar_exists(): void
    {
        DB::table('categories')->updateOrInsert(
            ['slug' => 'test-desk-setup'],
            [
                'name' => 'Desk Setup Items',
                'name_bn' => 'Desk Setup Items',
                'slug' => 'test-desk-setup',
                'sort_order' => 999,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'gemini_api_key' => 'valid-test-key',
                'gemini_model' => 'gemini-2.5-flash',
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
                                        'title' => 'Ergonomic Desk Shelf',
                                        'category_suggestion' => 'Desk Setup Items',
                                        'price' => 3500,
                                        'stock_qty' => 10,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.products.ai.generate'), [
                'hint' => 'Solid Oak Desk Shelf',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'category_suggestion' => 'Desk Setup Items',
                'matched_category' => [
                    'name' => 'Desk Setup Items',
                ],
            ],
        ]);
    }

    public function test_ai_create_category_prevents_duplicates_via_similarity(): void
    {
        DB::table('categories')->updateOrInsert(
            ['slug' => 'test-mech-keyboards'],
            [
                'name' => 'Mechanical Keyboards',
                'name_bn' => 'Mechanical Keyboards',
                'slug' => 'test-mech-keyboards',
                'sort_order' => 888,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.products.ai.category.create'), [
                'name' => 'Mechanical Keyboard',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_existed' => true,
            'category' => [
                'name' => 'Mechanical Keyboards',
            ],
        ]);
    }

    public function test_ai_create_category_creates_new_record_when_unique(): void
    {
        $uniqueName = 'Unique' . Str::random(10);

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.products.ai.category.create'), [
                'name' => $uniqueName,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_existed' => false,
            'category' => [
                'name' => $uniqueName,
            ],
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => $uniqueName,
        ]);

        DB::table('categories')->where('name', $uniqueName)->delete();
    }

    public function test_ai_generate_with_language_parameter(): void
    {
        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'gemini_api_key' => 'valid-test-key',
                'gemini_model' => 'gemini-2.5-flash',
                'product_generator_provider' => 'gemini',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        app(AiService::class)->clearCache();

        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) {
                $body = json_decode($request->body(), true);
                $promptText = $body['contents'][0]['parts'][0]['text'] ?? '';
                $this->assertStringContainsString('Bengali', $promptText);

                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    [
                                        'text' => json_encode([
                                            'title' => 'স্মার্ট ব্লুটুথ হেডফোন',
                                            'suggested_category' => 'Gadgets',
                                            'sku' => 'ZB-BN-1234',
                                            'purchase_price' => 800,
                                            'selling_price' => 1200,
                                            'mrp_price' => 1500,
                                            'stock_quantity' => 25,
                                            'short_summary' => 'উন্নত সাউন্ড কোয়ালিটি',
                                            'detailed_html_description' => '<p>উন্নত সাউন্ড কোয়ালিটি সম্পন্ন হেডফোন</p>',
                                            'url_slug' => 'smart-bluetooth-headphone',
                                            'meta_title' => 'স্মার্ট ব্লুটুথ হেডফোন',
                                            'meta_description' => 'উন্নত সাউন্ড কোয়ালিটি সম্পন্ন হেডফোন',
                                        ]),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->withSession($this->getAdminSession())
            ->postJson(route('admin.products.ai.generate'), [
                'hint' => 'Wireless Headphone',
                'language' => 'Bengali',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'title' => 'স্মার্ট ব্লুটুথ হেডফোন',
                'suggested_category' => 'Gadgets',
                'selling_price' => 1200,
            ],
        ]);
    }

    public function test_ai_generate_with_multiple_gallery_images(): void
    {
        DB::table('ai_settings')->updateOrInsert(
            ['id' => 1],
            [
                'gemini_api_key' => 'valid-test-key',
                'gemini_model' => 'gemini-2.5-flash',
                'product_generator_provider' => 'gemini',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        app(AiService::class)->clearCache();

        Http::fake([
            'generativelanguage.googleapis.com/*' => function ($request) {
                $body = json_decode($request->body(), true);
                $parts = $body['contents'][0]['parts'] ?? [];

                $inlineCount = 0;
                foreach ($parts as $part) {
                    if (!empty($part['inline_data'])) {
                        $inlineCount++;
                    }
                }

                $this->assertEquals(3, $inlineCount);

                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    [
                                        'text' => json_encode([
                                            'title' => 'Ergonomic Executive Chair',
                                            'suggested_category' => 'Office Furniture',
                                            'sku' => 'ZB-FUR-9988',
                                            'purchase_price' => 4500,
                                            'selling_price' => 6500,
                                            'mrp_price' => 7900,
                                            'stock_quantity' => 15,
                                            'short_summary' => 'Ergonomic lumbar support chair',
                                            'detailed_html_description' => '<p>Executive ergonomic mesh chair</p>',
                                            'url_slug' => 'ergonomic-executive-chair',
                                            'meta_title' => 'Ergonomic Executive Chair',
                                            'meta_description' => 'Ergonomic lumbar support chair',
                                        ]),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $featuredFake = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 600, 600);
        $galleryFake1 = \Illuminate\Http\UploadedFile::fake()->image('gallery1.jpg', 600, 600);
        $galleryFake2 = \Illuminate\Http\UploadedFile::fake()->image('gallery2.jpg', 600, 600);

        $response = $this->withSession($this->getAdminSession())
            ->post(route('admin.products.ai.generate'), [
                'hint' => 'Ergonomic mesh chair with headrest',
                'featured_image' => $featuredFake,
                'gallery_images' => [$galleryFake1, $galleryFake2],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Ergonomic Executive Chair',
                'selling_price' => 6500,
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['main_image']);
        $this->assertCount(2, $data['gallery_images']);
    }
}
