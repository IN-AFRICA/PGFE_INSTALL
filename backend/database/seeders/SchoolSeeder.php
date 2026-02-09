<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SchoolTypeEnum;
use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\Type;
use Illuminate\Database\Seeder;

final class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // S'assurer que les deux types existent (au cas où le TypeSeeder n'a pas été exécuté)
        foreach (SchoolTypeEnum::cases() as $case) {
            Type::firstOrCreate(['title' => $case->value]);
        }

        $types = Type::query()->get()->keyBy('title');

        Province::all()->each(function (Province $province) use ($types): void {
            $countryId = $province->country_id ?? Country::query()->value('id');
            if (! $countryId) {
                return; // aucun pays en base
            }

            $email = 'school@'.mb_strtolower($province->name).'.com';
            $name = 'School '.$province->name;

            // Alterne les types pour répartir FORMEL / NON FORMEL
            $chosenType = $province->id % 2 === 0 ? SchoolTypeEnum::FORMEL->value : SchoolTypeEnum::NON_FORMEL->value;
            $typeId = $types[$chosenType]->id ?? $types->first()->id;

            School::firstOrCreate([
                'name' => $name, // unique
            ], [
                'country_id' => $countryId,
                'city' => 'City '.$province->name,
                'address' => '123 Main St '.$province->name,
                'latitude' => null,
                'longitude' => null,
                'phone_number' => null,
                'email' => $email,
                'logo' => null,
                'type_id' => $typeId,
            ]);
        });
    }
}
