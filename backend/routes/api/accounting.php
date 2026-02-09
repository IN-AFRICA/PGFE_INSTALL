<?php
use App\Http\Controllers\Api\Accountings\Accounts\AccountController;
use App\Http\Controllers\Api\Accountings\Accounts\JournalController;
use App\Http\Controllers\Api\Accountings\Accounts\AccountNumberController;
use App\Http\Controllers\Api\Accountings\Types\AccountTypeController;
use App\Http\Controllers\Api\Fees\FeeController;
use App\Http\Controllers\Api\FeeTypes\FeeTypeController as FeeTypeApiController;
use App\Http\Controllers\Api\Payments\PaymentController;
use App\Http\Controllers\Api\Payments\PaymentMethodController;
use App\Http\Controllers\Api\Payments\PaymentMotifController;
use App\Http\Controllers\Api\Accountings\Accounts\DashboardController as AccountingDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('accounting')
    ->name('accounting.')
    ->group(function () {
        Route::get('dashboard', [AccountingDashboardController::class, 'index']);
        Route::apiResource('accounts', AccountController::class);
        // Exports des journaux (déclarés AVANT la resource pour éviter que
        // "export" ou "export-pdf" soient interprétés comme {journal})
        Route::get('journals/export', [JournalController::class, 'exportExcel'])
            ->name('journals.export');
        Route::get('journals/export-pdf', [JournalController::class, 'exportPdf'])
            ->name('journals.export-pdf');

        Route::apiResource('journals', JournalController::class);

        Route::apiResource('account-numbers', AccountNumberController::class);
        Route::apiResource('account-types', AccountTypeController::class);
        // Ancienne route d'export Excel (conservée pour compatibilité)
        Route::get('journal/export-excel', [JournalController::class, 'exportExcel']);

        // Export Excel
        Route::get('fees/export-excel', [\App\Http\Controllers\Api\Fees\FeeController::class, 'exportExcel']);
        Route::get('exchange-rates/export-excel', [\App\Http\Controllers\Api\ExchangeRates\ExchangeRateController::class, 'exportExcel']);
        Route::get('payments/motifs/export-excel', [\App\Http\Controllers\Api\Payments\PaymentMotifController::class, 'exportExcel']);

        // Additional Accountings/Accounts controllers
        Route::get('account-plans/export', [\App\Http\Controllers\Api\Accountings\Accounts\AccountPlanController::class, 'export'])->name('account-plans.export');
        Route::get('account-plans/export-pdf', [\App\Http\Controllers\Api\Accountings\Accounts\AccountPlanController::class, 'exportPdf'])->name('account-plans.export-pdf');
        Route::apiResource('account-plans', \App\Http\Controllers\Api\Accountings\Accounts\AccountPlanController::class);
        Route::apiResource('analytic-plans', \App\Http\Controllers\Api\Accountings\Accounts\AnalyticPlanController::class);
        Route::apiResource('budget-comptabilities', \App\Http\Controllers\Api\Accountings\Accounts\BudgetComptabilityController::class);
        Route::apiResource('category-comptabilities', \App\Http\Controllers\Api\Accountings\Accounts\CategoryComptabilityController::class);
        Route::apiResource('class-comptabilities', \App\Http\Controllers\Api\Accountings\Accounts\ClassComptabilityController::class);
        Route::apiResource('exercices', \App\Http\Controllers\Api\Accountings\Accounts\ExerciceController::class);
        Route::apiResource('immo-accounts', \App\Http\Controllers\Api\Accountings\Accounts\ImmoAccountController::class);
        Route::apiResource('immo-ammortissemen-comptabilities', \App\Http\Controllers\Api\Accountings\Accounts\ImmoAmmortissemenComptabilityController::class);
        Route::apiResource('immo-sub-accounts', \App\Http\Controllers\Api\Accountings\Accounts\ImmoSubAccountController::class);
        Route::apiResource('input-accounts', \App\Http\Controllers\Api\Accountings\Accounts\InputAccountController::class);
        Route::apiResource('output-accounts', \App\Http\Controllers\Api\Accountings\Accounts\OutputAccountController::class);
        Route::get('sub-account-plans/export', [\App\Http\Controllers\Api\Accountings\Accounts\SubAccountPlanController::class, 'export'])->name('sub-account-plans.export');
        Route::get('sub-account-plans/export-pdf', [\App\Http\Controllers\Api\Accountings\Accounts\SubAccountPlanController::class, 'exportPdf'])->name('sub-account-plans.export-pdf');
        Route::apiResource('sub-account-plans', \App\Http\Controllers\Api\Accountings\Accounts\SubAccountPlanController::class);
        Route::apiResource('a-new-comptabilities', \App\Http\Controllers\Api\Accountings\Accounts\ANewComptabilityController::class);

        // Fees
        // Liste des types de frais (évite que "types" soit interprété comme un ID de Fee)
        Route::get('fees/types', [FeeTypeApiController::class, 'index']);
        Route::apiResource('fees', FeeController::class);
        Route::prefix('payments')->group(function () {
            Route::apiResource('methods', PaymentMethodController::class);
            Route::apiResource('motifs', PaymentMotifController::class);
            Route::apiResource('transactions', PaymentController::class);
        });
    });
