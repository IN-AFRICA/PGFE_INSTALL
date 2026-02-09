<?php

namespace Database\Factories;

use App\Models\Insertion\Application;
use App\Models\Insertion\Candidate;
use App\Models\Insertion\JobOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsertionApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'job_offer_id' => JobOffer::factory(),
            'status' => 'pending',
        ];
    }
}
