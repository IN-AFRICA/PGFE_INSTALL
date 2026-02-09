<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Filiaire;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'filiaire_id' => Filiaire::factory(),
            'name' => $this->faker->unique()->words(3, true),
        ];
    }
}
