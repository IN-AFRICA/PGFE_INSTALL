<?php
// À compléter avec les routes RH (personnels, salaires, congés, etc.)

use App\Http\Controllers\Api\Personals\PersonalController;
use App\Http\Controllers\Api\Personals\AcademicPersonalController;
use App\Http\Controllers\Api\Person\PersonSalaireController;
use App\Http\Controllers\Api\Person\PersonCongeController;
use App\Http\Controllers\Api\Person\PersonAffectationController;
use App\Http\Controllers\Api\Person\PersonListeSalariesController;
use App\Http\Controllers\Api\Person\PersonEvaluationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('hr')
    ->name('hr.')
    ->group(function () {
        Route::apiResource('personals', PersonalController::class);
        // Les routes fixes doivent venir AVANT la resource pour éviter
        // que "stats-by-month" soit interprété comme un {academic_personal}
        Route::get('academic-personals/stats-by-month', [AcademicPersonalController::class, 'statsByMonth'])
            ->name('academic-personals.stats-by-month');
        Route::apiResource('academic-personals', AcademicPersonalController::class);
        Route::apiResource('person_salaires', PersonSalaireController::class);
        Route::get('person-salaires/stats-by-gender', [PersonSalaireController::class, 'statsByGender'])
            ->name('person-salaires.stats-by-gender');
        Route::apiResource('person-conges', PersonCongeController::class);
        Route::apiResource('person_affectations', PersonAffectationController::class);
        Route::apiResource('person_liste_salaries', PersonListeSalariesController::class);
        Route::apiResource('person-evaluations', PersonEvaluationController::class);
    });
