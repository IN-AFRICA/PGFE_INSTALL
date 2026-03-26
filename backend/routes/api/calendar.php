<?php

use App\Http\Controllers\Api\Calendar\CalendarWeeksController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('calendar')
    ->name('calendar.')
    ->group(function () {
        // Semaines ISO d'un mois (basées sur school_year.name)
        Route::get('/weeks', CalendarWeeksController::class)
            ->middleware('role:admin|admin-ecole|super-admin|enseignant')
            ->name('weeks');
    });
