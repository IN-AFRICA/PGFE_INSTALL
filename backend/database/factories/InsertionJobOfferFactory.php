<?php

namespace Database\Factories;

use App\Models\Insertion\JobOffer;
use App\Models\Insertion\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsertionJobOfferFactory extends Factory
{
    protected $model = JobOffer::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->text(100),
            'company_id' => Company::factory(),
            'is_open' => true,
        ];
    }
}
