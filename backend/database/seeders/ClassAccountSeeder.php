<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ClassAccount;
use Illuminate\Database\Seeder;

final class ClassAccountSeeder extends Seeder
{
    public function run(): void
    {
        ClassAccount::factory()
            ->count(10)
            ->create();
    }
}
