<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\AcademicPersonal;
use App\Models\Classroom;
use App\Models\Filiaire;
use App\Models\Registration;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

final class FirstYearElectronicsStudentsSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Trouver une école existante (pré-requis)
        $school = School::query()->first();
        if (! $school) {
            throw new RuntimeException("Aucune école trouvée. Veuillez créer une école avant d'exécuter ce seeder.");
        }

        // 2) Créer la filière "Électronique" liée à l'école
        $filiaire = $school->filiaires()->firstOrCreate([
            'name' => 'Électronique',
        ]);

        // 3) Créer le cycle "1er Cycle" lié à la filière
        $cycle = $filiaire->cycles()->firstOrCreate([
            'name' => '1er Cycle',
        ]);

        // 4) Créer le niveau "1ère" lié au cycle
        $level = AcademicLevel::query()->firstOrCreate([
            'name' => '1ère',
            'cycle_id' => $cycle->id,
        ]);

        // 5) Créer la classe "1ère Électronique" liée au niveau
        $classroom = Classroom::query()->firstOrCreate([
            'name' => '1ère Électronique',
            'academic_level_id' => $level->id,
            'school_id' => $school->id,
        ]);

        // 5) Vérifier dépendances pour Registration (optionnel)
        $schoolYear = SchoolYear::active($school->id) ?? SchoolYear::query()->where('school_id', $school->id)->latest('id')->first();
        $personal = AcademicPersonal::query()->where('school_id', $school->id)->first();
        $type = Type::query()->first();
        $canRegister = $schoolYear && $personal && $type;

        // 6) Créer 200 élèves et (si possible) les inscrire dans la classe
        DB::transaction(function () use ($school, $filiaire, $cycle, $level, $classroom, $schoolYear, $personal, $type, $canRegister) {
            $students = Student::factory()->count(200)->create([
                'school_id' => $school->id,
            ]);
            if ($canRegister) {
                foreach ($students as $student) {
                    Registration::create([
                        'student_id' => $student->id,
                        'school_id' => $school->id,
                        'filiaire_id' => $filiaire->id,
                        'cycle_id' => $cycle->id,
                        'academic_level_id' => $level->id,
                        'classroom_id' => $classroom->id,
                        'school_year_id' => $schoolYear->id,
                        'academic_personal_id' => $personal->id,
                        'type_id' => $type->id,
                        'registration_date' => now()->toDateString(),
                        'registration_status' => true,
                        'note' => 'Inscription générée par seeder: '.Str::random(6),
                    ]);
                }
            }
        });

        $this->command?->info('Seeder: 200 élèves créés pour la 1ère Électronique'.($canRegister ? ' + inscriptions' : ' (inscriptions non créées: dépendances manquantes)'));
    }
}
