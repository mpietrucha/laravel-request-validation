<?php

namespace Mpietrucha\RequestValidation;

use Illuminate\Support\ServiceProvider;

class RequestValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/request-validation.php' => config_path('request-validation.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'./../config/request-validation.php', 'request-validation');
    }
}
