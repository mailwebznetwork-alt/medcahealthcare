<?php

namespace Database\Factories;

use App\Models\PinCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PinCode>
 */
class PinCodeFactory extends Factory
{
    protected $model = PinCode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pin = (string) fake()->unique()->numerify('######');

        return [
            'pincode' => $pin,
            'area_name' => fake()->streetName(),
            'city' => 'Bangalore',
            'locality' => fake()->optional(0.6)->word(),
            'is_serviceable' => true,
            'is_active' => true,
            'delivery_charge' => fake()->optional(0.5)->randomFloat(2, 0, 500),
            'meta_title' => null,
            'meta_description' => null,
            'seo_keywords' => null,
            'slug' => null,
            'geo_page_ready' => false,
        ];
    }

    public function notServiceable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_serviceable' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
