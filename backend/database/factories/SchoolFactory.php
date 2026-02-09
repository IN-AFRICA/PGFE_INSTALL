<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\School;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $countryId = Country::query()->inRandomOrder()->value('id') ?? Country::factory();
        $typeId = Type::query()->inRandomOrder()->value('id') ?? Type::factory();

        return [
            'country_id' => $countryId,
            'city' => $this->faker->city(),
            'name' => $this->faker->unique()->company(),
            'address' => $this->faker->streetAddress(),
            'latitude' => null,
            'longitude' => null,
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'logo' => null,
            'type_id' => $typeId,
        ];
    }
}
