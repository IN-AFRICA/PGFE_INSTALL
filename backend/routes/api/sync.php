<?php

use Illuminate\Support\Facades\Route;
use App\Sync\SyncController;

Route::post('/sync', [SyncController::class, 'sync']);
