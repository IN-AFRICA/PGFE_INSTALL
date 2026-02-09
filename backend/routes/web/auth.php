<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Auth\LoginController;

// Routes d'authentification web (login/logout) pour l'admin

Route::middleware('web')
    ->group(function () {
        // Login web principal de PGFE (route par défaut utilisée par le middleware auth)
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.submit');

        // Alias sous /auth pour rester cohérent avec le prefix existant
        Route::prefix('auth')
            ->name('web.auth.')
            ->group(function () {
                Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
                Route::post('login', [LoginController::class, 'login'])->name('login.submit');
                Route::post('logout', [LoginController::class, 'logout'])->name('logout');
            });
    });

