<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Integration\Commands;

use LenderSpender\LaravelEnums\Tests\TestCase;

class AddDocBlocksToEnumsTest extends TestCase
{
    public function test_enums_get_docblocks(): void
    {
        config([
            'laravel-enums.enum_locations' => [
                'tests/Integration/Commands/Stubs/Enums/',
            ],
        ]);

        $this->artisan('ide-helper:generate:enums')
            ->expectsOutput('Parsed 1 enums');

        $content = file_get_contents(__DIR__ . '/Stubs/Enums/FooEnum.php');

        copy(__DIR__ . '/Stubs/Enums/FooEnum.org', __DIR__ . '/Stubs/Enums/FooEnum.php');
        self::assertSame(file_get_contents(__DIR__ . '/Stubs/Enums/FooEnum.stub'), $content);
    }
}
