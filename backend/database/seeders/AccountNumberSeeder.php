<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccountNumber;
use Illuminate\Database\Seeder;

final class AccountNumberSeeder extends Seeder
{
    public function run(): void
    {
        AccountNumber::factory()
            ->count(10)
            ->create();
    }
}
