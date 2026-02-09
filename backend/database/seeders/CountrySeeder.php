<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

final class CountrySeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Rwanda',
            'Morocco',
            'Tunisia',
            'Libya',
            'Egypt',
            'Sudan',
            'Mauritania',
            'Mali',
            'Niger',
            'Chad',
            'Democratic Republic of the Congo',
        ])->each(function (string $country): void {
            Country::firstOrCreate(['name' => $country]);
        });
    }
}
