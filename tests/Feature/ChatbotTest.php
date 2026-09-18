<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Services\Ai\AiService;

class ChatbotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'EcommerceNew',
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    public function test_delivery_policy_inquiry_returns_accurate_policy_without_asking_for_tracking_number(): void
    {
        $mockAiService = $this->createMock(AiService::class);
        $mockAiService->expects($this->once())
            ->method('chatWithFailover')
            ->with(
                $this->anything(),
                $this->callback(function (string $prompt) {
                    return str_contains($prompt, 'STORE POLICY - DELIVERY DETAILS')
                        && str_contains($prompt, 'Inside Dhaka: ৳60')
                        && str_contains($prompt, 'Outside Dhaka: ৳120');
                }),
                $this->equalTo('customer_qa')
            )
            ->willReturn('আমাদের ডেলিভারি চার্জ ঢাকার ভেতরে মাত্র ৬০ টাকা এবং ঢাকার বাইরে ১২০ টাকা।');

        $this->app->instance(AiService::class, $mockAiService);

        $response = $this->postJson(route('api.chatbot.message'), [
            'message' => 'delivery charge koto?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'reply' => 'আমাদের ডেলিভারি চার্জ ঢাকার ভেতরে মাত্র ৬০ টাকা এবং ঢাকার বাইরে ১২০ টাকা।',
            'order_card' => null,
            'products' => [],
        ]);
    }

    public function test_payment_cod_inquiry_returns_cod_policy(): void
    {
        $mockAiService = $this->createMock(AiService::class);
        $mockAiService->expects($this->once())
            ->method('chatWithFailover')
            ->with(
                $this->anything(),
                $this->callback(function (string $prompt) {
                    return str_contains($prompt, 'STORE POLICY - PAYMENT & COD')
                        && str_contains($prompt, 'Cash on Delivery (COD): 100% available');
                }),
                $this->equalTo('customer_qa')
            )
            ->willReturn('জি! সারা বাংলাদেশে ক্যাশ অন ডেলিভারি সুবিধা রয়েছে।');

        $this->app->instance(AiService::class, $mockAiService);

        $response = $this->postJson(route('api.chatbot.message'), [
            'message' => 'cash on delivery ache ki?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'reply' => 'জি! সারা বাংলাদেশে ক্যাশ অন ডেলিভারি সুবিধা রয়েছে।',
            'order_card' => null,
            'products' => [],
        ]);
    }

    public function test_warranty_inquiry_returns_warranty_policy(): void
    {
        $mockAiService = $this->createMock(AiService::class);
        $mockAiService->expects($this->once())
            ->method('chatWithFailover')
            ->with(
                $this->anything(),
                $this->callback(function (string $prompt) {
                    return str_contains($prompt, 'STORE POLICY - WARRANTY & GUARANTEE')
                        && str_contains($prompt, '7 days full replacement warranty');
                }),
                $this->equalTo('customer_qa')
            )
            ->willReturn('আমাদের সকল প্রোডাক্টে ৭ দিনের রিপ্লেসমেন্ট ওয়ারেন্টি রয়েছে।');

        $this->app->instance(AiService::class, $mockAiService);

        $response = $this->postJson(route('api.chatbot.message'), [
            'message' => 'product nosto hole replacement warranty ache?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'order_card' => null,
            'products' => [],
        ]);
    }

    public function test_order_tracking_with_order_code_returns_interactive_order_card(): void
    {
        $testOrderNumber = 'ZB-TEST-' . rand(1000, 9999);
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => $testOrderNumber,
            'customer_name' => 'Test User',
            'customer_phone' => '01712345678',
            'customer_address' => 'Mirpur, Dhaka',
            'district' => 'ঢাকা',
            'subtotal' => 2500,
            'shipping_cost' => 60,
            'total' => 2560,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'processing',
            'courier_provider' => 'Steadfast',
            'courier_tracking_code' => 'ST-998811',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $mockAiService = $this->createMock(AiService::class);
            $mockAiService->method('chatWithFailover')
                ->willReturn("আপনার অর্ডার #{$testOrderNumber} এর তথ্য প্রদর্শিত হচ্ছে।");

            $this->app->instance(AiService::class, $mockAiService);

            $response = $this->postJson(route('api.chatbot.message'), [
                'message' => 'track order ' . $testOrderNumber,
            ]);

            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
            ]);
            $data = $response->json();
            $this->assertNotNull($data['order_card']);
            $this->assertEquals($testOrderNumber, $data['order_card']['order_number']);
            $this->assertEquals('processing', $data['order_card']['order_status']);
            $this->assertEquals(2, $data['order_card']['timeline_step']);
        } finally {
            DB::table('orders')->where('id', $orderId)->delete();
        }
    }

    public function test_product_search_intent_provides_relevant_catalog_products(): void
    {
        $mockAiService = $this->createMock(AiService::class);
        $mockAiService->method('chatWithFailover')
            ->willReturn('এখানে আমাদের কিছু অডিও হেডফোন রয়েছে।');

        $this->app->instance(AiService::class, $mockAiService);

        $response = $this->postJson(route('api.chatbot.message'), [
            'message' => 'apnader kache valo headphone ache?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['products']);
    }

    public function test_clear_history_flushes_chatbot_session(): void
    {
        $response = $this->withSession([
            'chatbot_history' => [['role' => 'user', 'content' => 'hello']],
            'chatbot_ordering' => ['step' => 'gathering'],
        ])->postJson(route('api.chatbot.clear'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertSessionMissing('chatbot_history');
        $response->assertSessionMissing('chatbot_ordering');
    }

    public function test_history_returns_dynamic_greeting_and_chips_based_on_product_page_activity(): void
    {
        $response = $this->postJson(route('api.chatbot.history'), [
            'activity' => [
                'page_type' => 'product',
                'current_product' => [
                    'title' => 'Redragon K552 Kumara Keyboard',
                    'slug' => 'redragon-k552',
                    'price' => 3500,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertStringContainsString('Redragon K552 Kumara Keyboard', $data['greeting']);
        $this->assertNotEmpty($data['chips']);
        $this->assertEquals('product', $data['activity_context']['page_type']);
        $this->assertEquals('Redragon K552 Kumara Keyboard', $data['activity_context']['product_title']);
    }

    public function test_history_returns_dynamic_greeting_and_chips_for_checkout_page(): void
    {
        $response = $this->postJson(route('api.chatbot.history'), [
            'activity' => [
                'page_type' => 'checkout',
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertStringContainsString('চেকআউট', $data['greeting']);
        $this->assertNotEmpty($data['chips']);
        $this->assertEquals('checkout', $data['activity_context']['page_type']);
    }

    public function test_message_returns_dynamic_follow_up_chips(): void
    {
        $mockAiService = $this->createMock(AiService::class);
        $mockAiService->method('chatWithFailover')
            ->willReturn('ঢাকার ভেতরে ডেলিভারি চার্জ ৬০ টাকা।');

        $this->app->instance(AiService::class, $mockAiService);

        $response = $this->postJson(route('api.chatbot.message'), [
            'message' => 'delivery charge koto?',
            'activity' => [
                'page_type' => 'product',
                'current_product' => [
                    'title' => 'Ajazz AK820 Pro',
                    'slug' => 'ajazz-ak820-pro',
                    'price' => 5400,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['chips']);
        $this->assertNotEmpty($data['chips']);
    }

    public function test_active_situational_awareness_injects_current_product_into_system_prompt(): void
    {
        $mockAiService = $this->createMock(AiService::class);
        $mockAiService->expects($this->once())
            ->method('chatWithFailover')
            ->with(
                $this->anything(),
                $this->callback(function (string $prompt) {
                    return str_contains($prompt, 'Active Product on Screen')
                        && str_contains($prompt, 'AULA F75 Wireless Mechanical Keyboard')
                        && str_contains($prompt, '5800');
                }),
                $this->equalTo('customer_qa')
            )
            ->willReturn('জি ভাইয়া! AULA F75 কিবোর্ডটি আমাদের স্টকে এভেইলেবল রয়েছে।');

        $this->app->instance(AiService::class, $mockAiService);

        $response = $this->postJson(route('api.chatbot.message'), [
            'message' => 'eta koto? stock ache?',
            'activity' => [
                'page_type' => 'product',
                'current_product' => [
                    'title' => 'AULA F75 Wireless Mechanical Keyboard',
                    'slug' => 'aula-f75',
                    'price' => 5800,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertStringContainsString('AULA F75', $data['reply']);
    }
}
