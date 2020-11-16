<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Engines\SanityEngine;
use App\Engines\SanityEngineApi;

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
            return new SanityEngine(config('app.insanityId'));
        });

        $this->app->bind(SanityEngineApi::class, function ($app) {
            return new SanityEngineApi(config('app.insanityId'));
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
