<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Integration\Commands;

use LenderSpender\LaravelEnums\Tests\TestCase;

class AddEnumDocBlockToModelsTest extends TestCase
{
    public function test_models_have_enum_docblocks(): void
    {
        config([
            'laravel-enums.model_locations' => [
                'tests/Integration/Commands/Stubs/Models/',
            ],
        ]);

        $this->artisan('ide-helper:generate:model-enums')
            ->expectsOutput('Added enum information to 1 models');

        $content = file_get_contents(__DIR__ . '/Stubs/Models/ModelWithEnum.php');

        copy(__DIR__ . '/Stubs/Models/ModelWithEnum.org.stub', __DIR__ . '/Stubs/Models/ModelWithEnum.php');
        self::assertSame(file_get_contents(__DIR__ . '/Stubs/Models/ModelWithEnum.stub'), $content);
    }
}
