<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_can_be_created(): void
    {
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

        $this->assertDatabaseHas('transactions', [
            'external_id' => '1234567890',
            'payment_type' => Transaction::PAYMENT_TYPE_WHISH_MONEY,
            'amount' => 100.50,
            'status' => Transaction::STATUS_PENDING,
        ]);
    }

    public function test_transaction_belongs_to_order(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'pending',
            'sub_total' => 100.00,
            'total_discount' => 0,
            'delivery' => 0,
            'address' => 'Test Address',
            'name' => 'Test Name',
            'phone' => '123456789',
        ]);
        
        $transaction = Transaction::create([
            'order_id' => $order->id,
            'external_id' => '1234567890',
            'payment_type' => Transaction::PAYMENT_TYPE_WHISH_MONEY,
            'amount' => 100.50,
            'currency' => 'LBP',
            'status' => Transaction::STATUS_SUCCESS,
            'success_callback_url' => 'https://example.com/success',
            'failure_callback_url' => 'https://example.com/failure',
            'success_redirect_url' => 'https://example.com/success-redirect',
            'failure_redirect_url' => 'https://example.com/failure-redirect',
        ]);

        $this->assertInstanceOf(Order::class, $transaction->order);
        $this->assertEquals($order->id, $transaction->order->id);
    }

    public function test_transaction_metadata_is_casted_to_array(): void
    {
        $metadata = ['customer_id' => 1, 'items' => []];

        $transaction = Transaction::create([
            'external_id' => '1234567890',
            'payment_type' => Transaction::PAYMENT_TYPE_WHISH_MONEY,
            'amount' => 100.50,
            'currency' => 'LBP',
            'status' => Transaction::STATUS_PENDING,
            'metadata' => $metadata,
            'success_callback_url' => 'https://example.com/success',
            'failure_callback_url' => 'https://example.com/failure',
            'success_redirect_url' => 'https://example.com/success-redirect',
            'failure_redirect_url' => 'https://example.com/failure-redirect',
        ]);

        $this->assertIsArray($transaction->metadata);
        $this->assertEquals($metadata, $transaction->metadata);
    }

    public function test_transaction_amount_is_casted_to_decimal(): void
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

        $this->assertIsNumeric($transaction->amount);
        $this->assertEquals('100.50', (string) $transaction->amount);
    }

    public function test_transaction_status_constants(): void
    {
        $this->assertEquals('pending', Transaction::STATUS_PENDING);
        $this->assertEquals('success', Transaction::STATUS_SUCCESS);
        $this->assertEquals('failed', Transaction::STATUS_FAILED);
        $this->assertEquals('cancelled', Transaction::STATUS_CANCELLED);
    }

    public function test_transaction_payment_type_constants(): void
    {
        $this->assertEquals('whish_money', Transaction::PAYMENT_TYPE_WHISH_MONEY);
        $this->assertEquals('omt_pay', Transaction::PAYMENT_TYPE_OMT_PAY);
        $this->assertEquals('bank', Transaction::PAYMENT_TYPE_BANK);
    }
}
