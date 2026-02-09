<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Mois\MoisController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('mois', MoisController::class);
});
