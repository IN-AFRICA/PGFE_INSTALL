<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\Student;
use Illuminate\Database\Seeder;

final class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $classroomId = 1; // Classe existante
        $schoolYearId = 1; // Année scolaire existante
        $academicPersonalId = 1; // Personnel académique fictif ou existant
        $academicLevelId = 1; // Niveau académique fictif ou existant
        $students = Student::all();
        foreach ($students as $student) {
            Registration::firstOrCreate([
                'student_id' => $student->id,
                'classroom_id' => $classroomId,
                'school_year_id' => $schoolYearId,
                'school_id' => $student->school_id,
                'academic_personal_id' => $academicPersonalId,
                'academic_level_id' => $academicLevelId,
                'registration_date' => now()->format('Y-m-d'),
                'registration_status' => true,
            ]);
        }
    }
}
