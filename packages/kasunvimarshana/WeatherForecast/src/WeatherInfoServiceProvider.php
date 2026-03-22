<?php

namespace kasunvimarshana\WeatherForecast;

use Illuminate\Support\ServiceProvider;

class WeatherForecastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/weatherforecast.php' => config_path('weatherforecast.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/weatherforecast.php',
            'weatherforecast'
        );

        $this->app->singleton(WeatherService::class, function ($app) {
            return new WeatherService();
        });
    }
}
