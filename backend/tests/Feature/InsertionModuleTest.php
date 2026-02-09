<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;
use App\Models\Insertion\Candidate;
use App\Models\Insertion\Company;
use App\Models\Insertion\JobOffer;
use App\Models\Insertion\Application;

uses(RefreshDatabase::class);

test('insertion: can register candidate', function () {
    $user = \App\Models\User::factory()->create();
    actingAs($user);
    $data = Candidate::factory()->make()->toArray();
    $response = postJson('/api/v1/insertion/candidates', $data);
    $response->assertCreated();
    expect($response->json('id'))->not->toBeNull();
});

test('insertion: can create job offer', function () {
    $user = \App\Models\User::factory()->create();
    actingAs($user);
    $company = Company::factory()->create();
    $data = [
        'title' => 'Développeur PHP',
        'description' => 'Mission de 6 mois',
        'company_id' => $company->id,
    ];
    $response = postJson('/api/v1/insertion/job-offers', $data);
    $response->assertCreated();
    expect($response->json('id'))->not->toBeNull();
    expect($response->json('is_open'))->toBeTrue();
});

test('insertion: cannot apply to closed job offer', function () {
    $user = \App\Models\User::factory()->create();
    actingAs($user);
    $candidate = Candidate::factory()->create();
    $company = Company::factory()->create();
    $offer = JobOffer::factory()->create(['company_id' => $company->id, 'is_open' => false]);
    $data = [
        'candidate_id' => $candidate->id,
        'job_offer_id' => $offer->id,
    ];
    $response = postJson('/api/v1/insertion/applications', $data);
    $response->assertStatus(422);
    expect($response->json('error'))->toBe('Job offer is closed');
});

test('insertion: can apply to open job offer', function () {
    $user = \App\Models\User::factory()->create();
    actingAs($user);
    $candidate = Candidate::factory()->create();
    $company = Company::factory()->create();
    $offer = JobOffer::factory()->create(['company_id' => $company->id, 'is_open' => true]);
    $data = [
        'candidate_id' => $candidate->id,
        'job_offer_id' => $offer->id,
    ];
    $response = postJson('/api/v1/insertion/applications', $data);
    $response->assertCreated();
    expect($response->json('id'))->not->toBeNull();
    expect($response->json('status'))->toBe('pending');
});
