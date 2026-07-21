<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats\Laravel;

use Illuminate\Support\ServiceProvider;
use RyanHellyer\ProductionStats\Laravel\Http\Middleware\InjectLoadTime;

class ProductionStatsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        /** @var \Illuminate\Contracts\Http\Kernel $kernel */
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        // @phpstan-ignore method.notFound
        $kernel->pushMiddleware(InjectLoadTime::class);
    }
}
