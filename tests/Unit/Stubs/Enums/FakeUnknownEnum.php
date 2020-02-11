<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Unit\Stubs\Enums;

use LenderSpender\LaravelEnums\Enum;
use LenderSpender\LaravelEnums\CanBeUnknown;

class FakeUnknownEnum extends Enum implements CanBeUnknown
{
    private const FAKE = 'fake';
}
