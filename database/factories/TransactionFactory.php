<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Site;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => rand(11111, 99999), // istifadəçi factory varsa
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'amount' => $this->faker->randomFloat(2, 10, 10000), // min 10, max 10000
            'currency' => 'TRY',
            'wallet_id' => Wallet::factory(),
            'iban' => $this->faker->iban('TR'),
            'bank_id' => Bank::factory(),
            'client_ip' => $this->faker->ipv4,
            'status' => $this->faker->numberBetween(0, 2),
            'site_id' => Site::factory(),
        ];
    }
}
