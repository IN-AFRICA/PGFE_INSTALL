<?php

use App\Http\Controllers\Api\Currencies\CurrencyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('currencies', CurrencyController::class);
});
