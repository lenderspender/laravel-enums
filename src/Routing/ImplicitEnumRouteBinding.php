<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Routing;

use Illuminate\Routing\ImplicitRouteBinding;
use LenderSpender\LaravelEnums\Enum;

class ImplicitEnumRouteBinding extends ImplicitRouteBinding
{
    public static function resolveForRoute($container, $route)
    {
        $parameters = $route->parameters();

        foreach ($route->signatureParameters(Enum::class) as $parameter) {
            if (! $parameterName = self::getParameterName($parameter->name, $parameters)) {
                continue;
            }

            $parameterValue = $parameters[$parameterName];

            if ($parameterValue instanceof Enum) {
                continue;
            }

            $enumClass = $parameter->getClass()->name;

            abort_unless($enumClass::isValidValue($parameterValue), 404, "'{$parameterValue}' is not a valid value for '{$enumClass}'");

            $instance = $container->makeWith($enumClass, ['value' => $parameterValue]);

            $route->setParameter($parameterName, $instance);
        }
    }
}
