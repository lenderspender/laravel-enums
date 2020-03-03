<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Integration\Commands\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use LenderSpender\LaravelEnums\Models\Traits\CastsEnums;

/**
 * App\Models\ModelWithEnum.
 *
 * @property string                     $test
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
