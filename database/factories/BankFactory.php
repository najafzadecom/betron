<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'image' => $this->faker->imageUrl(640, 480, 'business', true, 'Bank'),
            'priority' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->boolean(80), // 80% chance of being true
            'transaction_status' => $this->faker->boolean(80), // 80% chance of being true
            'withdrawal_status' => $this->faker->boolean(80), // 80% chance of being true
        ];
    }
}
