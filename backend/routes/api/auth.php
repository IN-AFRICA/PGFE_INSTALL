<?php
// Auth routes

use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Auth\AuthuserinfoController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\UpdateAuthenticateUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', AuthenticationController::class)->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthuserinfoController::class, 'index'])->name('me');
        Route::post('logout', [AuthenticationController::class, 'logout'])->name('logout');
        Route::put('profile', UpdateAuthenticateUserController::class)->name('profile');
        Route::put('password', [UpdateAuthenticateUserController::class, 'updatePassword'])->name('password');
        Route::delete('account', [UpdateAuthenticateUserController::class, 'deleteAccount'])->name('delete');
    });
});
