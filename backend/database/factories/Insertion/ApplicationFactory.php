<?php

namespace Database\Factories\Insertion;

use App\Models\Insertion\Application;
use App\Models\Insertion\Candidate;
use App\Models\Insertion\JobOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
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
