<?php
// Infrastructures routes

use App\Http\Controllers\InfraTypeController;
use App\Http\Controllers\InfraEquipmentController;
use App\Http\Controllers\InfraStateController;
use App\Http\Controllers\InfraInventoryController;
use App\Http\Controllers\InfraInventoryEquipmentController;
use App\Http\Controllers\InfraInventoryRealStateController;
use App\Http\Controllers\Api\Infrastructure\InfraInfrastructureController;
use App\Http\Controllers\Api\Infrastructure\InfraInfrastructureInventaireController;
use App\Http\Controllers\Api\Infrastructure\InfraCategorieController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('infrastructures')
    ->name('infra.')
    ->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\Infrastructure\InfraDashboardController::class, 'index']);
        Route::apiResource('types', InfraTypeController::class);
        Route::apiResource('categories', InfraCategorieController::class);
        Route::apiResource('bailleurs', \App\Http\Controllers\Api\Infrastructure\InfraBailleurController::class);
        Route::apiResource('equipments', InfraEquipmentController::class);
        Route::apiResource('states', InfraStateController::class);
        Route::apiResource('inventories', InfraInventoryController::class);
        Route::apiResource('infrastructures', InfraInfrastructureController::class);
        Route::apiResource('inventory-equipments', InfraInventoryEquipmentController::class);
        Route::post('inventories/{inventory}/equipments', [InfraInventoryEquipmentController::class, 'store']);
        Route::post('inventories/{inventory}/real-states', [InfraInventoryRealStateController::class, 'store']);
        Route::apiResource('infrastructure-inventaires', InfraInfrastructureInventaireController::class);
        Route::post('inventaire/{id}/item', [\App\Http\Controllers\Api\Infrastructure\InfraInfrastructureInventaireItemController::class, 'store']);
        Route::get('infrastructure-inventaires/{id}/item', [\App\Http\Controllers\Api\Infrastructure\InfraInfrastructureInventaireItemV2Controller::class, 'index']);
        Route::post('infrastructure-inventaires/{id}/item', [\App\Http\Controllers\Api\Infrastructure\InfraInfrastructureInventaireItemV2Controller::class, 'store']);
        Route::get('inventories/{id}/item', [\App\Http\Controllers\InfraInventoryItemController::class, 'index']);
        Route::post('inventories/{id}/item', [\App\Http\Controllers\InfraInventoryItemController::class, 'store']);
    });
