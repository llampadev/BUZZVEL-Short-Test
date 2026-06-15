<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // This API has no 'login' route, so never redirect unauthenticated
        // requests there — always let them fall through to a JSON 401.
        Authenticate::redirectUsing(fn () => null);
    }
}
