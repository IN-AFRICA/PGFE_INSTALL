<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CivilStatusEnum;
use App\Enums\GenderEnum;
use App\Models\AcademicLevel;
use App\Models\AcademicPersonal;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Fonction;
use App\Models\Parents;
use App\Models\Province;
use App\Models\School;
use App\Models\Territory;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class AcademicPersonalFactory extends Factory
{
    protected $model = AcademicPersonal::class;

    public function definition(): array
    {
        // Reuse existing records when available to avoid deep cascade factory creation
        $countryId = Country::query()->inRandomOrder()->value('id') ?? Country::factory();
        $provinceId = Province::query()->inRandomOrder()->value('id') ?? Province::factory();
        $territoryId = Territory::query()->inRandomOrder()->value('id') ?? Territory::factory();
        $communeId = Commune::query()->inRandomOrder()->value('id') ?? Commune::factory();
        $schoolId = School::query()->inRandomOrder()->value('id') ?? School::factory();
        $typeId = Type::query()->inRandomOrder()->value('id') ?? Type::factory();
        $fatherId = Parents::query()->inRandomOrder()->value('id') ?? Parents::factory();
        $motherId = Parents::query()->inRandomOrder()->value('id') ?? Parents::factory();
        $academicLevelId = AcademicLevel::query()->inRandomOrder()->value('id') ?? AcademicLevel::factory();
        $fonctionId = Fonction::query()->inRandomOrder()->value('id') ?? Fonction::factory();
        $userId = User::query()->inRandomOrder()->value('id') ?? User::factory();

        return [
            'matricule' => $this->faker->words(3, true),
            'name' => $this->faker->words(3, true),
            'post_name' => $this->faker->lastName(),
            'pre_name' => $this->faker->firstName(),
            'username' => $this->faker->userName(),
            'gender' => $this->faker->randomElement(GenderEnum::cases())->value,
            'civil_status' => $this->faker->randomElement(CivilStatusEnum::cases())->value,
            'country_id' => $countryId,
            'province_id' => $provinceId,
            'territory_id' => $territoryId,
            'commune_id' => $communeId,
            'school_id' => $schoolId,
            'type_id' => $typeId,
            'physical_address' => $this->faker->address(),
            'birth_date' => $this->faker->date(),
            'birth_place' => $this->faker->city(),
            'identity_card_number' => $this->faker->bothify('ID########'),
            'father_id' => $fatherId,
            'mother_id' => $motherId,
            'academic_level_id' => $academicLevelId,
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'fonction_id' => $fonctionId,
            'user_id' => $userId,
        ];
    }
}
