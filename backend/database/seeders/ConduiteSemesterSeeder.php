<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Conduite;
use App\Models\ConduiteSemester;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

final class ConduiteSemesterSeeder extends Seeder
{
    public function run(): void
    {
        $semesters = Semester::pluck('id', 'name');

        foreach (School::all() as $school) {
            $schoolYear = SchoolYear::where('school_id', $school->id)->where('is_active', true)->first()
                ?? SchoolYear::create(['school_id' => $school->id, 'name' => now()->year.'-'.(now()->year + 1), 'is_active' => true]);

            $conduite = Conduite::firstOrCreate(['school_id' => $school->id, 'label' => 'Conduite Générale']);

            foreach ($semesters as $name => $id) {
                ConduiteSemester::firstOrCreate([
                    'conduite_id' => $conduite->id,
                    'school_year_id' => $schoolYear->id,
                    'semester_id' => $id,
                ]);
            }
        }
    }
}
