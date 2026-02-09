<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Parents;
use Illuminate\Database\Seeder;

final class ParentsSeeder extends Seeder
{
    public function run(): void
    {
        if (Parents::query()->exists()) {
            return;
        }

        Parents::factory()->count(10)->create();
    }
}
