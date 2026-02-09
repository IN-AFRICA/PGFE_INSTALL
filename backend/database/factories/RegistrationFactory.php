<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

final class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'school_id' => \App\Models\School::factory(),
            'academic_personal_id' => \App\Models\AcademicPersonal::factory(),
            'type_id' => \App\Models\Type::factory(),
            'registration_date' => $this->faker->words(3, true),
            'registration_status' => $this->faker->words(3, true),
            'note' => $this->faker->words(3, true),
        ];
    }
}
