<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Currency;
use App\Models\Fee;
use Illuminate\Database\Seeder;

final class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $usd = Currency::where('code', 'USD')->first();
        $cdf = Currency::where('code', 'CDF')->first();
        $exchangeUsd = \App\Models\ExchangeRate::where('currency_id', $usd?->id)->first();
        $exchangeCdf = \App\Models\ExchangeRate::where('currency_id', $cdf?->id)->first();
        $feeType = \App\Models\FeeType::first();
        foreach (Classroom::with('academicLevel.cycle.filiaire')->get() as $classroom) {
            // Derive school_id via filiaire since Cycle doesn't have a direct school_id column
            $schoolId = $classroom->academicLevel?->cycle?->filiaire?->school_id;
            if ($schoolId) {
                Fee::firstOrCreate([
                    'amount' => 100,
                    'currency_id' => $usd?->id,
                    'exchange_rate_id' => $exchangeUsd?->id,
                    'fee_type_id' => $feeType?->id,
                    'school_id' => $schoolId,
                    'effective_date' => now()->toDateString(),
                ]);
                Fee::firstOrCreate([
                    'amount' => 50,
                    'currency_id' => $cdf?->id,
                    'exchange_rate_id' => $exchangeCdf?->id,
                    'fee_type_id' => $feeType?->id,
                    'school_id' => $schoolId,
                    'effective_date' => now()->toDateString(),
                ]);
            }
        }
    }
}
