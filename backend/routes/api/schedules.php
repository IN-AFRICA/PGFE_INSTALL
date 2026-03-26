<?php

use App\Http\Controllers\Api\Schedules\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('schedules')
    ->name('schedules.')
    ->group(function () {
        // Lecture: admin-ecole/super-admin (tout), enseignant (son propre horaire)
        Route::get('/', [ScheduleController::class, 'index'])
            ->middleware('role:admin|admin-ecole|super-admin|enseignant')
            ->name('index');

        // Opérations: réservées aux admins
        Route::post('/', [ScheduleController::class, 'store'])
            ->middleware('role:admin|admin-ecole|super-admin')
            ->name('store');
        Route::get('/available-teachers', [ScheduleController::class, 'availableTeachers'])
            ->middleware('role:admin|admin-ecole|super-admin')
            ->name('available-teachers');
        Route::put('/{id}', [ScheduleController::class, 'update'])
            ->middleware('role:admin|admin-ecole|super-admin')
            ->name('update');
        Route::patch('/{id}', [ScheduleController::class, 'update'])
            ->middleware('role:admin|admin-ecole|super-admin');
        Route::delete('/{id}', [ScheduleController::class, 'destroy'])
            ->middleware('role:admin|admin-ecole|super-admin')
            ->name('destroy');
    });
