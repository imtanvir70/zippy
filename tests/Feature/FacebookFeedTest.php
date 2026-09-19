<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FacebookFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_facebook_product_feed_returns_valid_xml()
    {
        $catId = DB::table('categories')->insertGetId([
            'name' => 'Gadgets',
            'name_bn' => 'গ্যাজেট',
            'slug' => 'gadgets',
            'sort_order' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'category_id' => $catId,
            'title' => 'TWS Bluetooth Earbuds Pro',
            'slug' => 'tws-bluetooth-earbuds-pro',
            'sku' => 'TWS-PRO-01',
            'price' => 1850.00,
            'stock_qty' => 15,
            'is_active' => 1,
            'main_image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df',
            'short_desc' => 'High quality noise cancelling wireless earbuds',
            'description' => 'Full product specifications and details',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->insert([
            ['key' => 'store_name', 'value' => 'Zippy', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->get('/facebook-product-feed.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">', false);
        $response->assertSee('<g:title><![CDATA[TWS Bluetooth Earbuds Pro]]></g:title>', false);
        $response->assertSee('<g:availability>in stock</g:availability>', false);
        $response->assertSee('<g:condition>new</g:condition>', false);
        $response->assertSee('<g:price>1850.00 BDT</g:price>', false);
        $response->assertSee('<g:brand>Zippy</g:brand>', false);
    }
}
