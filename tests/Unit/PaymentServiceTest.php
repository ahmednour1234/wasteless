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
}
