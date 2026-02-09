<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Fee;
use App\Models\FeeType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

final class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition(): array
    {
        return [
            'label' => mb_strtoupper($this->faker->unique()->word).'_'.now()->format('Y'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency_id' => Currency::factory(),
            'fee_type_id' => FeeType::factory(),
            'school_id' => School::factory(),
            'effective_date' => $this->faker->optional()->date(),
        ];
    }
}
