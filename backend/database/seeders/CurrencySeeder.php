<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

final class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::firstOrCreate(['code' => 'USD'], ['name' => 'Dollar Américain', 'symbol' => '$']);
        Currency::firstOrCreate(['code' => 'CDF'], ['name' => 'Franc Congolais', 'symbol' => 'FC']);
    }
}
