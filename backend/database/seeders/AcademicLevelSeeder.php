<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicLevel;
use Illuminate\Database\Seeder;

final class AcademicLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deprecated: géré par CycleSeeder (school -> cycle -> academicLevel -> filiaire -> classroom)
        // no-op
    }
}
