<?php
// Stock routes

use App\Http\Controllers\StockArticleController;
use App\Http\Controllers\StockProviderController;
use App\Http\Controllers\StockCategoryController;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\StockExitController;
use App\Http\Controllers\StockStateController;
use App\Http\Controllers\StockInventoryController;
use App\Http\Controllers\Stock\StockOperationController;
use App\Http\Controllers\Stock\DashboardStockController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('stock')
    ->name('stock.')
    ->group(function () {
        Route::apiResource('articles', StockArticleController::class);
        Route::apiResource('categories', StockCategoryController::class);
        Route::apiResource('providers', StockProviderController::class);
        Route::apiResource('entries', StockEntryController::class);
        Route::apiResource('exits', StockExitController::class);
        Route::get('articles/{article}/state', [StockStateController::class, 'show']);
        Route::apiResource('inventories', StockInventoryController::class);
        Route::apiResource('operations', StockOperationController::class); // autorise toutes les méthodes REST
        Route::get('dashboard', [DashboardStockController::class, 'index']);
    });
