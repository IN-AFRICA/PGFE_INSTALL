<?php
declare(strict_types=1);



use App\Http\Controllers\Api\Students\StudentController;
use App\Http\Controllers\Api\Students\StudentDashboardController;
use App\Http\Controllers\Api\Students\StudentRegistrationController;
use App\Http\Controllers\Api\Repechage\ImportRepechageController;
use App\Http\Controllers\Api\ValidationAureat\ImportController;
use App\Http\Controllers\Api\ValidationAureat\ListValidationAureatController;
use App\Http\Controllers\Api\ValidationAureat\ExportController;
use App\Http\Controllers\Api\ChainageController;
use App\Http\Controllers\Api\Repechage\RepechageController;
use App\Http\Controllers\Api\Formation\FormationContinueController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('students')
    ->name('students.')
    ->group(function () {
        Route::get('dashboard', [StudentDashboardController::class, 'index']);
        // Routes repêchage avant les routes dynamiques {student}
        Route::post('repechage/import', [ImportRepechageController::class, 'import'])->name('repechage.import');
        Route::get('repechage', [RepechageController::class, 'index'])->name('repechage.index');
        // Export repêchage (Excel)
        Route::get('repechage/export', [RepechageController::class, 'export'])->name('repechage.export');
        Route::get('repechage/export-pdf', [RepechageController::class, 'exportPdf'])->name('repechage.export-pdf');
        Route::post('validation-aureat/import', [ImportController::class, 'import'])->name('validation-aureat.import');
        Route::get('validation-aureat', [ListValidationAureatController::class, 'index'])->name('validation-aureat.index');
        Route::get('validation-aureat/export', [ExportController::class, 'export'])->name('validation-aureat.export');
        Route::get('validation-aureat/export-all', [ExportController::class, 'exportAll'])->name('validation-aureat.export-all');
        // (Si besoin d'un export PDF, ajouter ici la route PDF)
        Route::get('cycles/{cycle}/academic-levels', [ChainageController::class, 'academicLevelsByCycle']);
        Route::get('academic-levels/{level}/filiaires', [ChainageController::class, 'filiairesByAcademicLevel']);
        Route::get('filiaires/{filiaire}/classrooms', [ChainageController::class, 'classroomsByFiliaire']);
        Route::get('courses/with-academic-personals', [\App\Http\Controllers\Api\Courses\CourseController::class, 'getCoursesWithAcademicPersonals']);
        Route::apiResource('formation-continues', FormationContinueController::class)->names('formation-continues');

        // Routes principales étudiants (après les chemins fixes comme 'repechage')
        Route::apiResource('/', StudentController::class)->parameters(['' => 'student']);
        Route::get('{student}/classrooms', [StudentRegistrationController::class, 'classroomsForStudent']);
        Route::get('{student}/registrations', [StudentRegistrationController::class, 'showForStudent']);
            Route::get('registrations/export', [StudentRegistrationController::class, 'export']);
            Route::get('registrations/export-pdf', [StudentRegistrationController::class, 'exportPdf']);
            Route::get('registrations/list', [StudentRegistrationController::class, 'list']);
            Route::put('registrations/{registration}', [StudentRegistrationController::class, 'update']);

        // (Route dashboard étudiant supprimée)
        // Route POST pour inscrire un étudiant existant
        Route::post('{student}/registrations', [StudentRegistrationController::class, 'store'])->name('registrations.store');
    });
