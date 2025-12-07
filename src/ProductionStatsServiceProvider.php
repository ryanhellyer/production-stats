<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats;

use Illuminate\Support\ServiceProvider;
use RyanHellyer\ProductionStats\Http\Middleware\InjectLoadTime;

/**
 * Service provider that automatically registers the performance monitoring middleware.
 *
 * Extends ServiceProvider instead of being a plain service to enable:
 * - Auto-discovery via composer.json (zero-config installation)
 * - Proper lifecycle integration during Laravel's boot process
 * - Access to the application container for middleware registration
 */
class ProductionStatsServiceProvider extends ServiceProvider
{
    public function register()
    {}

    public function boot()
    {
        // Register the middleware globally
        $this->app->make(\Illuminate\Contracts\Http\Kernel::class)
            ->pushMiddleware(InjectLoadTime::class);
    }
}
