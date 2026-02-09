<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Province;
use App\Models\Territory;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TerritoryFactory extends Factory
{
    protected $model = Territory::class;

    public function definition(): array
    {
        return [
            'province_id' => Province::factory(),
            'name' => $this->faker->unique()->words(3, true),
        ];
    }
}
