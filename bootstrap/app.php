<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// import middleware kustom
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureSiteSelected;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias middleware kustom
        $middleware->alias([
            'hasrole'        => EnsureUserHasRole::class,
            'site.selected'  => EnsureSiteSelected::class, // ⬅️ alias untuk cek site

            // Jika juga pakai Spatie (opsional):
            // 'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        ]);
    })
    ->withProviders([
        App\Providers\GateServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
