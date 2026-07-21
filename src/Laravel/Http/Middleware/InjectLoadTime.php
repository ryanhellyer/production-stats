<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RyanHellyer\ProductionStats\Core\HtmlResponseInjector;
use Symfony\Component\HttpFoundation\Response;

class InjectLoadTime
{
    private HtmlResponseInjector $injector;

    public function __construct(?HtmlResponseInjector $injector = null)
    {
        $this->injector = $injector ?? new HtmlResponseInjector();
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $elapsedMs = defined('LARAVEL_START')
            ? round((microtime(true) - LARAVEL_START) * 1000, 2)
            : 0;

        return $this->injector->inject($response, $elapsedMs);
    }
}
