<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Integration\Commands\Stubs\Models;

use LenderSpender\LaravelEnums\Enum;
use LenderSpender\LaravelEnums\CanBeUnknown;

/**
 * @method static self FAKE()
 * @method static self UNKNOWN()
 */
class FakeUnknownEnum extends Enum implements CanBeUnknown
{
    private const FAKE = 'fake';
}
