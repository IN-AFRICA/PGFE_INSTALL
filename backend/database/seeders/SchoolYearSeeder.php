<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

final class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = [
            ['name' => '2025-2026', 'is_active' => true, 'description' => 'Année scolaire 2025-2026'],
            ['name' => '2026-2027', 'is_active' => false, 'description' => 'Année scolaire 2026-2027'],
            ['name' => '2027-2028', 'is_active' => false, 'description' => 'Année scolaire 2027-2028'],
        ];
        foreach (School::all() as $school) {
            foreach ($years as $year) {
                SchoolYear::updateOrCreate(
                    ['school_id' => $school->id, 'name' => $year['name']],
                    [
                        'is_active' => $year['is_active'],
                        'description' => $year['description'],
                    ]
                );
            }
        }
    }
}
