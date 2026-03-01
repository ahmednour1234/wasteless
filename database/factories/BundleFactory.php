<?php

namespace Database\Factories;

use App\Models\Bundle;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BundleFactory extends Factory
{
    protected $model = Bundle::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->words(3, true),
            'image' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'price_after_discount' => null,
            'stock' => $this->faker->numberBetween(0, 100),
            'active' => true,
            'opening_time' => now(),
            'ended_time' => now()->addHours(2),
        ];
    }
}
