<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'order_id' => null,
            'external_id' => (string) $this->faker->unique()->numerify('##########'),
            'payment_type' => Transaction::PAYMENT_TYPE_WHISH_MONEY,
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'currency' => 'USD',
            'status' => Transaction::STATUS_PENDING,
            'collect_url' => null,
            'collect_status' => null,
            'payer_phone_number' => null,
            'invoice' => 'Test Invoice',
            'success_callback_url' => 'https://example.com/success',
            'failure_callback_url' => 'https://example.com/failure',
            'success_redirect_url' => 'https://example.com/success-redirect',
            'failure_redirect_url' => 'https://example.com/failure-redirect',
            'metadata' => [],
        ];
    }
}
