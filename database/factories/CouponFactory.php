<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Coupon::class;


    public function definition(): array
    {
        return [
            'code' => 'CODE' . $this->faker->unique()->numberBetween(1, 99999),
            'shop' => $this->faker->optional()->company,
            'discount_id' => $this->faker->randomElement(range(3, 1002)),
            ];
    }
}
