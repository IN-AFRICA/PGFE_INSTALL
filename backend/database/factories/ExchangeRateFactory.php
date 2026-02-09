<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRate>
 */
final class ExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency_id' => Currency::factory(),
            'school_id' => School::factory(),
            'rate' => $this->faker->randomFloat(4, 100, 5000),
            'date_effective' => now(),
            'is_active' => (bool) ($this->faker->randomElement([false, true])),
        ];
    }
}
