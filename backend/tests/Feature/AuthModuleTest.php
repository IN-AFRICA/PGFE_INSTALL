<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('register and login flow', function () {
    $register = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);
    $register->assertStatus(201);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    $login->assertStatus(200)->assertJsonStructure(['token']);
});

test('authenticated user can get profile', function () {
    $user = \App\Models\User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/v1/auth/me');
    $response->assertStatus(200)->assertJsonFragment(['email' => $user->email]);
});
