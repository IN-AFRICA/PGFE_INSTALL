<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolYear>
 */
final class SchoolYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = SchoolYear::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2020, 2030);

        return [
            'school_id' => School::factory(), // Génère aussi une école liée
            'name' => "{$year}-".($year + 1),
            'is_active' => false,
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
