<?php
// À compléter avec les routes de présence (élèves, personnels, visites, etc.)

use App\Http\Controllers\Api\Presences\PresenceController;
use App\Http\Controllers\Api\Person\PersonPresenceController;
use App\Http\Controllers\Api\Visits\VisitController;
use App\Http\Controllers\Api\DisciplinaryActions\IndisciplineCaseController;
use App\Http\Controllers\Api\DisciplinaryActions\AbandonCaseController;
use App\Http\Controllers\Api\Students\StudentExitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('presence')
    ->name('presence.')
    ->group(function () {
        // Présences élèves
        Route::get('presences/export', [PresenceController::class, 'export'])->name('presences.export');
        Route::get('presences/export-pdf', [PresenceController::class, 'exportPdf'])->name('presences.export-pdf');
        Route::get('presences/bulletin', [\App\Http\Controllers\Api\Bulletin\BulletinController::class, 'print'])->name('presences.bulletin.print');
        Route::get('presences/bulletin/json', [\App\Http\Controllers\Api\Bulletin\BulletinController::class, 'show'])->name('presences.bulletin.json');
        // Alias plus simple pour le front
        Route::get('bulletin/json', [\App\Http\Controllers\Api\Bulletin\BulletinController::class, 'show'])->name('bulletin.json');
        Route::get('bulletin/print', [\App\Http\Controllers\Api\Bulletin\BulletinController::class, 'print'])->name('bulletin.print');
        Route::put('presences/students/{studentId}', [PresenceController::class, 'updateStudentPresence'])->whereNumber('studentId')->name('presences.update-student');
        Route::patch('presences/students/{studentId}', [PresenceController::class, 'updateStudentPresence'])->whereNumber('studentId');
        Route::put('presences', [PresenceController::class, 'updateByBody'])->name('presences.update.body');
        Route::patch('presences', [PresenceController::class, 'updateByBody']);
        Route::post('presences/classrooms/{classroomId}/bulk', [PresenceController::class, 'bulkStorePresences'])->whereNumber('classroomId')->name('presences.bulk');
        Route::apiResource('presences', PresenceController::class)->names('presences');
        // Présences personnels - routes spécifiques AVANT apiResource
        Route::post('person-presences/initialize', [PersonPresenceController::class, 'initializeAll'])->name('person-presences.initialize');
        Route::get('person-presences/export', [PersonPresenceController::class, 'export'])->name('person-presences.export');
        Route::get('person-presences/export-pdf', [PersonPresenceController::class, 'exportPdf'])->name('person-presences.export-pdf');
        Route::put('person-presences/personnels/{personnelId}', [PersonPresenceController::class, 'updateByPersonnel'])->whereNumber('personnelId')->name('person-presences.update-personnel');
        Route::patch('person-presences/personnels/{personnelId}', [PersonPresenceController::class, 'updateByPersonnel'])->whereNumber('personnelId');
        Route::put('person-presences', [PersonPresenceController::class, 'updateByBody'])->name('person-presences.update.body');
        Route::patch('person-presences', [PersonPresenceController::class, 'updateByBody']);
        Route::post('person-presences/bulk', [PersonPresenceController::class, 'bulkStore'])->name('person-presences.bulk-store');
        Route::apiResource('person-presences', PersonPresenceController::class)->names('person-presences');
        // Visites
        Route::apiResource('visits', VisitController::class)->names('visits');
        // Indiscipline
        Route::apiResource('indiscipline-cases', IndisciplineCaseController::class)->names('indiscipline-cases');
        // Abandon
        Route::apiResource('abandon-cases', AbandonCaseController::class)->names('abandon-cases');
        // Sorties
        Route::apiResource('student-exits', StudentExitController::class)->names('student-exits');
    });
