<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicLevel;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Fonction;
use App\Models\Mechanisation;
use App\Models\Personal;
use App\Models\Province;
use App\Models\School;
use App\Models\Territory;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Personal>
 */
final class PersonalFactory extends Factory
{
    protected $model = Personal::class;

    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'matricule' => 'PER-'.now()->format('Y').'-'.Str::upper(Str::random(6)),
            'name' => fake()->lastName(),
            'post_name' => fake()->lastName(),
            'pre_name' => fake()->firstName($gender === 'male' ? 'male' : 'female'),
            'gender' => $gender,
            'civil_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'country_id' => Country::query()->inRandomOrder()->value('id') ?? Country::factory(),
            'province_id' => Province::query()->inRandomOrder()->value('id') ?? Province::factory(),
            'territory_id' => Territory::query()->inRandomOrder()->value('id') ?? Territory::factory(),
            'commune_id' => Commune::query()->inRandomOrder()->value('id') ?? Commune::factory(),
            'school_id' => School::query()->inRandomOrder()->value('id'),
            'type_id' => Type::query()->inRandomOrder()->value('id') ?? Type::factory(),
            'physical_address' => fake()->address(),
            'birth_date' => fake()->date(),
            'birth_place' => fake()->city(),
            'identity_card_number' => mb_strtoupper(Str::random(12)),
            'father_id' => null,
            'mother_id' => null,
            'academic_level_id' => AcademicLevel::query()->inRandomOrder()->value('id'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'fonction_id' => Fonction::query()->inRandomOrder()->value('id') ?? Fonction::factory(),
            'mechanisation_id' => Mechanisation::query()->inRandomOrder()->value('id')
                ?? Mechanisation::query()->create(['label' => 'Mecanisation', 'description' => null])->id,
        ];
    }
}
