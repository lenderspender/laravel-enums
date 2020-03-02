<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Http\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use LenderSpender\LaravelEnums\Routing\ImplicitEnumRouteBinding;

class SubstituteEnumBindings
{
    private Registrar $router;
    private Container $container;

    public function __construct(Registrar $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function handle($request, Closure $next)
    {
        ImplicitEnumRouteBinding::resolveForRoute($this->container, $request->route());

        return $next($request);
    }
}
