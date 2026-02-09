<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {

        return [
            'code' => mb_strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->word(),
            'symbol' => $this->faker->randomElement(['$', '€', '£', '¥', '₦']),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
