<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Models\Parents;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ParentsFactory extends Factory
{
    protected $model = Parents::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->lastName(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'genre' => 'Masculin',
            'phone_number' => '+243' . $this->faker->numerify('#########'),
        ];
    }
}
