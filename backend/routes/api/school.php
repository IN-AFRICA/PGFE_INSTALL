<?php
// School config routes

use App\Http\Controllers\Api\Schools\SchoolController;
use App\Http\Controllers\Api\Classroom\ClassroomController;
use App\Http\Controllers\Api\SchooYears\SchoolYearController;
use App\Http\Controllers\Api\Filiaires\ListsFiliaireController;
use App\Http\Controllers\Api\Filiaires\StoreFiliaireController;
use App\Http\Controllers\Api\Filiaires\UpdateFiliaireController;
use App\Http\Controllers\Api\Students\StudentRegistrationController;
use App\Http\Controllers\Api\Semester\SemesterController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('school')
    ->name('school.')
    ->group(function () {
        Route::apiResource('schools', SchoolController::class);
        Route::apiResource('classrooms', ClassroomController::class);
        Route::apiResource('courses', \App\Http\Controllers\Api\Courses\CourseController::class);
        // Semesters
        Route::apiResource('semesters', SemesterController::class);
        Route::prefix('years')->group(function () {
            Route::get('/', [SchoolYearController::class, 'index']);
            Route::get('active', [SchoolYearController::class, 'active']);
            Route::post('/', [SchoolYearController::class, 'store']);
            Route::put('{schoolYear}/activate', [SchoolYearController::class, 'activate']);
        });
        // Filières (optionnel, si rattaché à l'école)
        Route::prefix('filiaires')->group(function () {
            Route::get('export', [ListsFiliaireController::class, 'export']);
            Route::get('lists', ListsFiliaireController::class);
            Route::get('{filiaire}/show', [ListsFiliaireController::class, 'show']);
            Route::post('store', StoreFiliaireController::class);
            Route::put('{filiaire}/update', UpdateFiliaireController::class);
            Route::delete('{filiaire}/delete', [ListsFiliaireController::class, 'delete']);
        });
        Route::get('classrooms/my-school', [StudentRegistrationController::class, 'classroomsForMySchool'])->name('classrooms.my-school');
    });
