<?php
// Admin routes

use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard & Monitoring
    Route::get('dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('search', [SuperAdminDashboardController::class, 'globalSearch'])->name('search');
    Route::get('sync/monitoring', [SuperAdminDashboardController::class, 'syncMonitoring'])->name('sync.monitoring');

    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::apiResource('users', AdminUserController::class)->names('users');
    Route::patch('users/{user}/school', [AdminUserController::class, 'assignSchool'])->name('users.assignSchool');
});
