<?php

namespace Audentio\LaravelPermissions\Providers;

use Audentio\LaravelAuth\LaravelAuth;
use Audentio\LaravelPermissions\LaravelPermissions;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class PermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/audentioPermissions.php', 'audentioPermissions'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
            $this->registerPublishes();
        }
    }

    protected function registerMigrations(): void
    {
        if (LaravelPermissions::runsMigrations()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }
    }

    protected function registerPublishes(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/audentioPermissions.php' => config_path('audentioPermissions.php'),
        ]);
    }
}