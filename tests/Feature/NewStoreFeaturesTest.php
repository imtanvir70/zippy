<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;

class NewStoreFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    protected function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_name' => 'Administrator',
            'admin_email' => 'admin@Zippy.com'
        ]);
    }

    public function test_recent_sales_api_returns_success_and_array_of_sales(): void
    {
        $response = $this->getJson(route('api.recent_sales'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'sales' => [
                '*' => [
                    'name',
                    'location',
                    'product_title',
                    'product_url',
                    'product_image',
                    'price',
                    'time_ago',
                ]
            ]
        ]);
        $this->assertTrue($response->json('success'));
        $sales = $response->json('sales');
        $this->assertNotEmpty($sales);
        foreach ($sales as $sale) {
            $this->assertStringContainsString('***', $sale['name']);
        }
    }

    public function test_product_show_page_loads_with_bundle_products_and_reviews(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();
        $this->assertNotNull($product);

        $response = $this->get('/product/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee($product->title);
        $response->assertDontSee('Frequently Bought Together');
        $response->assertDontSee('স্পেশাল কম্বো অফার');
        $response->assertDontSee('সবগুলো একসাথে কার্টে নিন');
        $response->assertSee('আপনার রিভিউ লিখুন');
    }

    public function test_customer_can_submit_product_review_with_rating_and_updates_product_stats(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();
        $this->assertNotNull($product);

        $postData = [
            'customer_name' => 'তানভীর রহমান',
            'rating' => 5,
            'comment' => 'অসাধারণ প্রোডাক্ট! ডেলিভারি খুব ফাস্ট ছিল এবং প্যাকেজিং ভালো ছিল।',
        ];

        $response = $this->postJson(route('product.review.submit', $product->id), $postData);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'customer_name' => 'তানভীর রহমান',
            'rating' => 5,
            'status' => 'approved',
        ]);

        $updatedProduct = DB::table('products')->where('id', $product->id)->first();
        $this->assertEquals(5.0, (float)$updatedProduct->rating);
        $this->assertGreaterThanOrEqual(1, (int)$updatedProduct->reviews_count);
    }

    public function test_customer_can_submit_product_review_with_photo(): void
    {
        Storage::fake('public');

        $product = DB::table('products')->where('is_active', 1)->first();
        $this->assertNotNull($product);

        $file = UploadedFile::fake()->image('review_item.jpg', 600, 600);

        $postData = [
            'customer_name' => 'সাকিব আহমেদ',
            'rating' => 4,
            'comment' => 'প্রোডাক্টের কোয়ালিটি বেশ ভালো, ছবি যুক্ত করলাম।',
            'photo' => $file,
        ];

        $response = $this->post(route('product.review.submit', $product->id), $postData, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $review = DB::table('product_reviews')
            ->where('product_id', $product->id)
            ->where('customer_name', 'সাকিব আহমেদ')
            ->first();

        $this->assertNotNull($review);
        $this->assertNotNull($review->photo);
        $this->assertStringStartsWith('/uploads/reviews/', $review->photo);
    }

    public function test_customer_review_validation_errors(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();
        $this->assertNotNull($product);

        $response = $this->postJson(route('product.review.submit', $product->id), [
            'customer_name' => '',
            'rating' => 6,
            'comment' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['customer_name', 'rating', 'comment']);
    }

    public function test_admin_can_mark_abandoned_cart_as_recovered(): void
    {
        $cartId = DB::table('abandoned_carts')->insertGetId([
            'session_id' => 'sess_test_' . time(),
            'customer_name' => 'জামিল হোসেন',
            'customer_phone' => '01711223344',
            'cart_data' => json_encode([
                ['id' => 1, 'title' => 'স্মার্ট গ্যাজেট', 'price' => 1200, 'quantity' => 1]
            ]),
            'total_amount' => 1200,
            'is_recovered' => 0,
            'last_activity_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAsAdmin()->postJson(route('admin.abandoned.recover', $cartId));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('abandoned_carts', [
            'id' => $cartId,
            'is_recovered' => 1,
        ]);
    }
}
