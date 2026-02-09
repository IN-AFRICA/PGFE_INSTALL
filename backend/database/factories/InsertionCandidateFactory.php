<?php

namespace Database\Factories;

use App\Models\Insertion\Candidate;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsertionCandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
