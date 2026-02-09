<?php

namespace Database\Factories\Insertion;

use App\Models\Insertion\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
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
