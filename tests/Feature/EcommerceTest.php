<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;

class EcommerceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test homepage loads successfully.
     */
    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Zippy');
        $response->assertSee('ফ্ল্যাশ সেল');
    }

    /**
     * Test product details page loads with SEO schema.
     */
    public function test_product_page_loads_with_schema(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();
        $this->assertNotNull($product);

        $response = $this->get('/product/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee($product->title);
        $response->assertSee('schema.org');
    }

    /**
     * Test category catalog page.
     */
    public function test_category_page_loads(): void
    {
        $category = DB::table('categories')->where('is_active', 1)->first();
        $this->assertNotNull($category);

        $response = $this->get('/category/' . $category->slug);
        $response->assertStatus(200);
        $response->assertSee($category->name_bn);
    }

    /**
     * Test New Collection page loads.
     */
    public function test_new_collection_page_loads(): void
    {
        $response = $this->get('/new-collection');
        $response->assertStatus(200);
        $response->assertSee('নতুন কালেকশন');
    }

    /**
     * Test Best Sale page loads.
     */
    public function test_best_sale_page_loads(): void
    {
        $response = $this->get('/best-sale');
        $response->assertStatus(200);
        $response->assertSee('বেস্ট সেল কালেকশন');
    }

    /**
     * Test Quick View API returns JSON.
     */
    public function test_quick_view_api_returns_json(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();

        $response = $this->get('/api/quick-view/' . $product->id);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
            ]
        ]);
    }

    /**
     * Test Live Search Autocomplete API.
     */
    public function test_search_autocomplete_api(): void
    {
        $response = $this->get('/api/search?q=কিবোর্ড');
        $response->assertStatus(200);
        $response->assertJsonStructure(['suggestions']);
    }

    /**
     * Test Cart add and get workflow.
     */
    public function test_cart_workflow(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();

        // 1. Add to cart
        $addResponse = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $addResponse->assertStatus(200);
        $addResponse->assertJson(['success' => true, 'count' => 2]);

        // 2. Get cart
        $getResponse = $this->getJson('/cart/get');
        $getResponse->assertStatus(200);
        $getResponse->assertJson(['success' => true, 'count' => 2]);

        // 3. Direct Delete from cart
        $removeResponse = $this->postJson('/cart/remove', [
            'cart_key' => (string) $product->id,
            'product_id' => $product->id,
        ]);
        $removeResponse->assertStatus(200);
        $removeResponse->assertJson(['success' => true, 'count' => 0]);

        // 4. Restore to cart (Undo)
        $restoreResponse = $this->postJson('/cart/restore', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $restoreResponse->assertStatus(200);
        $restoreResponse->assertJson(['success' => true, 'count' => 2]);
    }

    /**
     * Test 1-page Checkout process & Order creation.
     */
    public function test_checkout_process_creates_order_and_decrements_stock(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();
        $initialStock = $product->stock_qty;

        // Set cart session
        $cart = [
            $product->id => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price' => (float) $product->price,
                'old_price' => (float) $product->old_price,
                'image' => $product->main_image,
                'qty' => 1,
                'total' => (float) $product->price,
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->postJson('/checkout/process', [
            'customer_name' => 'তানভীর রহমান',
            'customer_phone' => '01711223344',
            'customer_address' => 'রোড ৫, ধানমন্ডি, ঢাকা',
            'district' => 'ঢাকা',
            'payment_method' => 'cod',
            'notes' => 'দ্রুত ডেলিভারি চাই',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert order exists in DB
        $order = DB::table('orders')->where('customer_phone', '01711223344')->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('ঢাকা', $order->district);
        $this->assertEquals(60, $order->shipping_cost);

        // Assert order item exists
        $orderItem = DB::table('order_items')->where('order_id', $order->id)->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($product->id, $orderItem->product_id);

        // Assert stock was decremented
        $updatedProduct = DB::table('products')->where('id', $product->id)->first();
        $this->assertEquals($initialStock - 1, $updatedProduct->stock_qty);
    }

    /**
     * Test exactly 50 demo products are seeded across categories.
     */
    public function test_fifty_demo_products_seeded(): void
    {
        $count = DB::table('products')->count();
        $this->assertEquals(50, $count);

        $categories = DB::table('categories')->get();
        $this->assertCount(5, $categories);

        foreach ($categories as $cat) {
            $catProductCount = DB::table('products')->where('category_id', $cat->id)->count();
            $this->assertEquals(10, $catProductCount, "Category {$cat->slug} must have 10 demo products");
        }
    }

    /**
     * Test product details page loads with gallery, variants, and specifications.
     */
    public function test_product_page_loads_with_schema_and_features(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();
        $this->assertNotNull($product);

        $response = $this->get('/product/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee($product->title);
        $response->assertSee('schema.org');
        $response->assertSee('স্পেসিফিকেশন');
        $response->assertSee('ডেলিভারি ও রিটার্ন');
        $response->assertSee('Buy Now');
    }


    /**
     * Test dynamic XML Sitemap and robots.txt.
     */
    public function test_sitemap_and_robots(): void
    {
        $sitemapRes = $this->get('/sitemap.xml');
        $sitemapRes->assertStatus(200);
        $sitemapRes->assertHeader('Content-Type', 'application/xml');

        $robotsRes = $this->get('/robots.txt');
        $robotsRes->assertStatus(200);
        $robotsRes->assertSee('User-agent: *');
        $robotsRes->assertSee('Sitemap:');
    }

    /**
     * Test batch addition of multiple variants (e.g. 2 Red + 5 Black) with variant-wise pricing.
     */
    public function test_batch_add_multiple_variants_and_custom_pricing(): void
    {
        $product = DB::table('products')->where('is_active', 1)->first();
        $this->assertNotNull($product);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'variant' => 'রেড লিনিয়ার সুইচ',
                    'quantity' => 2,
                    'price' => 5400,
                ],
                [
                    'product_id' => $product->id,
                    'variant' => 'ইয়েলো প্রি-লুব্রিকেন্টেড সুইচ (+৳৩০০)',
                    'quantity' => 5,
                    'price' => 5700,
                ]
            ]
        ];


        $response = $this->postJson('/cart/add-batch', $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 7,
            'subtotal' => (2 * 5400) + (5 * 5700), // 10800 + 28500 = 39300
        ]);

        // Verify cart structure
        $cart = session()->get('cart');
        $this->assertCount(2, $cart);

        // Test checkout process with these multi-variant items
        $checkoutData = [
            'customer_name' => 'তানভীর রহমান',
            'customer_phone' => '01711223344',
            'district' => 'ঢাকা',
            'customer_address' => 'ধানমন্ডি ৩২, ঢাকা',
            'payment_method' => 'cod',
        ];

        $orderResponse = $this->postJson('/checkout/process', $checkoutData);
        $orderResponse->assertStatus(200);
        $orderResponse->assertJson(['success' => true]);


        $order = DB::table('orders')->where('customer_phone', '01711223344')->first();
        $this->assertNotNull($order);
        $this->assertEquals(39300, $order->subtotal);
        $this->assertEquals(39360, $order->total); // + 60 shipping

        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $this->assertCount(2, $items);
    }

    /**
     * Test high-performance homepage and AJAX load more endpoint.
     */
    public function test_high_performance_load_more_and_caching(): void
    {
        // 1. Initial homepage visit loads all active products & banners
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('ফ্ল্যাশ সেল');
        $response->assertSee('ANC ওয়্যারলেস স্টুডিও হেডফোন');

        // 2. Load more API returns paginated items
        $apiResponse = $this->getJson('/api/products/more?page=1&limit=6');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJson(['success' => true]);
        $this->assertCount(6, $apiResponse->json('products'));
    }

    /**
     * Test 3-column interactive Mega Menu renders dynamically with categories and products.
     */
    public function test_mega_menu_renders_with_categories_and_products(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('mega-menu-dropdown');
        $response->assertSee('btn-mega-trigger');
        $response->assertSee('Categories');
        $response->assertSee('ফিচারড প্রোডাক্টস');
    }

    /**
     * Test bilingual search autocomplete works in both Bengali and English.
     */
    public function test_bilingual_search_autocomplete_works_for_both_bangla_and_english(): void
    {
        // 1. Search in English: 'keyboard' should find products containing 'কিবোর্ড'
        $enResponse = $this->getJson('/api/search?q=keyboard');
        $enResponse->assertStatus(200);
        $enSuggestions = $enResponse->json('suggestions');
        $this->assertNotEmpty($enSuggestions);
        $enTitles = collect($enSuggestions)->pluck('title')->implode(' ');
        $this->assertStringContainsString('কিবোর্ড', $enTitles);

        // 2. Search in Bengali: 'মাউস' should find 'মাউস'
        $bnResponse = $this->getJson('/api/search?q=' . urlencode('মাউস'));
        $bnResponse->assertStatus(200);
        $bnSuggestions = $bnResponse->json('suggestions');
        $this->assertNotEmpty($bnSuggestions);
        $bnTitles = collect($bnSuggestions)->pluck('title')->implode(' ');
        $this->assertStringContainsString('মাউস', $bnTitles);

        // 3. Search in English: 'mouse' should also find 'মাউস'
        $enMouseResponse = $this->getJson('/api/search?q=mouse');
        $enMouseResponse->assertStatus(200);
        $enMouseSuggestions = $enMouseResponse->json('suggestions');
        $this->assertNotEmpty($enMouseSuggestions);
        $enMouseTitles = collect($enMouseSuggestions)->pluck('title')->implode(' ');
        $this->assertStringContainsString('মাউস', $enMouseTitles);
    }

    /**
     * Test full search results page renders with matching products.
     */
    public function test_full_search_results_page(): void
    {
        $response = $this->get('/search?q=lamp');
        $response->assertStatus(200);
        $response->assertSee('সার্চ রেজাল্ট');
        $response->assertSee('ল্যাম্প');
    }

    /**
     * Test all products index page renders properly.
     */
    public function test_all_products_page_loads(): void
    {
        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertSee('সকল প্রোডাক্টস');
    }

    /**
     * Test AJAX catalog filtering across endpoints.
     */
    public function test_ajax_catalog_filtering_returns_json(): void
    {
        // 1. All products AJAX filter
        $response = $this->getJson('/products?price_range=0-1000', [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'html', 'total', 'count_text']);
        $this->assertTrue($response->json('success'));

        // 2. Category AJAX filter
        $catResponse = $this->getJson('/category/desk?in_stock=1', [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);
        $catResponse->assertStatus(200);
        $catResponse->assertJson(['success' => true]);

        // 3. Search AJAX filter
        $searchResponse = $this->getJson('/search?q=keyboard&sort=price_asc', [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);
        $searchResponse->assertStatus(200);
        $searchResponse->assertJson(['success' => true]);

        // 4. Flash deals AJAX filter
        $flashResponse = $this->getJson('/flash-deals?sort=rating', [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);
        $flashResponse->assertStatus(200);
        $flashResponse->assertJson(['success' => true]);
    }

    /**
     * Test Flash Deals catalog page loads.
     */
    public function test_flash_deals_page_loads(): void
    {
        $response = $this->get('/flash-deals');
        $response->assertStatus(200);
        $response->assertSee('ফ্ল্যাশ ডিল');
    }
}
