<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests;

use LenderSpender\LaravelEnums\LaravelEnumsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(__DIR__ . '/../');
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelEnumsServiceProvider::class,
        ];
    }
}
