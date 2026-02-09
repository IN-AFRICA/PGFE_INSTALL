<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Seeder;

final class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        Country::all()->each(function (Country $country): void {
            collect([
                'Kasai-Central',
                'Kasai-Oriental',
                'Kasai',
                'Bandundu',
                'Nord-Kivu',
                'Sud-Kivu',
                'Nord-Ubangi',
                'Sud-Ubangi',
            ])->each(function (string $province) use ($country): void {
                // Include country_id inside the attribute array so each country can have
                // its own province with the same name (composite unique (country_id, name)).
                Province::firstOrCreate([
                    'country_id' => $country->id,
                    'name' => $province,
                ]);
            });
        });
    }
}
