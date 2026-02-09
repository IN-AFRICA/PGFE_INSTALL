<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

final class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        return [
            'academic_personal_id' => \App\Models\AcademicPersonal::factory(),
            'classroom_id' => \App\Models\Classroom::factory(),
            'school_id' => \App\Models\School::factory(),
            'subject' => $this->faker->words(3, true),
            'doc_prof' => $this->faker->words(3, true),
            'meth_proc' => $this->faker->randomFloat(2, 1, 1000),
            'matiere' => $this->faker->words(3, true),
            'march_lecon' => $this->faker->randomFloat(2, 1, 1000),
            'teacher' => $this->faker->words(3, true),
            'eleve' => $this->faker->words(3, true),
            'visit_time' => $this->faker->dateTime(),
            'appr_synth' => $this->faker->words(3, true),
        ];
    }
}
