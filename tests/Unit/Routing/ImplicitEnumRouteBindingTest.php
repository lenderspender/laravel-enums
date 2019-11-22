<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use LenderSpender\LaravelEnums\Enum;
use LenderSpender\LaravelEnums\Routing\ImplicitEnumRouteBinding;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImplicitEnumRouteBindingTest extends TestCase
{
    public function test_enum_is_bound_to_route(): void
    {
        $route = new Route('POST', '/bla/{testEnum}', function (TestEnum $testEnum) {});

        $request = Request::create('/bla/valid', 'POST');
        $route->bind($request);

        ImplicitEnumRouteBinding::resolveForRoute($this->app, $route);

        self::assertTrue(TestEnum::VALID()->equals($route->parameter('testEnum')));
    }

    public function test_not_found_exception_is_thrown_when_enum_does_not_exists(): void
    {
        $route = new Route('POST', '/bla/{testEnum}', function (TestEnum $testEnum) {});

        $request = Request::create('/bla/invalid', 'POST');
        $route->bind($request);

        try {
            ImplicitEnumRouteBinding::resolveForRoute($this->app, $route);
        } catch (NotFoundHttpException $e) {
            self::assertSame("'invalid' is not a valid value for '" . TestEnum::class . "'", $e->getMessage());

            return;
        }

        self::fail('Invalid enum should throw not found exception');
    }
}

class TestEnum extends Enum
{
    private const VALID = 'valid';
}
