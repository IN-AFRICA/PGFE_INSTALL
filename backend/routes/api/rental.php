<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Rental\EquipmentController;
use App\Http\Controllers\Api\Rental\ClientController;
use App\Http\Controllers\Api\Rental\RentalContractController;
use App\Http\Controllers\Api\Rental\RentalContractEquipmentController;
use App\Http\Controllers\Api\Rental\PaymentController;
use App\Http\Controllers\Api\Rental\ProjectController;
use App\Http\Controllers\Api\Rental\RentalSessionController;

// This file is included inside the main `v1` prefix group in routes/api.php,
// so we only add the `rental` segment here to get /api/v1/rental/*
Route::middleware('auth:sanctum')->prefix('rental')->group(function () {
    Route::apiResource('equipments', EquipmentController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('contracts', RentalContractController::class);
    Route::apiResource('contract-equipments', RentalContractEquipmentController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('sessions', RentalSessionController::class);
});
