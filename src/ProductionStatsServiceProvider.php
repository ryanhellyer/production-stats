<?php

declare(strict_types=1);

namespace RyanHellyer\ProductionStats;

use Illuminate\Support\ServiceProvider;
use RyanHellyer\ProductionStats\Http\Middleware\InjectLoadTime;

class ProductionStatsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register the middleware globally
        $this->app->make(\Illuminate\Contracts\Http\Kernel::class)
            ->pushMiddleware(InjectLoadTime::class);
    }
}
