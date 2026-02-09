<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SchoolWebController;
use App\Http\Controllers\Admin\ClassroomWebController;
use App\Http\Controllers\Admin\StudentWebController;
use App\Http\Controllers\Admin\PersonnelWebController;
use App\Http\Controllers\Admin\Accounting\AccountingDashboardController;
use App\Http\Controllers\Admin\CountryWebController;
use App\Http\Controllers\Admin\ProvinceWebController;
use App\Http\Controllers\Admin\CommuneWebController;
use App\Http\Controllers\Admin\TerritoryWebController;
use App\Http\Controllers\Admin\TypeWebController;
use App\Http\Controllers\Admin\FiliaireWebController;
use App\Http\Controllers\Admin\AcademicLevelWebController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RoleWebController;
use App\Http\Controllers\Admin\RegistrationWebController;

// Routes web pour la partie administration (protégées par rôle super-admin)

Route::middleware(['web', 'auth', 'role:super-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard principal super admin (backend existant)
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Monitoring et Recherche Globale
        Route::get('/search', [AdminController::class, 'globalSearch'])->name('search');
        Route::get('/sync/monitoring', [AdminController::class, 'syncMonitoring'])->name('sync.monitoring');
        Route::get('/export-roles-pdf', [\App\Http\Controllers\Admin\RoleExportController::class, 'exportPdf'])->name('export-roles-pdf');

        // Gestion des écoles
        Route::resource('schools', SchoolWebController::class)->names('schools');

        // Géographie : pays, provinces, communes, territoires
        Route::resource('countries', CountryWebController::class)->names('countries');
        Route::resource('provinces', ProvinceWebController::class)->names('provinces');
        Route::resource('communes', CommuneWebController::class)->names('communes');
        Route::resource('territories', TerritoryWebController::class)->names('territories');

        // Gestion des classes (restreint aux actions implémentées)
        Route::resource('classrooms', ClassroomWebController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('classrooms');

        // Gestion des élèves (liste + édition / suppression)
        Route::resource('students', StudentWebController::class)
            ->only(['index', 'edit', 'update', 'destroy'])
            ->names('students');

        // Gestion des personnels académiques (liste + édition / suppression)
        Route::resource('personnels', PersonnelWebController::class)
            ->only(['index', 'edit', 'update', 'destroy'])
            ->names('personnels');

        // Filières et types d'établissement
        Route::resource('filiaires', FiliaireWebController::class)->names('filiaires');
        Route::resource('types', TypeWebController::class)->names('types');

        // Niveaux académiques
        Route::resource('academic-levels', AcademicLevelWebController::class)->names('academic-levels');

        // Utilisateurs et inscriptions
        Route::resource('users', UsersController::class)->names('users');
        Route::resource('roles', RoleWebController::class)->names('roles');
        Route::post('roles/assign', [RoleWebController::class, 'assign'])->name('roles.assign');
        Route::post('roles/revoke', [RoleWebController::class, 'revoke'])->name('roles.revoke');
        Route::resource('registrations', RegistrationWebController::class)->names('registrations');

        // Module de comptabilité (routes par entité)
        Route::resource('accounting', \App\Http\Controllers\Admin\Accounting\AccountingWebController::class)->names('accounting');
        
            // Année scolaire
            Route::resource('school-years', \App\Http\Controllers\Admin\SchoolYearWebController::class)->names('school-years');
            Route::get('school-years/{id}/activate', [\App\Http\Controllers\Admin\SchoolYearWebController::class, 'activate'])->name('school-years.activate');
        
            // Semestres
            Route::resource('semesters', \App\Http\Controllers\Admin\SemesterWebController::class)->names('semesters');
        
            // Périodes
            Route::resource('periods', \App\Http\Controllers\Admin\PeriodWebController::class)->names('periods');

            // Mois
            Route::resource('mois', \App\Http\Controllers\Admin\MoisWebController::class)->names('mois');

            // Infrastructure Dashboard
            Route::get('infra/dashboard', [\App\Http\Controllers\Admin\Infra\InfraDashboardWebController::class, 'index'])->name('infra.dashboard');

            // Infrastructures - routes par entité (namespace Admin\Infra)
            Route::resource('infra-categories', \App\Http\Controllers\Admin\Infra\InfraCategoryWebController::class)->names('infra-categories');
            Route::resource('infra-bailleurs', \App\Http\Controllers\Admin\Infra\InfraBailleurWebController::class)->names('infra-bailleurs');
            Route::resource('infra-equipements', \App\Http\Controllers\Admin\Infra\InfraEquipementWebController::class)->names('infra-equipements');
            Route::resource('infra-inventaires', \App\Http\Controllers\Admin\Infra\InfraInventaireWebController::class)->names('infra-inventaires');
            Route::resource('infra-infrastructures', \App\Http\Controllers\Admin\Infra\InfraInfrastructureWebController::class)->names('infra-infrastructures');
            Route::resource('infra-etats', \App\Http\Controllers\Admin\Infra\InfraEtatWebController::class)->names('infra-etats');
            Route::resource('infra-infrastructure-inventaires', \App\Http\Controllers\Admin\Infra\InfraInfrastructureInventaireWebController::class)->names('infra-infrastructure-inventaires');

            // Stock - routes par entité (namespace Admin\Stock)
            Route::resource('stock-articles', \App\Http\Controllers\Admin\Stock\StockArticleWebController::class)->names('stock-articles');
            Route::resource('stock-categories', \App\Http\Controllers\Admin\Stock\StockCategoryWebController::class)->names('stock-categories');
            Route::resource('stock-providers', \App\Http\Controllers\Admin\Stock\StockProviderWebController::class)->names('stock-providers');
            Route::resource('stock-entries', \App\Http\Controllers\Admin\Stock\StockEntryWebController::class)->names('stock-entries');
            Route::resource('stock-exits', \App\Http\Controllers\Admin\Stock\StockExitWebController::class)->names('stock-exits');
            Route::resource('stock-states', \App\Http\Controllers\Admin\Stock\StockStateWebController::class)->names('stock-states');
            Route::resource('stock-inventories', \App\Http\Controllers\Admin\Stock\StockInventoryWebController::class)->names('stock-inventories');
    });
