<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //auto increment id thì không đặt trong cái này được
        return [
            //            'id' => $this->faker->numberBetween(50, 100), // Fake ID từ 50 đến 100
            'name' => $this->faker->sentence(2), // Tên giả lập
            'started_at' => $this->faker->dateTimeBetween('-1 month', 'now'), // Thời gian bắt đầu
            'expired_at' => $this->faker->dateTimeBetween('now', '+1 month'), // Thời gian hết hạn
            'type' => $this->faker->randomElement(['percentage', 'amount']), // Loại discount
            'value' => $this->faker->numberBetween(5, 50), // Giá trị discount
            'usage_limit' => $this->faker->randomNumber(2), // Giới hạn sử dụng
            'trial_days' => $this->faker->numberBetween(0, 14), // Số ngày dùng thử
            'discount_month' => $this->faker->numberBetween(1, 12), // Tháng áp dụng discount
        ];
    }
}
