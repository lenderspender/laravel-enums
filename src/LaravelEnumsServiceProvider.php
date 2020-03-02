<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums;

use Illuminate\Support\ServiceProvider;
use LenderSpender\LaravelEnums\Commands\AddDocBlocksToEnums;
use LenderSpender\LaravelEnums\Commands\AddEnumDocBlockToModels;

class LaravelEnumsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../config/laravel-enums.php' => config_path('laravel-enums.php')], 'config');
    }

    public function register()
    {
        if (! $this->app->environment('production')) {
            $this->commands([
                AddDocBlocksToEnums::class,
                AddEnumDocBlockToModels::class,
            ]);

            $this->mergeConfigFrom(__DIR__ . '/../config/laravel-enums.php', 'ide-helper');
        }
    }
}
