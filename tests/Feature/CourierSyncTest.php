<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourierSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_unsupported_courier_webhook_returns_400(): void
    {
        $response = $this->postJson('/api/webhooks/courier/unknown_provider', [
            'tracking_code' => 'TEST-12345',
            'status' => 'delivered'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false
        ]);
    }

    public function test_steadfast_webhook_updates_order_and_history(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-TEST-STF-01',
            'customer_name' => 'John Doe',
            'customer_phone' => '01711112233',
            'customer_address' => 'Mirpur 10',
            'district' => 'Dhaka',
            'subtotal' => 1400.00,
            'shipping_cost' => 100.00,
            'total' => 1500.00,
            'payment_method' => 'cod',
            'order_status' => 'shipped',
            'courier_name' => 'steadfast',
            'courier_provider' => 'steadfast',
            'tracking_code' => 'STF-TEST9999',
            'courier_tracking_code' => 'STF-TEST9999',
            'consignment_id' => 'CONS-9999',
            'courier_consignment_id' => 'CONS-9999',
            'delivery_status' => 'in_transit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/webhooks/courier/steadfast', [
            'tracking_code' => 'STF-TEST9999',
            'status' => 'delivered',
            'note' => 'Delivered to customer successfully.'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $order = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals('delivered', $order->delivery_status);
        $this->assertEquals('delivered', $order->order_status);
        $this->assertEquals('delivered', $order->courier_status);

        $this->assertNotNull($order->courier_history);
        $history = json_decode($order->courier_history, true);
        $this->assertIsArray($history);
        $this->assertNotEmpty($history);

        $lastMilestone = end($history);
        $this->assertEquals('delivered', $lastMilestone['delivery_status']);
    }

    public function test_pathao_webhook_updates_order_status(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-TEST-PTH-02',
            'customer_name' => 'Jane Smith',
            'customer_phone' => '01822223344',
            'customer_address' => 'Banani',
            'district' => 'Dhaka',
            'subtotal' => 2100.00,
            'shipping_cost' => 100.00,
            'total' => 2200.00,
            'payment_method' => 'cod',
            'order_status' => 'shipped',
            'courier_name' => 'pathao',
            'courier_provider' => 'pathao',
            'tracking_code' => 'PTH-TEST8888',
            'consignment_id' => 'PATHAO-8888',
            'delivery_status' => 'in_transit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/webhooks/courier/pathao', [
            'consignment_id' => 'PATHAO-8888',
            'order_status' => 'Out for Delivery',
            'hub_name' => 'Banani Hub'
        ]);

        $response->assertStatus(200);

        $order = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals('out_for_delivery', $order->delivery_status);
    }

    public function test_redx_webhook_updates_order_status(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-TEST-RDX-03',
            'customer_name' => 'Alice Rahman',
            'customer_phone' => '01933334455',
            'customer_address' => 'Uttara',
            'district' => 'Dhaka',
            'subtotal' => 3000.00,
            'shipping_cost' => 100.00,
            'total' => 3100.00,
            'payment_method' => 'cod',
            'order_status' => 'shipped',
            'courier_name' => 'redx',
            'courier_provider' => 'redx',
            'tracking_code' => 'RDX-TEST7777',
            'consignment_id' => 'REDX-7777',
            'delivery_status' => 'in_transit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/webhooks/courier/redx', [
            'tracking_id' => 'RDX-TEST7777',
            'status' => 'delivered',
            'message' => 'Delivered on time'
        ]);

        $response->assertStatus(200);

        $order = DB::table('orders')->where('id', $orderId)->first();
        $this->assertEquals('delivered', $order->delivery_status);
        $this->assertEquals('delivered', $order->order_status);
    }

    public function test_sync_courier_status_artisan_command_runs_successfully(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-TEST-SYNC-04',
            'customer_name' => 'Test User',
            'customer_phone' => '01644445566',
            'customer_address' => 'Dhanmondi',
            'district' => 'Dhaka',
            'subtotal' => 1100.00,
            'shipping_cost' => 100.00,
            'total' => 1200.00,
            'payment_method' => 'cod',
            'order_status' => 'shipped',
            'courier_name' => 'steadfast',
            'courier_provider' => 'steadfast',
            'tracking_code' => 'STF-TESTSYNC4',
            'delivery_status' => 'processing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('orders:sync-courier-status')
            ->assertSuccessful();

        $order = DB::table('orders')->where('id', $orderId)->first();
        $this->assertNotNull($order->courier_history);
    }

    public function test_frontend_order_tracking_page_renders_with_courier_details(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'ZB-TRACK-TEST-05',
            'customer_name' => 'Kazi Nazrul',
            'customer_phone' => '01555556677',
            'customer_address' => 'Mohakhali DOHS',
            'district' => 'Dhaka',
            'subtotal' => 2400.00,
            'shipping_cost' => 100.00,
            'total' => 2500.00,
            'payment_method' => 'cod',
            'order_status' => 'shipped',
            'courier_name' => 'steadfast',
            'courier_provider' => 'steadfast',
            'tracking_code' => 'STF-LIVE5555',
            'delivery_status' => 'in_transit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('order.track', ['track_id' => 'STF-LIVE5555']));

        $response->assertStatus(200);
        $response->assertSee('ZB-TRACK-TEST-05');
        $response->assertSee('STF-LIVE5555');
        $response->assertSee('Steadfast Courier');
        $response->assertSee('কুরিয়ারে হস্তান্তর');
    }
}
