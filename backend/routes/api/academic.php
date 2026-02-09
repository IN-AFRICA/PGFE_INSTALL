<?php
// Academic routes

use App\Http\Controllers\Api\AcademicLevels\AcademicLevelController;
use App\Http\Controllers\Api\Cycles\CycleController;
use App\Http\Controllers\Api\Deliberation\DeliberationController;
use App\Http\Controllers\Api\Deliberation\GeneralDeliberationController;
use App\Http\Controllers\Api\FicheCotation\FicheCotationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('academic')
    ->name('academic.')
    ->group(function () {
        Route::apiResource('levels', AcademicLevelController::class);
        Route::apiResource('cycles', CycleController::class);
        Route::get('cycles/export', [CycleController::class, 'export'])->name('cycles.export');
        // Délibérations
        Route::prefix('deliberations')->name('deliberations.')->group(function () {
            Route::post('initialize', [DeliberationController::class, 'initialize'])->name('initialize');
            Route::apiResource('/', DeliberationController::class)
                ->parameters(['' => 'deliberation'])
                ->names('main');
            Route::prefix('general')->name('general.')->group(function () {
            Route::get('students/{student}', [GeneralDeliberationController::class, 'showForStudent'])->whereNumber('student')->name('students.show');
            Route::post('students/{student}/validate', [GeneralDeliberationController::class, 'validateForStudent'])->whereNumber('student')->name('students.validate');
            });
        });
        // Fiche de cotation
        Route::prefix('fiche-cotations')->name('fiche-cotations.')->group(function () {
            Route::get('/', [FicheCotationController::class, 'index'])->name('index');
            Route::post('export', [FicheCotationController::class, 'export'])->name('export');
            Route::get('export/template', [FicheCotationController::class, 'exportTemplate'])->name('export/template');
            Route::post('import', [FicheCotationController::class, 'import'])->name('import');
            Route::post('initialize', [FicheCotationController::class, 'initialize'])->name('initialize');
            Route::apiResource('/', FicheCotationController::class)->names('main');
        });
    });
