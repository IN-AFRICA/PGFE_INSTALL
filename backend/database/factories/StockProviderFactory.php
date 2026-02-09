<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StockProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StockProviderFactory extends Factory
{
    protected $model = StockProvider::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact' => $this->faker->phoneNumber(),
            'school_id' => \App\Models\School::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
