<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Http\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use LenderSpender\LaravelEnums\Routing\ImplicitEnumRouteBinding;

class SubstituteEnumBindings
{
    /** @var \Illuminate\Contracts\Routing\Registrar */
    private $router;

    /** @var \Illuminate\Container\Container */
    private $container;

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
