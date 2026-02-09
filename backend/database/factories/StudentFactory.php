<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CivilStatusEnum;
use App\Enums\GenderEnum;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Parents;
use App\Models\Province;
use App\Models\School;
use App\Models\Student;
use App\Models\Territory;
use Illuminate\Database\Eloquent\Factories\Factory;

final class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        // Reuse existing IDs if present to prevent large cascades and stay consistent with seeded data
        $provinceId = Province::query()->inRandomOrder()->value('id') ?? Province::factory();
        $countryId = Country::query()->inRandomOrder()->value('id') ?? Country::factory();
        $territoryId = Territory::query()->inRandomOrder()->value('id') ?? Territory::factory();
        $communeId = Commune::query()->inRandomOrder()->value('id') ?? Commune::factory();
        $parentsId = Parents::query()->inRandomOrder()->value('id') ?? Parents::factory();
        $school = School::query()->inRandomOrder()->value('id') ?? School::factory();

        $lastname = $this->faker->lastName;
        return [
            'matricule' => $this->faker->unique()->bothify('MAT####??'),
            'name' => $lastname,
            'lastname' => $lastname,
            'firstname' => $this->faker->firstName,
            // Limiter le genre à uniquement Masculin (MA) ou Féminin (FA)
            'gender' => $this->faker->randomElement([
                GenderEnum::MA->value,
                GenderEnum::FA->value,
            ]),
            'civil_status' => $this->faker->randomElement(CivilStatusEnum::cases())->value,
            'country_id' => $countryId,
            'province_id' => $provinceId,
            'territory_id' => $territoryId,
            'commune_id' => $communeId,
            'parents_id' => $parentsId,
            'address' => $this->faker->address,
            'birth_date' => $this->faker->date(),
            'birth_place' => $this->faker->city,
            'phone_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'school_id' => $school,
        ];
    }
}
