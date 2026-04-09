<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PaymentService;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('services.whish.channel', 'test_channel');
        Config::set('services.whish.secret', 'test_secret');
        Config::set('services.whish.website_url', 'https://test.com');
        Config::set('services.whish.env', 'sandbox');
    }

    public function test_initiate_payment_success(): void
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

        $transaction = Transaction::create([
            'external_id' => '1234567890',
            'payment_type' => Transaction::PAYMENT_TYPE_WHISH_MONEY,
            'amount' => 100.50,
            'currency' => 'LBP',
            'status' => Transaction::STATUS_PENDING,
            'invoice' => 'Test Invoice',
            'success_callback_url' => 'https://example.com/success',
            'failure_callback_url' => 'https://example.com/failure',
            'success_redirect_url' => 'https://example.com/success-redirect',
            'failure_redirect_url' => 'https://example.com/failure-redirect',
        ]);

        $paymentService = new PaymentService();
        $result = $paymentService->initiatePayment($transaction, []);

        $this->assertTrue($result['success']);
        $this->assertEquals('https://whish.money/pay/test123', $result['collect_url']);
        $this->assertEquals($transaction->id, $result['transaction_id']);

        $transaction->refresh();
        $this->assertEquals('https://whish.money/pay/test123', $transaction->collect_url);
        $this->assertEquals(Transaction::STATUS_PENDING, $transaction->status);
    }

    public function test_initiate_payment_failure(): void
    {
        Http::fake([
            'api.sandbox.whish.money/*' => Http::response([
                'status' => false,
                'code' => 'ERROR_001',
                'dialog' => [
                    'title' => 'Error',
                    'message' => 'Payment initiation failed'
                ]
            ], 200)
        ]);

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

        $paymentService = new PaymentService();
        $result = $paymentService->initiatePayment($transaction, []);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Payment initiation failed', $result['message']);
    }

    public function test_check_payment_status_success(): void
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

        $paymentService = new PaymentService();
        $result = $paymentService->checkPaymentStatus($transaction);

        $this->assertTrue($result['success']);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('96170902894', $result['payer_phone_number']);

        $transaction->refresh();
        $this->assertEquals('success', $transaction->collect_status);
        $this->assertEquals('96170902894', $transaction->payer_phone_number);
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
    }

    public function test_check_payment_status_failed(): void
    {
        Http::fake([
            'api.sandbox.whish.money/*' => Http::response([
                'status' => true,
                'code' => null,
                'dialog' => null,
                'data' => [
                    'collectStatus' => 'failed',
                    'payerPhoneNumber' => null
                ]
            ], 200)
        ]);

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

        $paymentService = new PaymentService();
        $result = $paymentService->checkPaymentStatus($transaction);

        $this->assertTrue($result['success']);
        $this->assertEquals('failed', $result['status']);

        $transaction->refresh();
        $this->assertEquals('failed', $transaction->collect_status);
        $this->assertEquals(Transaction::STATUS_FAILED, $transaction->status);
    }

    // ===================================================================
    // MPGS / Bank payment unit tests
    // Test merchant: TEST06300200
    // Gateway: https://creditlibanais-netcommerce.gateway.mastercard.com/
    // ===================================================================

    const MERCHANT_ID  = 'TEST06300200';
    const GATEWAY_URL  = 'https://creditlibanais-netcommerce.gateway.mastercard.com/';
    const API_VERSION  = '61';
    const API_PASSWORD = 'Temp0r@ryP@$$1';
    const MPGS_PATTERN = 'creditlibanais-netcommerce.gateway.mastercard.com/*';

    protected function setUpMpgsConfig(): void
    {
        Config::set('services.mpgs.merchant_id',  self::MERCHANT_ID);
        Config::set('services.mpgs.api_password', self::API_PASSWORD);
        Config::set('services.mpgs.gateway_url',  self::GATEWAY_URL);
        Config::set('services.mpgs.api_version',  self::API_VERSION);
        Config::set('app.url', 'https://wastelesslb.com/public');
    }

    private function makeBankTransaction(array $metadata = []): Transaction
    {
        return Transaction::create([
            'external_id'          => (string) time() . rand(100, 999),
            'payment_type'         => Transaction::PAYMENT_TYPE_BANK,
            'amount'               => 45.00,
            'currency'             => 'USD',
            'status'               => Transaction::STATUS_PENDING,
            'invoice'              => 'Bank Test Invoice',
            'success_callback_url' => 'https://wastelesslb.com/public/api/user/payments/bank/return',
            'failure_callback_url' => 'https://wastelesslb.com/public/api/user/payments/callback/failure',
            'success_redirect_url' => 'https://wastelesslb.com/public/api/user/payments/bank/return',
            'failure_redirect_url' => 'https://wastelesslb.com/public/api/user/payments/callback/failure',
            'metadata'             => array_merge([
                'mpgs_session_id'        => null,
                'mpgs_success_indicator' => null,
            ], $metadata),
        ]);
    }

    // -------------------------------------------------------------------
    // initiateBankPayment – success
    // MPGS creates a session and returns SESSION_ID + successIndicator
    // -------------------------------------------------------------------
    public function test_initiate_bank_payment_success(): void
    {
        $this->setUpMpgsConfig();

        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'session' => [
                    'id'           => 'SESSION_MPGS_UNIT_001',
                    'updateStatus' => 'SUCCESS',
                ],
                'successIndicator' => 'IND_UNIT_TEST_001',
                'result'           => 'SUCCESS',
            ], 200),
        ]);

        $transaction    = $this->makeBankTransaction();
        $paymentService = new PaymentService();
        $result         = $paymentService->initiateBankPayment($transaction);

        $this->assertTrue($result['success']);
        $this->assertEquals('SESSION_MPGS_UNIT_001', $result['session_id']);
        $this->assertStringContainsString('checkout/pay/SESSION_MPGS_UNIT_001', $result['collect_url']);
        $this->assertEquals($transaction->id, $result['transaction_id']);

        // Verify DB was updated
        $transaction->refresh();
        $this->assertStringContainsString('SESSION_MPGS_UNIT_001', $transaction->collect_url);
        $this->assertEquals(Transaction::STATUS_PENDING, $transaction->status);
        $this->assertEquals('IND_UNIT_TEST_001', $transaction->metadata['mpgs_success_indicator']);
        $this->assertEquals('SESSION_MPGS_UNIT_001', $transaction->metadata['mpgs_session_id']);
    }

    // -------------------------------------------------------------------
    // initiateBankPayment – MPGS returns an auth error (wrong password etc.)
    // -------------------------------------------------------------------
    public function test_initiate_bank_payment_failure_on_mpgs_error(): void
    {
        $this->setUpMpgsConfig();

        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'error' => [
                    'cause'       => 'INVALID_REQUEST',
                    'explanation' => 'Invalid merchant credentials',
                ],
                'result' => 'ERROR',
            ], 401),
        ]);

        $transaction    = $this->makeBankTransaction();
        $paymentService = new PaymentService();
        $result         = $paymentService->initiateBankPayment($transaction);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid merchant credentials', $result['message']);
    }

    // -------------------------------------------------------------------
    // initiateBankPayment – MPGS response has no session.id (malformed)
    // -------------------------------------------------------------------
    public function test_initiate_bank_payment_failure_on_missing_session_id(): void
    {
        $this->setUpMpgsConfig();

        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'result' => 'SUCCESS',
                // session.id is missing
            ], 200),
        ]);

        $transaction    = $this->makeBankTransaction();
        $paymentService = new PaymentService();
        $result         = $paymentService->initiateBankPayment($transaction);

        $this->assertFalse($result['success']);
    }

    // -------------------------------------------------------------------
    // verifyBankPayment – resultIndicator matches stored successIndicator
    // → immediately marked SUCCESS; no gateway query needed
    // -------------------------------------------------------------------
    public function test_verify_bank_payment_matching_success_indicator(): void
    {
        $this->setUpMpgsConfig();

        $transaction = $this->makeBankTransaction([
            'mpgs_success_indicator' => 'IND_MATCH_XYZ',
        ]);

        $paymentService = new PaymentService();
        $result         = $paymentService->verifyBankPayment($transaction, 'IND_MATCH_XYZ');

        $this->assertTrue($result['success']);
        $this->assertEquals('success', $result['status']);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);

        // No HTTP call should have been made
        Http::assertNothingSent();
    }

    // -------------------------------------------------------------------
    // verifyBankPayment – indicator mismatch → gateway query → CAPTURED
    // -------------------------------------------------------------------
    public function test_verify_bank_payment_mismatch_falls_back_to_query_and_succeeds(): void
    {
        $this->setUpMpgsConfig();

        $transaction = $this->makeBankTransaction([
            'mpgs_success_indicator' => 'IND_CORRECT',
        ]);

        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'id'     => $transaction->external_id,
                'status' => 'CAPTURED',
                'result' => 'SUCCESS',
            ], 200),
        ]);

        $paymentService = new PaymentService();
        $result         = $paymentService->verifyBankPayment($transaction, 'IND_WRONG');

        $this->assertTrue($result['success']);
        $this->assertEquals('success', $result['status']);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
    }

    // -------------------------------------------------------------------
    // verifyBankPayment – indicator mismatch → gateway says DECLINED
    // -------------------------------------------------------------------
    public function test_verify_bank_payment_mismatch_falls_back_to_query_and_fails(): void
    {
        $this->setUpMpgsConfig();

        $transaction = $this->makeBankTransaction([
            'mpgs_success_indicator' => 'IND_CORRECT',
        ]);

        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'id'     => $transaction->external_id,
                'status' => 'DECLINED',
                'result' => 'FAILURE',
            ], 200),
        ]);

        $paymentService = new PaymentService();
        $result         = $paymentService->verifyBankPayment($transaction, 'IND_WRONG');

        $this->assertFalse($result['success']);
        $this->assertEquals('failed', $result['status']);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_FAILED, $transaction->status);
    }

    // -------------------------------------------------------------------
    // verifyBankPayment – no stored successIndicator (edge case)
    //   → falls through to gateway query → AUTHORIZED
    // -------------------------------------------------------------------
    public function test_verify_bank_payment_without_stored_indicator_uses_gateway_query(): void
    {
        $this->setUpMpgsConfig();

        // No mpgs_success_indicator stored
        $transaction = $this->makeBankTransaction();

        Http::fake([
            self::MPGS_PATTERN => Http::response([
                'id'     => $transaction->external_id,
                'status' => 'AUTHORIZED',
                'result' => 'SUCCESS',
            ], 200),
        ]);

        $paymentService = new PaymentService();
        $result         = $paymentService->verifyBankPayment($transaction, 'ANY_INDICATOR');

        $this->assertTrue($result['success']);
        $this->assertEquals('success', $result['status']);

        $transaction->refresh();
        $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
    }
}
