<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Territory;
use Illuminate\Database\Seeder;

final class TerritorySeeder extends Seeder
{
    public function run(): void
    {
        Province::all()->each(function (Province $province): void {
            collect([
                'Lubusha',
                'Luisha',
                'Sud',
                'Est',
                'Ouest',
            ])->each(function (string $territory) use ($province): void {
                Territory::firstOrCreate([
                    'name' => $territory,
                    'province_id' => $province->id,
                ]);
            });
        });
    }
}
