<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Support\Providers\ServiceProvider as BaseServiceProvider;

class AppServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MySQL < 5.7.7 and MariaDB < 10.2.2
        Schema::defaultStringLength(191);

        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Prevent database queries during artisan commands
        if ($this->app->runningInConsole()) {
            return;
        }

        // Handle database errors during bootstrap
        try {
            // Any database operations that need to be done during bootstrap
            // will be wrapped in this try-catch block
            if (Schema::hasTable('trial_user')) {
                // Only perform operations if the table exists
                // This prevents errors during initial setup
            }
        } catch (\Exception $e) {
            // Silently ignore database errors during bootstrap
            // This allows the application to continue loading
            // even if the database is not yet set up
        }

        // Custom Blade directives
        Blade::if('env', function ($environment) {
            return app()->environment($environment);
        });

        // Share common data with all views
        View::share('appName', config('app.name'));
    }
}
