<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OutputAccount;
use Illuminate\Database\Seeder;

final class OutputAccountSeeder extends Seeder
{
    public function run(): void
    {
        OutputAccount::factory()
            ->count(10)
            ->create();
    }
}
