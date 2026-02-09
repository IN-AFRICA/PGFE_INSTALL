{# <?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AcademicLevelWebController;
// (Dashboard backend supprimé)
use App\Http\Controllers\Api\Accountings\Accounts\DashboardController as AccountingDashboardController;
use App\Http\Controllers\Admin\SelectionController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
});

// Routes pour le backend Laravel - Accessibles uniquement aux super-admin
Route::middleware('super-admin')->prefix('admin')->group(function () {
    // (Route dashboard admin supprimée)

    // Page listant les actions disponibles et permettant d'exécuter des requêtes simples
    Route::get('/routes/actions', [App\Http\Controllers\Admin\ActionsController::class, 'index'])
        ->name('admin.routes.actions');

    // Module Academic Levels (interface web) — nécessite une école sélectionnée
    Route::resource('academic-levels', AcademicLevelWebController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('require.school')
        ->names('admin.academic-levels');

    // Routes placeholders pour éviter erreurs et permettre future implémentation
    if (class_exists(App\Http\Controllers\Admin\RolesController::class)) {
        Route::get('/roles', [App\Http\Controllers\Admin\RolesController::class, 'index'])->name('admin.roles.index');
    }
    if (class_exists(App\Http\Controllers\Admin\PermissionsController::class)) {
        Route::get('/permissions', [App\Http\Controllers\Admin\PermissionsController::class, 'index'])->name('admin.permissions.index');
    }
    if (class_exists(App\Http\Controllers\Admin\TranslationsController::class)) {
        Route::get('/translations', [App\Http\Controllers\Admin\TranslationsController::class, 'index'])->name('admin.translations.index');
    }

    // AJAX route to get filtered student statistics
    // (Route dashboard stats supprimée)

    // Routes web admin pour écoles (pas de restriction)
    Route::get('/schools', [App\Http\Controllers\Admin\SchoolWebController::class, 'index'])->name('admin.schools.index');
    Route::get('/schools/create', [App\Http\Controllers\Admin\SchoolWebController::class, 'create'])->name('admin.schools.create');
    Route::post('/schools', [App\Http\Controllers\Admin\SchoolWebController::class, 'store'])->name('admin.schools.store');
    Route::get('/schools/{school}/edit', [App\Http\Controllers\Admin\SchoolWebController::class, 'edit'])->name('admin.schools.edit');
    Route::put('/schools/{school}', [App\Http\Controllers\Admin\SchoolWebController::class, 'update'])->name('admin.schools.update');
    Route::delete('/schools/{school}', [App\Http\Controllers\Admin\SchoolWebController::class, 'destroy'])->name('admin.schools.destroy');

    // Classes — nécessite une école sélectionnée
    Route::get('/classrooms', [App\Http\Controllers\Admin\ClassroomWebController::class, 'index'])->middleware('require.school')->name('admin.classrooms.index');
    Route::get('/classrooms/create', [App\Http\Controllers\Admin\ClassroomWebController::class, 'create'])->middleware('require.school')->name('admin.classrooms.create');
    Route::post('/classrooms', [App\Http\Controllers\Admin\ClassroomWebController::class, 'store'])->middleware('require.school')->name('admin.classrooms.store');
    Route::get('/classrooms/{classroom}/edit', [App\Http\Controllers\Admin\ClassroomWebController::class, 'edit'])->middleware('require.school')->name('admin.classrooms.edit');
    Route::put('/classrooms/{classroom}', [App\Http\Controllers\Admin\ClassroomWebController::class, 'update'])->middleware('require.school')->name('admin.classrooms.update');
    Route::delete('/classrooms/{classroom}', [App\Http\Controllers\Admin\ClassroomWebController::class, 'destroy'])->middleware('require.school')->name('admin.classrooms.destroy');

    // Élèves — nécessite une école sélectionnée
    Route::get('/students', [App\Http\Controllers\Admin\StudentWebController::class, 'index'])->middleware('require.school')->name('admin.students.index');
    Route::get('/students/{student}/edit', [App\Http\Controllers\Admin\StudentWebController::class, 'edit'])->middleware('require.school')->name('admin.students.edit');
    Route::put('/students/{student}', [App\Http\Controllers\Admin\StudentWebController::class, 'update'])->middleware('require.school')->name('admin.students.update');
    Route::delete('/students/{student}', [App\Http\Controllers\Admin\StudentWebController::class, 'destroy'])->middleware('require.school')->name('admin.students.destroy');

    // Personnels (optionnel) — si vous souhaitez restreindre aussi: décommentez
    // Route::get('/personnels', [App\Http\Controllers\Admin\PersonnelWebController::class, 'index'])->middleware('require.school')->name('admin.personnels.index');
    Route::get('/personnels', [App\Http\Controllers\Admin\PersonnelWebController::class, 'index'])->name('admin.personnels.index');

    // Filières — nécessite une école sélectionnée
    Route::get('/filiaires', [App\Http\Controllers\Admin\FiliaireWebController::class, 'index'])->middleware('require.school')->name('admin.filiaires.index');
    Route::get('/filiaires/create', [App\Http\Controllers\Admin\FiliaireWebController::class, 'create'])->middleware('require.school')->name('admin.filiaires.create');
    Route::post('/filiaires', [App\Http\Controllers\Admin\FiliaireWebController::class, 'store'])->middleware('require.school')->name('admin.filiaires.store');
    Route::get('/filiaires/{filiaire}/edit', [App\Http\Controllers\Admin\FiliaireWebController::class, 'edit'])->middleware('require.school')->name('admin.filiaires.edit');
    Route::put('/filiaires/{filiaire}', [App\Http\Controllers\Admin\FiliaireWebController::class, 'update'])->middleware('require.school')->name('admin.filiaires.update');
    Route::delete('/filiaires/{filiaire}', [App\Http\Controllers\Admin\FiliaireWebController::class, 'destroy'])->middleware('require.school')->name('admin.filiaires.destroy');

    // Inscriptions — nécessite une école sélectionnée
    Route::get('/registrations', [App\Http\Controllers\Admin\RegistrationWebController::class, 'index'])->middleware('require.school')->name('admin.registrations.index');
    Route::get('/registrations/create', [App\Http\Controllers\Admin\RegistrationWebController::class, 'create'])->middleware('require.school')->name('admin.registrations.create');
    Route::post('/registrations', [App\Http\Controllers\Admin\RegistrationWebController::class, 'store'])->middleware('require.school')->name('admin.registrations.store');
    Route::get('/registrations/{registration}/edit', [App\Http\Controllers\Admin\RegistrationWebController::class, 'edit'])->middleware('require.school')->name('admin.registrations.edit');
    Route::put('/registrations/{registration}', [App\Http\Controllers\Admin\RegistrationWebController::class, 'update'])->middleware('require.school')->name('admin.registrations.update');
    Route::delete('/registrations/{registration}', [App\Http\Controllers\Admin\RegistrationWebController::class, 'destroy'])->middleware('require.school')->name('admin.registrations.destroy');

    // Types (laissez libre si global)
    Route::get('/types', [App\Http\Controllers\Admin\TypeWebController::class, 'index'])->name('admin.types.index');
    Route::get('/types/create', [App\Http\Controllers\Admin\TypeWebController::class, 'create'])->name('admin.types.create');
    Route::post('/types', [App\Http\Controllers\Admin\TypeWebController::class, 'store'])->name('admin.types.store');
    Route::get('/types/{type}/edit', [App\Http\Controllers\Admin\TypeWebController::class, 'edit'])->name('admin.types.edit');
    Route::put('/types/{type}', [App\Http\Controllers\Admin\TypeWebController::class, 'update'])->name('admin.types.update');
    Route::delete('/types/{type}', [App\Http\Controllers\Admin\TypeWebController::class, 'destroy'])->name('admin.types.destroy');

    // Utilisateurs (ajout create/store)
    Route::get('/users', [App\Http\Controllers\Admin\UsersController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UsersController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UsersController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UsersController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UsersController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UsersController::class, 'destroy'])->name('admin.users.destroy');

    // Sélection de classe (session)
    Route::post('/selections/classroom', [SelectionController::class, 'setClassroom'])->name('admin.selections.classroom.set');
    Route::delete('/selections/classroom', [SelectionController::class, 'clearClassroom'])->name('admin.selections.classroom.clear');

    // Pays (global, sans require.school)
    Route::get('/countries', [App\Http\Controllers\Admin\CountryWebController::class, 'index'])->name('admin.countries.index');
    Route::get('/countries/create', [App\Http\Controllers\Admin\CountryWebController::class, 'create'])->name('admin.countries.create');
    Route::post('/countries', [App\Http\Controllers\Admin\CountryWebController::class, 'store'])->name('admin.countries.store');
    Route::get('/countries/{country}/edit', [App\Http\Controllers\Admin\CountryWebController::class, 'edit'])->name('admin.countries.edit');
    Route::put('/countries/{country}', [App\Http\Controllers\Admin\CountryWebController::class, 'update'])->name('admin.countries.update');
    Route::delete('/countries/{country}', [App\Http\Controllers\Admin\CountryWebController::class, 'destroy'])->name('admin.countries.destroy');

    // Provinces (global, sans require.school)
    Route::get('/provinces', [App\Http\Controllers\Admin\ProvinceWebController::class, 'index'])->name('admin.provinces.index');
    Route::get('/provinces/create', [App\Http\Controllers\Admin\ProvinceWebController::class, 'create'])->name('admin.provinces.create');
    Route::post('/provinces', [App\Http\Controllers\Admin\ProvinceWebController::class, 'store'])->name('admin.provinces.store');
    Route::get('/provinces/{province}/edit', [App\Http\Controllers\Admin\ProvinceWebController::class, 'edit'])->name('admin.provinces.edit');
    Route::put('/provinces/{province}', [App\Http\Controllers\Admin\ProvinceWebController::class, 'update'])->name('admin.provinces.update');
    Route::delete('/provinces/{province}', [App\Http\Controllers\Admin\ProvinceWebController::class, 'destroy'])->name('admin.provinces.destroy');

    // Territoires (global, sans require.school)
    Route::get('/territories', [App\Http\Controllers\Admin\TerritoryWebController::class, 'index'])->name('admin.territories.index');
    Route::get('/territories/create', [App\Http\Controllers\Admin\TerritoryWebController::class, 'create'])->name('admin.territories.create');
    Route::post('/territories', [App\Http\Controllers\Admin\TerritoryWebController::class, 'store'])->name('admin.territories.store');
    Route::get('/territories/{territory}/edit', [App\Http\Controllers\Admin\TerritoryWebController::class, 'edit'])->name('admin.territories.edit');
    Route::put('/territories/{territory}', [App\Http\Controllers\Admin\TerritoryWebController::class, 'update'])->name('admin.territories.update');
    Route::delete('/territories/{territory}', [App\Http\Controllers\Admin\TerritoryWebController::class, 'destroy'])->name('admin.territories.destroy');

    // Communes (global, sans require.school)
    Route::get('/communes', [App\Http\Controllers\Admin\CommuneWebController::class, 'index'])->name('admin.communes.index');
    Route::get('/communes/create', [App\Http\Controllers\Admin\CommuneWebController::class, 'create'])->name('admin.communes.create');
    Route::post('/communes', [App\Http\Controllers\Admin\CommuneWebController::class, 'store'])->name('admin.communes.store');
    Route::get('/communes/{commune}/edit', [App\Http\Controllers\Admin\CommuneWebController::class, 'edit'])->name('admin.communes.edit');
    Route::put('/communes/{commune}', [App\Http\Controllers\Admin\CommuneWebController::class, 'update'])->name('admin.communes.update');
    Route::delete('/communes/{commune}', [App\Http\Controllers\Admin\CommuneWebController::class, 'destroy'])->name('admin.communes.destroy');

    // Module Comptabilité — nécessite une école sélectionnée
    Route::get('/accounting', AccountingDashboardController::class)
        ->middleware('require.school')
        ->name('admin.accounting.index');
});

// Helper route: allow front-end to POST the API token to create a session and redirect
Route::post('/auth/web-login', [App\Http\Controllers\Auth\WebLoginController::class, 'login'])
    ->name('auth.web.login');

// Also accept GET for convenience (front can open a URL with token as query string)
Route::get('/auth/web-login', [App\Http\Controllers\Auth\WebLoginController::class, 'login'])
    ->name('auth.web.login.get');

// Routes d'authentification (formulaire classique)
Route::middleware('web')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });

    Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
}); #}
