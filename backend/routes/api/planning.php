<?php

use App\Http\Controllers\Api\Planning\PlanningFileController;
use App\Http\Controllers\Api\Planning\SchoolworkPlanningController;
use App\Http\Controllers\Api\Planning\WorkDepositController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('planning')
    ->name('planning.')
    ->group(function () {
        // Fichiers de planification
        Route::get('files', [PlanningFileController::class, 'index'])->name('files.index');
        Route::post('files', [PlanningFileController::class, 'store'])->name('files.store');
        Route::delete('files/{planningFile}', [PlanningFileController::class, 'destroy'])->whereNumber('planningFile')->name('files.destroy');

        // Planifications de travaux
        Route::apiResource('schoolworks', SchoolworkPlanningController::class)->names('schoolworks');

        // Dépôts de travaux
        Route::get('deposits', [WorkDepositController::class, 'index'])->name('deposits.index');
        Route::post('deposits', [WorkDepositController::class, 'store'])->name('deposits.store');
        Route::delete('deposits/{workDeposit}', [WorkDepositController::class, 'destroy'])->whereNumber('workDeposit')->name('deposits.destroy');
    });
