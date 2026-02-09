<?php
// Geo routes

use App\Http\Controllers\Api\Country\CountryController;
use App\Http\Controllers\Api\Country\ProvinceController;
use App\Http\Controllers\Api\Country\TerritoryController;
use App\Http\Controllers\Api\Country\CommuneController;
use App\Http\Controllers\Api\Type\TypeController;
use App\Http\Controllers\Api\Fonctions\FonctionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('geo')
    ->name('geo.')
    ->group(function () {
        Route::apiResource('countries', CountryController::class);
        Route::apiResource('provinces', ProvinceController::class);
        Route::apiResource('territories', TerritoryController::class);
        Route::apiResource('communes', CommuneController::class);
        Route::apiResource('types', TypeController::class);
        Route::apiResource('fonctions', FonctionController::class);
    });
