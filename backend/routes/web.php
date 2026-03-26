<?php

declare(strict_types=1);

// Regroupement des routes web en fichiers séparés

require __DIR__.'/web/auth.php';
require __DIR__.'/web/admin.php';
require __DIR__.'/../routes/sync.php';

// Route::get('/{any}', function () {
//    return view('app');
// })->where('any', '^(?!admin|api).*$');
Route::get('/{any}', function () {
    return response()->file(public_path('frontend/index.html'));
})->where('any', '^(?!admin|api).*$');
