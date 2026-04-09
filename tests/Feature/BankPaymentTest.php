<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Bundle;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

class BankPaymentTest extends TestCase
{
    use RefreshDatabase;

    // Real test merchant details (TEST environment only)
    const MERCHANT_ID   = 'TEST06300200';
    const GATEWAY_URL   = 'https://creditlibanais-netcommerce.gateway.mastercard.com';
    const API_VERSION   = '61';
    const API_PASSWORD  = 'Temp0r@ryP@$$1';

    // Gateway URL pattern used in Http::fake() – matches all MPGS REST API calls
    const MPGS_PATTERN  = 'creditlibanais-netcommerce.gateway.mastercard.com/*';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.mpgs.merchant_id',  self::MERCHANT_ID);
        Config::set('services.mpgs.api_password', self::API_PASSWORD);
        Config::set('services.mpgs.gateway_url',  self::GATEWAY_URL . '/');
        Config::set('services.mpgs.api_version',  self::API_VERSION);
        Config::set('app.url', 'https://wastelesslb.com/public');

        // Also keep Whish config to avoid errors in shared service code
        Config::set('services.whish.channel',     'test_channel');
        Config::set('services.whish.secret',      'test_secret');
        Config::set('services.whish.website_url', 'https://wastelesslb.com');
        Config::set('services.whish.env',         'sandbox');
    }

    // -----------------------------------------------------------------------
    // Helper: build a pending bank transaction with metadata
    // -----------------------------------------------------------------------
    private function createBankTransaction(Customer $customer, Bundle $bundle, array $override = []): Transaction
    {
        return Transaction::create(array_merge([
            'external_id'          => '9991234567890',
            'payment_type'         => Transaction::PAYMENT_TYPE_BANK,
            'amount'               => 90.00,
            'currency'             => 'USD',
            'status'               => Transaction::STATUS_PENDING,
            'invoice'              => 'Order Payment - Test',
            'success_callback_url' => 'https://wastelesslb.com/public/api/user/payments/callback/success',
            'failure_callback_url' => 'https://wastelesslb.com/public/api/user/payments/callback/failure',
            'success_redirect_url' => 'https://wastelesslb.com/public/api/user/payments/callback/success',
            'failure_redirect_url' => 'https://wastelesslb.com/public/api/user/payments/callback/failure',
            'collect_url'          => self::GATEWAY_URL . '/checkout/pay/SESSION001?checkoutVersion=1.0.0',
            'metadata'             => [
                'customer_id'    => $customer->id,
                'items'          => [
                    [
                        'bundle'   => $bundle->toArray(),
                        'quantity' => 1,
                        'price'    => 100.00,
                        'discount' => 10.00,
                        'total'    => 100.00,
                        'snapshot' => $bundle->toArray(),
                    ],
                ],
                'address'              => '123 Test Street',
                'name'                 => $customer->name,
                'phone'                => $customer->phone ?? '03000000',
                'sub_total'            => 100.00,
                'total_discount'       => 10.00,
                'mpgs_session_id'      => 'SESSION001',
                'mpgs_success_indicator' => 'SUCCESS_IND_ABC123',
            ],
        ], $override));
    }

    // -----------------------------------------------------------------------
    // 1. Placing an order with payment_type=bank creates a transaction and
    //    returns an MPGS hosted-checkout URL
    // -----------------------------------------------------------------------
    public function test_order_with_bank_payment_initiates_mpgs_checkout(): void
    {
        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'session' => [
                    'id'         => 'SESSION_MPGS_001',
                    'updateStatus' => 'SUCCESS',
                ],
                'successIndicator' => 'IND_TEST_001',
                'result'           => 'SUCCESS',
            ], 200),
        ]);

        $customer = Customer::factory()->create();
        $bundle   = Bundle::factory()->create([
            'price'                => 50.00,
            'price_after_discount' => 45.00,
            'active'               => true,
            'stock'                => 5,
        ]);

        Sanctum::actingAs($customer, ['*']);

        $response = $this->postJson('/api/user/orders', [
            'items'        => [['bundle_id' => $bundle->id, 'quantity' => 2]],
            'address'      => 'Beirut, Lebanon',
            'name'         => 'Ahmad Test',
            'phone'        => '03123456',
            'payment_type' => 'bank',
        ]);

        $response->assertStatus(201)
            ->assertJson(['status' => true, 'payment_type' => 'bank'])
            ->assertJsonStructure(['status', 'message', 'transaction_id', 'payment_type', 'collect_url']);

        // Transaction saved with correct type and pending status
        $this->assertDatabaseHas('transactions', [
            'payment_type' => Transaction::PAYMENT_TYPE_BANK,
            'status'       => Transaction::STATUS_PENDING,
        ]);

        // collect_url must point to the MPGS hosted checkout
        $transaction = Transaction::where('payment_type', Transaction::PAYMENT_TYPE_BANK)->latest()->first();
        $this->assertNotNull($transaction->collect_url);
        $this->assertStringContainsString('checkout/pay/SESSION_MPGS_001', $transaction->collect_url);

        // MPGS success indicator stored in metadata
        $metadata = $transaction->metadata;
        $this->assertEquals('IND_TEST_001', $metadata['mpgs_success_indicator']);
        $this->assertEquals('SESSION_MPGS_001', $metadata['mpgs_session_id']);
    }

    // -----------------------------------------------------------------------
    // 2. MPGS returns an error (bad credentials / network) → order fails
    // -----------------------------------------------------------------------
    public function test_order_with_bank_payment_fails_when_mpgs_returns_error(): void
    {
        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'error' => [
                    'cause'       => 'INVALID_REQUEST',
                    'explanation' => 'Invalid merchant credentials',
                ],
                'result' => 'ERROR',
            ], 401),
        ]);

        $customer = Customer::factory()->create();
        $bundle   = Bundle::factory()->create([
            'price'  => 50.00,
            'active' => true,
            'stock'  => 5,
        ]);

        Sanctum::actingAs($customer, ['*']);

        $response = $this->postJson('/api/user/orders', [
            'items'        => [['bundle_id' => $bundle->id, 'quantity' => 1]],
            'payment_type' => 'bank',
        ]);

        $response->assertStatus(400)->assertJson(['status' => false]);

        // Transaction recorded as failed
        $this->assertDatabaseHas('transactions', [
            'payment_type' => Transaction::PAYMENT_TYPE_BANK,
            'status'       => Transaction::STATUS_FAILED,
        ]);

        // No order created
        $this->assertDatabaseCount('orders', 0);
    }

    // -----------------------------------------------------------------------
    // 3. Bank return: resultIndicator matches → order created successfully
    // -----------------------------------------------------------------------
    public function test_bank_return_with_correct_result_indicator_creates_order(): void
    {
        $customer = Customer::factory()->create();
        $bundle   = Bundle::factory()->create(['price' => 100.00, 'active' => true, 'stock' => 10]);

        $transaction = $this->createBankTransaction($customer, $bundle);

        $response = $this->getJson(
            '/api/user/payments/bank/return'
            . '?external_id=' . $transaction->external_id
            . '&resultIndicator=SUCCESS_IND_ABC123'
        );

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['status', 'message', 'order_id']);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
        $this->assertNotNull($transaction->order_id);

        // Order row exists in DB
        $this->assertDatabaseHas('orders', [
            'id'          => $transaction->order_id,
            'customer_id' => $customer->id,
        ]);

        // Stock decremented
        $bundle->refresh();
        $this->assertEquals(9, $bundle->stock);
    }

    // -----------------------------------------------------------------------
    // 4. Bank return: resultIndicator does NOT match → fall back to gateway
    //    order query → gateway says CAPTURED → order created
    // -----------------------------------------------------------------------
    public function test_bank_return_wrong_indicator_falls_back_to_gateway_query_success(): void
    {
        $customer = Customer::factory()->create();
        $bundle   = Bundle::factory()->create(['price' => 100.00, 'active' => true, 'stock' => 10]);

        $transaction = $this->createBankTransaction($customer, $bundle);

        // MPGS order-status query returns CAPTURED
        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'id'     => $transaction->external_id,
                'status' => 'CAPTURED',
                'result' => 'SUCCESS',
            ], 200),
        ]);

        $response = $this->getJson(
            '/api/user/payments/bank/return'
            . '?external_id=' . $transaction->external_id
            . '&resultIndicator=WRONG_INDICATOR'
        );

        $response->assertStatus(200)->assertJson(['status' => true]);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
        $this->assertNotNull($transaction->order_id);
    }

    // -----------------------------------------------------------------------
    // 5. Bank return: resultIndicator does NOT match → gateway says FAILED
    //    → transaction marked failed, no order
    // -----------------------------------------------------------------------
    public function test_bank_return_wrong_indicator_falls_back_to_gateway_query_failure(): void
    {
        $customer = Customer::factory()->create();
        $bundle   = Bundle::factory()->create(['price' => 100.00, 'active' => true, 'stock' => 10]);

        $transaction = $this->createBankTransaction($customer, $bundle);

        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'id'     => $transaction->external_id,
                'status' => 'DECLINED',
                'result' => 'FAILURE',
            ], 200),
        ]);

        $response = $this->getJson(
            '/api/user/payments/bank/return'
            . '?external_id=' . $transaction->external_id
            . '&resultIndicator=WRONG_INDICATOR'
        );

        $response->assertStatus(400)->assertJson(['status' => false]);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_FAILED, $transaction->status);
        $this->assertDatabaseCount('orders', 0);
    }

    // -----------------------------------------------------------------------
    // 6. Bank return: no resultIndicator → payment was cancelled
    // -----------------------------------------------------------------------
    public function test_bank_return_without_result_indicator_marks_transaction_failed(): void
    {
        $customer    = Customer::factory()->create();
        $bundle      = Bundle::factory()->create(['price' => 100.00, 'active' => true, 'stock' => 10]);
        $transaction = $this->createBankTransaction($customer, $bundle);

        $response = $this->getJson(
            '/api/user/payments/bank/return?external_id=' . $transaction->external_id
        );

        $response->assertStatus(400)->assertJson(['status' => false]);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_FAILED, $transaction->status);
        $this->assertDatabaseCount('orders', 0);
    }

    // -----------------------------------------------------------------------
    // 7. Bank return: missing external_id → 400
    // -----------------------------------------------------------------------
    public function test_bank_return_without_external_id_returns_400(): void
    {
        $response = $this->getJson('/api/user/payments/bank/return?resultIndicator=SOME_IND');

        $response->assertStatus(400)->assertJson(['status' => false]);
    }

    // -----------------------------------------------------------------------
    // 8. Bank return: unknown external_id → 404
    // -----------------------------------------------------------------------
    public function test_bank_return_with_unknown_external_id_returns_404(): void
    {
        $response = $this->getJson(
            '/api/user/payments/bank/return?external_id=DOES_NOT_EXIST&resultIndicator=IND'
        );

        $response->assertStatus(404)->assertJson(['status' => false]);
    }

    // -----------------------------------------------------------------------
    // 9. Bank return: already processed → returns existing order_id
    // -----------------------------------------------------------------------
    public function test_bank_return_already_processed_returns_existing_order(): void
    {
        $customer = Customer::factory()->create();
        $bundle   = Bundle::factory()->create(['price' => 100.00, 'active' => true, 'stock' => 10]);

        $order = Order::create([
            'customer_id'           => $customer->id,
            'status'                => 'pending',
            'sub_total'             => 100.00,
            'total_discount'        => 10.00,
            'delivery'              => 0,
            'commission_percentage' => 0,
            'commission_amount'     => 0,
            'address'               => 'Test',
            'name'                  => $customer->name,
            'phone'                 => '03000000',
        ]);

        $transaction = $this->createBankTransaction($customer, $bundle, [
            'status'   => Transaction::STATUS_SUCCESS,
            'order_id' => $order->id,
        ]);

        $response = $this->getJson(
            '/api/user/payments/bank/return'
            . '?external_id=' . $transaction->external_id
            . '&resultIndicator=SUCCESS_IND_ABC123'
        );

        $response->assertStatus(200)
            ->assertJson([
                'status'   => true,
                'order_id' => $order->id,
            ]);
    }
}
