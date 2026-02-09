<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Course;
use App\Models\School;
use Illuminate\Database\Seeder;

final class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $labels = ['Mathématiques', 'Français', 'Sciences', 'Histoire', 'Géographie'];
        $teachers = \App\Models\AcademicPersonal::all();
        if ($teachers->count() === 0) {
            echo "Aucun enseignant trouvé, aucun cours ne sera créé.\n";

            return;
        }
        foreach (School::with('filiaires.cycles.academicLevels.classrooms')->get() as $school) {
            foreach ($school->filiaires as $filiaire) {
                foreach ($filiaire->cycles as $cycle) {
                    foreach ($cycle->academicLevels as $level) {
                        foreach ($level->classrooms as $classroom) {
                            foreach ($labels as $idx => $label) {
                                $course = Course::firstOrCreate([
                                    'label' => $label.' - '.$classroom->name,
                                    'academic_level_id' => $level->id,
                                    'cycle_id' => $cycle->id,
                                    'filiaire_id' => $filiaire->id,
                                    'school_id' => $school->id,
                                    'classroom_id' => $classroom->id,
                                    'hourly_volume' => 2 + $idx,
                                    'max_period_1' => 10,
                                    'max_period_2' => 10,
                                    'max_period_3' => 10,
                                    'max_period_4' => 10,
                                    'max_exam_1' => 20,
                                    'max_exam_2' => 20,
                                ]);
                                // Associer plusieurs enseignants au cours
                                $course->academicPersonals()->syncWithoutDetaching($teachers->random(rand(1, 3))->pluck('id')->toArray());
                            }
                        }
                    }
                }
            }
        }
    }
}
