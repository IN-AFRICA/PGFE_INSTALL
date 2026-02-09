<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\actingAs;
use App\Models\User;
use App\Models\Parents;

uses(RefreshDatabase::class);

test('parents: can create parent', function () {
    $user = User::factory()->create();
    actingAs($user);
    $parent = Parents::factory()->make();
    $data = [
        'name' => $parent->name,
        'firstname' => $parent->firstname,
        'lastname' => $parent->lastname,
        'genre' => 'Masculin', // valeur explicite de l'enum
        'phone_number' => $parent->phone_number,
    ];
    $response = postJson('/api/v1/parents', $data);
    $response->assertCreated();
    expect($response->json('data.id'))->not->toBeNull();
});

test('parents: can list parents', function () {
    $user = User::factory()->create();
    actingAs($user);
    Parents::factory()->count(3)->create();
    $response = getJson('/api/v1/parents');
    $response->assertOk();
    expect($response->json())->toBeArray();
});

test('parents: can update parent', function () {
    $user = User::factory()->create();
    actingAs($user);
    $parent = Parents::factory()->create();
    $update = [
        'name' => 'UpdatedName',
        'firstname' => $parent->firstname,
        'lastname' => $parent->lastname,
        'genre' => $parent->genre,
        'phone_number' => $parent->phone_number,
    ];
    $response = putJson("/api/v1/parents/{$parent->id}", $update);
    $response->assertOk();
    expect($response->json('data.name'))->toBe('UpdatedName');
});

test('parents: can delete parent', function () {
    $user = User::factory()->create();
    actingAs($user);
    $parent = Parents::factory()->create();
    $response = deleteJson("/api/v1/parents/{$parent->id}");
    $response->assertOk();
    expect(Parents::find($parent->id))->toBeNull();
});
