<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['text', 'number', 'boolean', 'json', 'email', 'url', 'textarea'];
        $groups = ['general', 'email', 'payment', 'security', 'api', 'notification', 'system'];
        $type = $this->faker->randomElement($types);

        return [
            'key' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'value' => $this->generateValueByType($type),
            'type' => $type,
            'group' => $this->faker->randomElement($groups),
        ];
    }

    /**
     * Generate value based on type
     */
    private function generateValueByType(string $type)
    {
        switch ($type) {
            case 'boolean':
                return $this->faker->boolean();
            case 'number':
                return $this->faker->numberBetween(1, 1000);
            case 'json':
                return [
                    'option1' => $this->faker->word(),
                    'option2' => $this->faker->word(),
                ];
            case 'email':
                return $this->faker->email();
            case 'url':
                return $this->faker->url();
            case 'textarea':
                return $this->faker->paragraph();
            default:
                return $this->faker->sentence();
        }
    }

    /**
     * Indicate that the setting is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the setting is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }
}
