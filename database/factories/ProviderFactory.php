<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
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
            'code' => $this->faker->unique()->bothify('PRV-####'), // Məs: PRV-1234
            'credentials' => json_encode([
                'endpoint' => $this->faker->url,
                'email' => $this->faker->email,
                'key' => $this->faker->sha1(),
            ]),
            'status' => $this->faker->boolean,
        ];
    }
}
