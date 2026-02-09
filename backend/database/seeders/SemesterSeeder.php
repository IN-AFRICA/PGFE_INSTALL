<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

final class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['1 Semester', '2 Semester'];
        foreach ($names as $name) {
            Semester::firstOrCreate(['name' => $name]);
        }
    }
}
