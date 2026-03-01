<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\Bundle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Illuminate\Support\Facades\Config::set('services.whish.channel', 'test_channel');
        \Illuminate\Support\Facades\Config::set('services.whish.secret', 'test_secret');
        \Illuminate\Support\Facades\Config::set('services.whish.website_url', 'https://test.com');
        \Illuminate\Support\Facades\Config::set('services.whish.env', 'sandbox');
    }

    public function test_success_callback_creates_order(): void
    {
        Http::fake([
            'api.sandbox.whish.money/*' => Http::response([
                'status' => true,
                'code' => null,
                'dialog' => null,
                'data' => [
                    'collectStatus' => 'success',
                    'payerPhoneNumber' => '96170902894'
                ]
            ], 200)
        ]);

        $customer = Customer::factory()->create();
        $bundle = Bundle::factory()->create([
            'id' => 1,
            'price' => 100.00,
            'price_after_discount' => 90.00,
            'active' => true,
            'stock' => 10,
        ]);

        $transaction = Transaction::create([
            'external_id' => '1234567890',
            'payment_type' => Transaction::PAYMENT_TYPE_WHISH_MONEY,
            'amount' => 180.00,
            'currency' => 'LBP',
            'status' => Transaction::STATUS_PENDING,
            'invoice' => 'Test Invoice',
            'success_callback_url' => 'https://example.com/success',
            'failure_callback_url' => 'https://example.com/failure',
            'success_redirect_url' => 'https://example.com/success-redirect',
            'failure_redirect_url' => 'https://example.com/failure-redirect',
            'metadata' => [
                'customer_id' => $customer->id,
                'items' => [
                    [
                        'bundle' => $bundle->toArray(),
                        'quantity' => 2,
                        'price' => 100.00,
                        'discount' => 10.00,
                        'total' => 200.00,
                        'snapshot' => $bundle->toArray(),
                    ]
                ],
                'address' => 'Test Address',
                'name' => 'Test Name',
                'phone' => '123456789',
                'sub_total' => 200.00,
                'total_discount' => 20.00,
            ],
        ]);

        $response = $this->postJson('/api/user/payments/callback/success', [
            'externalId' => '1234567890'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'order_id'
            ]);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
        $this->assertNotNull($transaction->order_id);

        $this->assertDatabaseHas('orders', [
            'id' => $transaction->order_id,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('order_details', [
            'order_id' => $transaction->order_id,
            'bundle_id' => $bundle->id,
            'quantity' => 2,
        ]);

        $bundle->refresh();
        $this->assertEquals(8, $bundle->stock);
    }

    public function test_failure_callback_updates_transaction_status(): void
    {
        $transaction = Transaction::create([
            'external_id' => '1234567890',
            'payment_type' => Transaction::PAYMENT_TYPE_WHISH_MONEY,
            'amount' => 100.50,
            'currency' => 'LBP',
            'status' => Transaction::STATUS_PENDING,
            'success_callback_url' => 'https://example.com/success',
            'failure_callback_url' => 'https://example.com/failure',
            'success_redirect_url' => 'https://example.com/success-redirect',
            'failure_redirect_url' => 'https://example.com/failure-redirect',
        ]);

        $response = $this->postJson('/api/user/payments/callback/failure', [
            'externalId' => '1234567890'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_FAILED, $transaction->status);
    }

    public function test_success_callback_without_external_id_fails(): void
    {
        $response = $this->postJson('/api/user/payments/callback/success', []);

        $response->assertStatus(400)
            ->assertJson([
                'status' => false,
            ]);
    }

    public function test_success_callback_with_invalid_external_id_fails(): void
    {
        $response = $this->postJson('/api/user/payments/callback/success', [
            'externalId' => '9999999999'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
            ]);
    }
}
