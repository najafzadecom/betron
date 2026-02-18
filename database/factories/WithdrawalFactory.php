<?php

namespace Database\Factories;

use App\Enums\WithdrawalStatus;
use App\Models\Bank;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 1000),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'bank_id' => Bank::factory(),
            'iban' => $this->faker->iban('TR'), // Assuming Turkish IBAN format
            'order_id' => $this->faker->uuid(), // Unique order ID for the withdrawal
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'status' => $this->faker->randomElement([
                WithdrawalStatus::Pending,
                WithdrawalStatus::Processing,
                WithdrawalStatus::Cancelled,
                WithdrawalStatus::AutoConfirmed,
                WithdrawalStatus::AutoCancelled,
            ]), // Random status
            'wallet_id' => Wallet::factory(), // Random wallet ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
