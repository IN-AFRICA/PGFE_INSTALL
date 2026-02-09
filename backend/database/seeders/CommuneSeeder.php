<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Province;
use Illuminate\Database\Seeder;

final class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        Province::all()->each(function (Province $province): void {
            collect([
                'Lubumbashi',
                'Annexe',
                'Kampemba',
            ])->each(function (string $commune) use ($province): void {
                Commune::firstOrCreate([
                    'name' => $commune,
                    'province_id' => $province->id,
                ]);
            });
        });
    }
}
