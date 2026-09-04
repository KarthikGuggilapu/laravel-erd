<?php

namespace YourVendor\LaravelErd;

use Illuminate\Support\ServiceProvider;
use YourVendor\LaravelErd\Commands\InstallCommand;
use YourVendor\LaravelErd\Commands\RefreshCommand;
use YourVendor\LaravelErd\Services\RegistryManager;

class ErdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/erd.php',
            'erd'
        );

        $this->app->singleton(
            RegistryManager::class,
            fn () => new RegistryManager()
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/erd.php' => config_path('erd.php'),
        ], 'erd-config');

        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'erd'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                RefreshCommand::class,
            ]);
        }

        if (config('erd.enabled')) {
            $this->loadRoutesFrom(
                __DIR__ . '/../routes/web.php'
            );
        }
    }
}