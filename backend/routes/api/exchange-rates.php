<?php

use App\Http\Controllers\Api\ExchangeRates\ExchangeRateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('exchange-rates', ExchangeRateController::class);
});
