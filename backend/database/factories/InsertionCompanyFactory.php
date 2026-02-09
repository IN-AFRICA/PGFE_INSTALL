<?php

namespace Database\Factories;

use App\Models\Insertion\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsertionCompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'contact' => $this->faker->phoneNumber(),
        ];
    }
}
