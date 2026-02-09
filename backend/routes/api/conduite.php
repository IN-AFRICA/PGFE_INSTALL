<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Conduite\ConduiteController;
use App\Http\Controllers\Api\Conduite\ConduiteSemesterController;
use App\Http\Controllers\Api\DisciplinaryActions\ConduiteGradeController;

Route::middleware('auth:sanctum')
    ->prefix('conduite')
    ->name('conduite.')
    ->group(function () {
        Route::apiResource('notes', ConduiteGradeController::class)->names('notes');
        Route::apiResource('semesters', ConduiteSemesterController::class)->names('semesters');
        Route::apiResource('/', ConduiteController::class)->names('main');
    });
