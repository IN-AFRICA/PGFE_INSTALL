<?php
// Infrastructures routes

use App\Http\Controllers\InfraTypeController;
use App\Http\Controllers\InfraEquipmentController;
use App\Http\Controllers\InfraStateController;
use App\Http\Controllers\InfraInventoryController;
use App\Http\Controllers\InfraInventoryEquipmentController;
use App\Http\Controllers\InfraInventoryRealStateController;
use App\Http\Controllers\Api\Infrastructure\InfraInfrastructureInventaireController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('infrastructures')
    ->name('infra.')
    ->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\Infrastructure\InfraDashboardController::class, 'index']);
        Route::apiResource('types', InfraTypeController::class);
        Route::apiResource('equipments', InfraEquipmentController::class);
        Route::apiResource('states', InfraStateController::class);
        Route::apiResource('inventories', InfraInventoryController::class);
        Route::post('inventories/{inventory}/equipments', [InfraInventoryEquipmentController::class, 'store']);
        Route::post('inventories/{inventory}/real-states', [InfraInventoryRealStateController::class, 'store']);
        Route::apiResource('infrastructure-inventaires', InfraInfrastructureInventaireController::class);
    });
