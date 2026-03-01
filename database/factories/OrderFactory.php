<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'status' => 'pending',
            'sub_total' => $this->faker->randomFloat(2, 100, 1000),
            'total_discount' => $this->faker->randomFloat(2, 0, 100),
            'delivery' => 0,
            'address' => $this->faker->address(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
