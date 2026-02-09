<?php

use App\Http\Controllers\Api\Schedules\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('schedules')
    ->name('schedules.')
    ->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/', [ScheduleController::class, 'store'])->name('store');
        Route::get('/available-teachers', [ScheduleController::class, 'availableTeachers'])->name('available-teachers');
    });
