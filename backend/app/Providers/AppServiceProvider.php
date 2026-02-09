<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\InputAccount;
use App\Models\OutputAccount;
use App\Observers\InputAccountObserver;
use App\Observers\OutputAccountObserver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureVite();
        $this->shouldBeStrict();
        $this->configureDates();
        $this->configurePasswordValidation();

        // Alias pour compatibilité des vues qui utilisent Hook::applyFilters() & *FilterHook
        if (! class_exists('Hook')) {
            class_alias(\App\Support\Hook::class, 'Hook');
        }
        if (! class_exists('AdminFilterHook')) {
            class_alias(\App\Enums\AdminFilterHook::class, 'AdminFilterHook');
        }
        if (! class_exists('DashboardFilterHook')) {
            class_alias(\App\Enums\DashboardFilterHook::class, 'DashboardFilterHook');
        }

        InputAccount::observe(InputAccountObserver::class);
        OutputAccount::observe(OutputAccountObserver::class);
    }

    public function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }

    public function configureVite(): void
    {
        Vite::usePrefetchStrategy('aggressive');
    }

    public function shouldBeStrict(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard();
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the password validation rules.
     */
    private function configurePasswordValidation(): void
    {
        Password::defaults(fn () => $this->app->isProduction() ? Password::min(8)->uncompromised() : null);
    }
}
