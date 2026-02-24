<?php

use App\Http\Controllers\Api\Activities\SchoolActivityController;
use App\Http\Controllers\Api\Activities\StudentActivityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('activities')
    ->name('activities.')
    ->group(function () {
        // Activités scolaires
        Route::apiResource('school', SchoolActivityController::class)->names('school');

        // Participations / activités par classe
        Route::get('student-activities', [StudentActivityController::class, 'index'])->name('student-activities.index');
        Route::post('student-activities', [StudentActivityController::class, 'store'])->name('student-activities.store');
        Route::delete('student-activities/{studentActivity}', [StudentActivityController::class, 'destroy'])->whereNumber('studentActivity')->name('student-activities.destroy');
    });
