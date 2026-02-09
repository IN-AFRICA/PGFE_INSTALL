<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

final class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = auth()->user()?->school_id ?? 1;
        $usd = Currency::where('code', 'USD')->first();
        $cdf = Currency::where('code', 'CDF')->first();

        // Taux pour USD (référence, donc 1)
        if ($usd) {
            ExchangeRate::firstOrCreate([
                'currency_id' => $usd->id,
                'school_id' => $schoolId,
            ], [
                'rate' => 1,
                'is_active' => true,
            ]);
        }

        // Taux pour CDF (par rapport à USD)
        if ($cdf) {
            ExchangeRate::firstOrCreate([
                'currency_id' => $cdf->id,
                'school_id' => $schoolId,
            ], [
                'rate' => 2500,
                'is_active' => true,
            ]);
        }
    }
}
