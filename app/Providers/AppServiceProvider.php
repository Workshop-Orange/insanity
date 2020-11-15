<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Engines\SanityEngine;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SanityEngine::class, function ($app) {
            return new SanityEngine();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
