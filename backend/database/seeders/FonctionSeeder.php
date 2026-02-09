<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Fonction;
use Illuminate\Database\Seeder;

final class FonctionSeeder extends Seeder
{
    public function run(): void
    {
        if (Fonction::query()->exists()) {
            return;
        }

        Fonction::factory()->count(10)->create();
    }
}
