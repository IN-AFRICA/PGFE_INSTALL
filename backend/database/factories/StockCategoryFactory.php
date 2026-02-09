<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StockCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StockCategoryFactory extends Factory
{
    protected $model = StockCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'school_id' => \App\Models\School::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
