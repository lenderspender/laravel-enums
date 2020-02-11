<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Unit\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\LaravelEnums\Models\Traits\CastsEnums;
use LenderSpender\LaravelEnums\Tests\Unit\Stubs\Enums\FakeEnum;
use LenderSpender\LaravelEnums\Tests\Unit\Stubs\Enums\FakeUnknownEnum;

/**
 * App\Models\ModelWithEnum.
 *
 * @property string $test
 * @property string|\App\Enums\Bar|null $bar
 */
class ModelWithEnum extends Model
{
    use CastsEnums;

    protected $enums = [
        'foo' => FakeEnum::class,
        'baz' => FakeUnknownEnum::class,
    ];
}
