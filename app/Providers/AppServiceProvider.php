<?php

namespace App\Providers;

use App\Services\FifoService;
use App\Services\HppService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FifoService::class);

        $this->app->singleton(HppService::class, function ($app) {
            return new HppService($app->make(FifoService::class));
        });
    }

    public function boot(): void {}
}
