<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Insertion\CandidateController;
use App\Http\Controllers\Insertion\CompanyController;
use App\Http\Controllers\Insertion\JobOfferController;
use App\Http\Controllers\Insertion\ApplicationController;
use App\Http\Controllers\Insertion\ApplicationQueryController;
use App\Http\Controllers\Academic\BulletinPrintController;
use App\Http\Controllers\Admin\RoleExportController;


Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('admin/export-roles-pdf', [RoleExportController::class, 'exportPdf']);
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/admin.php';
    require __DIR__.'/api/geo.php';
    require __DIR__.'/api/school.php';
    require __DIR__.'/api/academic.php';
    require __DIR__.'/api/students.php';
    require __DIR__.'/api/accounting.php';
    require __DIR__.'/api/stock.php';
    require __DIR__.'/api/infrastructures.php';
    require __DIR__.'/api/hr.php';
    require __DIR__.'/api/currencies.php';
    require __DIR__.'/api/exchange-rates.php';
    require __DIR__.'/api/mois.php';
    require __DIR__.'/api/presence.php';
    require __DIR__.'/api/documents.php';
    require __DIR__.'/api/parents.php';
    require __DIR__.'/api/insertion-professionnelle.php';
    require __DIR__.'/api/rental.php';
    require __DIR__.'/api/conduite.php';
    require __DIR__.'/api/schedules.php';
    require __DIR__.'/api/planning.php';
    require __DIR__.'/api/activities.php';
});

Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function () {
    // Endpoints nécessitant une authentification par token (Sanctum)
    require __DIR__.'/api/sync.php';

    Route::get('bulletins/print/class/{classroom}', [BulletinPrintController::class, 'printByClass']);
    Route::get('bulletins/print/student/{student}', [BulletinPrintController::class, 'printByStudent']);
});