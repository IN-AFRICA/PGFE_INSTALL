<?php
// À compléter avec les routes de gestion documentaire

use App\Http\Controllers\Api\Documents\DocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('documents')
    ->name('documents.')
    ->group(function () {
        Route::apiResource('/', DocumentController::class);
    });
