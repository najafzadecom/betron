<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name('Male'),
            'iban' => $this->faker->iban('TR'), // Türkiyə IBAN-ı
            'total_amount' => $this->faker->randomFloat(2, 0, 1000000),
            'blocked_amount' => $this->faker->randomFloat(2, 0, 1000000),
            'maximum_amount' => $this->faker->randomFloat(2, 50000, 500000),
            'single_deposit_min_amount' => $this->faker->randomFloat(2, 10, 500),
            'single_deposit_max_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'last_sync_date' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'bank_id' => Bank::factory(),
            'description' => $this->faker->sentence(10),
            'status' => $this->faker->boolean(50),
        ];
    }
}
