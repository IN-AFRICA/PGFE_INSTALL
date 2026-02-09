<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicPersonal;
use App\Models\Discipline;
use App\Models\School;
use App\Models\Student;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DisciplineFactory extends Factory
{
    protected $model = Discipline::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'school_id' => School::factory(),
            'type_id' => Type::factory(),
            'academic_personal_id' => AcademicPersonal::factory(),
            'title' => $this->faker->words(3, true),
            'observation' => $this->faker->words(3, true),
            'level' => $this->faker->words(3, true),
            'discipline_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
