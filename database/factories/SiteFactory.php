<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'url' => $this->faker->url(),
            'logo' => $this->faker->imageUrl(),
            'description' => $this->faker->text(),
            'token' => $this->faker->unique()->sha256(),
            'status' => $this->faker->boolean()
        ];
    }
}
