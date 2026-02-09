<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\Classroom;
use App\Models\Cycle;
use App\Models\Filiaire;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class CycleSeeder extends Seeder
{
    public function run(): void
    {
        // Cycles et niveaux exactement comme demandé
        $levelsByCycle = [
            'Long' => ['1er', '2nd', '3eme', '4eme', '5eme', '6eme'],
            'Court' => ['1er', '2n', '3em', '4eme'],
        ];

        // Filières exactement comme demandées
        $filieres = ['Eletrinoique', 'Mecanique', 'Imprimeri', 'Pedagogie'];

        $hasSchoolOnClassrooms = Schema::hasColumn('classrooms', 'school_id');

        // Pour chaque école, créer ses filières, cycles, niveaux et classes
        School::query()->each(function (School $school) use ($levelsByCycle, $filieres, $hasSchoolOnClassrooms): void {
            foreach ($filieres as $filName) {
                // 1) Filière par école
                $filiaire = $school->filiaires()->firstOrCreate([
                    'name' => $filName,
                    'school_id' => $school->id,
                ], [
                    'code' => substr($filName, 0, 3) . '-' . $school->id,
                    // Ensure uuid is set at insert time to satisfy NOT NULL constraint
                    'uuid' => (string) Str::uuid(),
                ]);

                foreach ($levelsByCycle as $cycleName => $levelNames) {
                    // 2) Cycle par filière
                    $cycle = $filiaire->cycles()->firstOrCreate([
                        'name' => $cycleName,
                    ]);

                    foreach ($levelNames as $levelName) {
                        // 3) Niveau académique rattaché au cycle
                        $level = AcademicLevel::query()->firstOrCreate([
                            'name' => $levelName,
                            'cycle_id' => $cycle->id,
                        ]);

                        // 4) Une classe par combinaison (niveau)
                        $attributes = [
                            'name' => $levelName.' - '.$filName,
                            'academic_level_id' => $level->id,
                        ];
                        if ($hasSchoolOnClassrooms) {
                            $attributes['school_id'] = $school->id;
                        }
                        Classroom::query()->firstOrCreate($attributes);
                    }
                }
            }
        });
    }
}
