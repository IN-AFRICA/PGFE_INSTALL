<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Registration;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Database\Seeder;

final class StudentSeeder extends Seeder
{
    public function run(): void
    {
        if (Student::query()->exists()) {
            return;
        }

        $classrooms = Classroom::with(['academicLevel.cycle.filiaire.school'])->get();
        if ($classrooms->isEmpty()) {
            return; // Pas de classe, pas d'élève
        }

        // Récupération robuste de l'année scolaire active
        $activeYear = method_exists(SchoolYear::class, 'active') && SchoolYear::active() ? SchoolYear::active()->first() : SchoolYear::query()->first();
        if (! $activeYear) {
            return; // Pas d'année scolaire, pas d'élève
        }

        $academicPersonalId = \App\Models\AcademicPersonal::query()->first()?->id;

        foreach ($classrooms as $classroom) {
            for ($i = 1; $i <= 5; $i++) {
                $academicLevel = $classroom->academicLevel;
                $cycle = $academicLevel?->cycle;
                $filiaire = $cycle?->filiaire;
                $school = $filiaire?->school;
                if (!$school?->id) {
                    // Impossible d'affecter un élève sans école
                    continue;
                }
                $student = Student::factory()->create([
                    'school_id' => $school->id,
                    'name' => 'Eleve_'.$classroom->id.'_'.$i,
                    'firstname' => 'Prenom_'.$classroom->id.'_'.$i,
                    'matricule' => 'MAT-'.$classroom->id.'-'.$i,
                    'email' => 'eleve'.$classroom->id.'_'.$i.'@school.com',
                ]);
                if ($activeYear && $academicPersonalId) {
                    Registration::firstOrCreate([
                        'student_id' => $student->id,
                        'school_id' => $school->id,
                        'filiaire_id' => $filiaire?->id,
                        'cycle_id' => $cycle?->id,
                        'academic_level_id' => $academicLevel?->id,
                        'classroom_id' => $classroom->id,
                        'school_year_id' => $activeYear->id,
                        'academic_personal_id' => $academicPersonalId,
                        'registration_date' => now()->toDateString(),
                        'registration_status' => true,
                    ]);
                }
            }
        }
    }
}
