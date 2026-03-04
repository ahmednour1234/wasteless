<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Bundle;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Illuminate\Support\Facades\Config::set('services.whish.channel', 'test_channel');
        \Illuminate\Support\Facades\Config::set('services.whish.secret', 'test_secret');
        \Illuminate\Support\Facades\Config::set('services.whish.website_url', 'https://test.com');
        \Illuminate\Support\Facades\Config::set('services.whish.env', 'sandbox');
        \Illuminate\Support\Facades\Config::set('app.url', 'https://wastelesslb.com/public');
    }

    public function test_order_store_creates_transaction_and_initiates_payment(): void
    {
        Http::fake([
            'api.sandbox.whish.money/*' => Http::response([
                'status' => true,
                'code' => null,
                'dialog' => null,
                'data' => [
                    'collectUrl' => 'https://whish.money/pay/test123'
                ]
            ], 200)
        ]);

        $customer = Customer::factory()->create();
        $bundle = Bundle::factory()->create([
            'price' => 100.00,
            'price_after_discount' => 90.00,
            'active' => true,
            'stock' => 10,
        ]);

        Sanctum::actingAs($customer, ['*']);

        $response = $this->postJson('/api/user/orders', [
            'items' => [
                [
                    'bundle_id' => $bundle->id,
                    'quantity' => 2
                ]
            ],
            'address' => 'Test Address',
            'name' => 'Test Name',
            'phone' => '123456789',
            'payment_type' => 'whish_money'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'transaction_id',
                'collect_url'
            ]);

        $this->assertDatabaseHas('transactions', [
            'payment_type' => 'whish_money',
            'status' => Transaction::STATUS_PENDING,
        ]);

        $transaction = Transaction::latest()->first();
        $this->assertNotNull($transaction->collect_url);
        $this->assertEquals('https://whish.money/pay/test123', $transaction->collect_url);
    }

    public function test_order_not_created_until_payment_success(): void
    {
        Http::fake([
            'api.sandbox.whish.money/*' => Http::response([
                'status' => true,
                'code' => null,
                'dialog' => null,
                'data' => [
                    'collectUrl' => 'https://whish.money/pay/test123'
                ]
            ], 200)
        ]);

        $customer = Customer::factory()->create();
        $bundle = Bundle::factory()->create([
            'price' => 100.00,
            'active' => true,
            'stock' => 10,
        ]);

        Sanctum::actingAs($customer, ['*']);

        $response = $this->postJson('/api/user/orders', [
            'items' => [
                [
                    'bundle_id' => $bundle->id,
                    'quantity' => 1
                ]
            ]
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('transactions', [
            'status' => Transaction::STATUS_PENDING,
        ]);
    }

    public function test_order_validation_fails_without_items(): void
    {
        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer, ['*']);

        $response = $this->postJson('/api/user/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_validation_fails_with_invalid_bundle(): void
    {
        $customer = Customer::factory()->create();
        Sanctum::actingAs($customer, ['*']);

        $response = $this->postJson('/api/user/orders', [
            'items' => [
                [
                    'bundle_id' => 99999,
                    'quantity' => 1
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.bundle_id']);
    }
}
