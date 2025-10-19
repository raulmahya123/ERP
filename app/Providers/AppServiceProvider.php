<?php

namespace App\Providers;

use App\Models\Scm\HourMeter;
use App\Observers\Scm\HourMeterObserver;
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
        HourMeter::observe(HourMeterObserver::class);
    }
}
