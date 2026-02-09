<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InputAccount;
use Illuminate\Database\Seeder;

final class InputAccountSeeder extends Seeder
{
    public function run(): void
    {
        InputAccount::factory()
            ->count(10)
            ->create();
    }
}
