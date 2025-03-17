<?php

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'started_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'expired_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'type' => $this->faker->randomElement(['percentage', 'amount']),
            'value' => $this->faker->numberBetween(5, 50),
            'usage_limit' => $this->faker->randomNumber(2),
            'trial_days' => $this->faker->numberBetween(0, 14),
        ];
    }
}
