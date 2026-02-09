<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StockArticle;
use App\Models\StockCategory;
use App\Models\StockProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StockArticleFactory extends Factory
{
    protected $model = StockArticle::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'category_id' => StockCategory::factory(),
            'provider_id' => StockProvider::factory(),
            'school_id' => \App\Models\School::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'min_threshold' => $this->faker->numberBetween(1, 10),
            'max_threshold' => $this->faker->numberBetween(20, 100),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
