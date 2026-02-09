<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discipline;
use Illuminate\Database\Seeder;

final class DisciplineSeeder extends Seeder
{
    public function run(): void
    {
        Discipline::factory()
            ->count(10)
            ->create();
    }
}
