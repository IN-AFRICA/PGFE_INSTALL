<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\actingAs;
use App\Models\User;
use App\Models\InfraType;
use App\Models\InfraState;
use App\Models\InfraInventory;
use App\Models\InfraEquipment;
use App\Models\InfraInventoryEquipment;
use App\Models\InfraInventoryRealState;

uses(RefreshDatabase::class);

describe('Infrastructure API', function () {
    it('forbids guests on infrastructure endpoints', function () {
        getJson('/api/v1/infrastructures/types')->assertUnauthorized();
        getJson('/api/v1/infrastructures/equipments')->assertUnauthorized();
        getJson('/api/v1/infrastructures/states')->assertUnauthorized();
        getJson('/api/v1/infrastructures/inventories')->assertUnauthorized();
    });

    it('allows authenticated user to access index endpoints', function () {
        $user = User::factory()->create();
        actingAs($user);

        getJson('/api/v1/infrastructures/types')->assertOk();
        getJson('/api/v1/infrastructures/equipments')->assertOk();
        getJson('/api/v1/infrastructures/states')->assertOk();
        getJson('/api/v1/infrastructures/inventories')->assertOk();
    });

    it('can create an infrastructure type', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = postJson('/api/v1/infrastructures/types', [
            'name' => 'Salle de classe',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'name' => 'Salle de classe',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        expect(InfraType::count())->toBe(1);
    });

    it('can create an infrastructure state', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = postJson('/api/v1/infrastructures/states', [
            'name' => 'Bon etat',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'name' => 'Bon etat',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        expect(InfraState::count())->toBe(1);
    });

    it('can create an infrastructure inventory', function () {
        $user = User::factory()->create();
        actingAs($user);

        $response = postJson('/api/v1/infrastructures/inventories', [
            'inventory_date' => '2025-01-01',
            'note' => 'Inventaire test',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'inventory_date' => '2025-01-01',
            'note' => 'Inventaire test',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        expect(InfraInventory::count())->toBe(1);
    });

    it('can create an infrastructure equipment and attach it to an inventory', function () {
        $user = User::factory()->create();
        actingAs($user);

        $type = InfraType::create([
            'name' => 'Ordinateur',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        $state = InfraState::create([
            'name' => 'Neuf',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        $inventory = InfraInventory::create([
            'inventory_date' => '2025-01-02',
            'note' => 'Inventaire informatique',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        $equipmentResponse = postJson('/api/v1/infrastructures/equipments', [
            'name' => 'PC Portable',
            'type_id' => $type->id,
            'serial_number' => 'ABC-123',
            'location' => 'Salle 1',
            'state_id' => $state->id,
        ]);

        $equipmentResponse->assertCreated();
        $equipmentId = $equipmentResponse->json('id');
        expect($equipmentId)->not()->toBeNull();

        $linkResponse = postJson("/api/v1/infrastructures/inventories/{$inventory->id}/equipments", [
            'inventory_id' => $inventory->id,
            'equipment_id' => $equipmentId,
            'quantity' => 3,
        ]);

        $linkResponse->assertCreated();
        $linkResponse->assertJsonFragment([
            'inventory_id' => $inventory->id,
            'equipment_id' => $equipmentId,
            'quantity' => 3,
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        expect(InfraEquipment::count())->toBe(1);
        expect(InfraInventoryEquipment::count())->toBe(1);
    });

    it('can attach a real state to an inventory', function () {
        $user = User::factory()->create();
        actingAs($user);

        $state = InfraState::create([
            'name' => 'En reparation',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        $inventory = InfraInventory::create([
            'inventory_date' => '2025-01-03',
            'note' => 'Inventaire batiment',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        $response = postJson("/api/v1/infrastructures/inventories/{$inventory->id}/real-states", [
            'inventory_id' => $inventory->id,
            'state_id' => $state->id,
            'note' => 'Mur fissure',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'inventory_id' => $inventory->id,
            'state_id' => $state->id,
            'note' => 'Mur fissure',
            'school_id' => $user->school_id,
            'user_id' => $user->id,
        ]);

        expect(InfraInventoryRealState::count())->toBe(1);
    });
});
