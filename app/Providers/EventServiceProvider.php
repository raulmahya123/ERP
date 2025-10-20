<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\SendEmailVerificationCode;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * Daftarkan listener OTP untuk event Registered.
     */
    public function boot(): void
    {
        Event::listen(
            Registered::class,
            [SendEmailVerificationCode::class, 'handle']
        );
    }
}
