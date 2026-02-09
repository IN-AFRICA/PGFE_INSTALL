<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Parents\ParentController;

Route::apiResource('parents', ParentController::class);