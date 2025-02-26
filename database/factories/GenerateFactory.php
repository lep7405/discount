<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Generate>
 */
class GenerateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'app_name' => 'cs',
            'app_url' => 'http://localhost:8000/generates_new',
            'discount_id' => 1,
            'expired_range' => 14,
        ];
    }
}
