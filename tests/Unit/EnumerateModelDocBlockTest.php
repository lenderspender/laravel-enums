<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Unit;

use LenderSpender\LaravelEnums\EnumerateModelDocBlock;
use Orchestra\Testbench\TestCase;

class EnumerateModelDocBlockTest extends TestCase
{
    /** @var \LenderSpender\LaravelEnums\EnumerateModelDocBlock */
    private $enumerateModelDocBlock;

    public function setUp(): void
    {
        parent::setUp();

        $this->enumerateModelDocBlock = $this->app->make(EnumerateModelDocBlock::class);
    }

    public function test_models_are_enumerated(): void
    {
        $this->enumerateModelDocBlock->enumerate(__DIR__ . '/Stubs/Models', 'LenderSpender\LaravelEnums\Tests\Unit\Stubs\Models');

        $content = file_get_contents(__DIR__ . '/Stubs/Models/ModelWithEnum.php');

        copy(__DIR__ . '/Stubs/Models/ModelWithEnum.org.stub', __DIR__ . '/Stubs/Models/ModelWithEnum.php');
        self::assertSame($content, file_get_contents(__DIR__ . '/Stubs/Models/ModelWithEnum.stub'));
    }
}
