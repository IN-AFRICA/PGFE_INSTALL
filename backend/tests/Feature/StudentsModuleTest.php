<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;
use App\Models\User;

use App\Models\Student;

uses(RefreshDatabase::class);

describe('Students API', function () {
    it('forbidden for guests', function () {
        getJson('/api/v1/students')->assertUnauthorized();
    });

    it('can list students for authenticated user', function () {
        $user = User::factory()->create();
        actingAs($user);
        getJson('/api/v1/students')->assertOk();
    });


    it('can create a student', function () {
        $user = User::factory()->create();
        actingAs($user);
        $data = Student::factory()->make()->toArray();
        $response = \Pest\Laravel\postJson('/api/v1/students', $data);
        $response->assertCreated();
        expect($response->json('student.id'))->not->toBeNull();
    });


    it('can update a student', function () {
        $user = User::factory()->create();
        actingAs($user);
        $student = Student::factory()->create();
        $update = ['name' => 'UpdatedName', 'lastname' => 'UpdatedName'];
        $response = \Pest\Laravel\putJson("/api/v1/students/{$student->id}", $update);
        $response->assertOk();
        expect($response->json('student.name'))->toBe('UpdatedName');
        expect($response->json('student.lastname'))->toBe('UpdatedName');
    });

    it('can delete a student', function () {
        $user = User::factory()->create();
        actingAs($user);
        $student = Student::factory()->create();
        $response = \Pest\Laravel\deleteJson("/api/v1/students/{$student->id}");
        $response->assertOk();
        expect(Student::find($student->id))->toBeNull();
    });
});
