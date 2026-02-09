<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Insertion\CandidateController;
use App\Http\Controllers\Insertion\CompanyController;
use App\Http\Controllers\Insertion\JobOfferController;
use App\Http\Controllers\Insertion\ApplicationController;
use App\Http\Controllers\Insertion\ApplicationQueryController;

// Bounded Context : Insertion Professionnelle
Route::middleware('auth:sanctum')->prefix('insertion')->group(function () {
    Route::post('candidates', [CandidateController::class, 'register']);
    Route::post('companies', [CompanyController::class, 'store']);
    Route::post('job-offers', [JobOfferController::class, 'create']);
    Route::post('job-offers/{id}/close', [JobOfferController::class, 'close']);
    Route::post('applications', [ApplicationController::class, 'apply']);
    Route::post('applications/{id}/accept', [ApplicationController::class, 'accept']);
    Route::post('applications/{id}/reject', [ApplicationController::class, 'reject']);
    Route::get('candidates/{id}/applications', [ApplicationQueryController::class, 'candidateApplications']);
});
