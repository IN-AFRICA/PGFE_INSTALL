<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;
use App\Models\User;

uses(RefreshDatabase::class);

describe('Academic API', function () {
    it('forbidden for guests', function () {
        getJson('/api/v1/academic/levels')->assertUnauthorized();
    });

    it('can list academic levels for authenticated user', function () {
        $user = User::factory()->create();
        actingAs($user);
        getJson('/api/v1/academic/levels')->assertOk();
    });
});
