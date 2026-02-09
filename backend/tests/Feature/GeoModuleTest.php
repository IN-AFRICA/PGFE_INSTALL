<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;
use App\Models\User;

uses(RefreshDatabase::class);

describe('Geo API', function () {
    it('forbidden for guests', function () {
        getJson('/api/v1/geo/countries')->assertUnauthorized();
    });

    it('can list countries for authenticated user', function () {
        $user = User::factory()->create();
        actingAs($user);
        getJson('/api/v1/geo/countries')->assertOk();
    });

    it('can list provinces for authenticated user', function () {
        $user = User::factory()->create();
        actingAs($user);
        getJson('/api/v1/geo/provinces')->assertOk();
    });
});
