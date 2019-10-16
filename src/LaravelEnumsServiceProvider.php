<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums;

use Illuminate\Support\ServiceProvider;
use LenderSpender\LaravelEnums\Commands\AddDocBlocksToEnums;
use LenderSpender\LaravelEnums\src\Commands\AddEnumDocBlockToModels;

class LaravelEnumsServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->environment('local', 'dev')) {
            $this->commands([
                AddDocBlocksToEnums::class,
                AddEnumDocBlockToModels::class,
            ]);
        }
    }
}
