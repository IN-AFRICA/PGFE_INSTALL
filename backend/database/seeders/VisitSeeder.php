<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Visit;
use Illuminate\Database\Seeder;

final class VisitSeeder extends Seeder
{
    public function run(): void
    {
        Visit::factory()
            ->count(10)
            ->create();
    }
}
