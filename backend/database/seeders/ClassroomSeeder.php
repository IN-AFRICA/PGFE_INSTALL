<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Filiaire;
use App\Models\School;
use Illuminate\Database\Seeder;

final class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        // Deprecated: géré par CycleSeeder (school -> cycle -> academicLevel -> filiaire -> classroom)
        // no-op
    }
}
