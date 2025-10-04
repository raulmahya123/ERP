<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureSiteSelected; // ⬅️ tambahkan import ini

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias middleware kustom kita
        $middleware->alias([
            'hasrole'       => EnsureUserHasRole::class,
            'site.selected' => EnsureSiteSelected::class, // ⬅️ tambahkan alias ini

            // Jika juga pakai Spatie (opsional), aktifkan baris di bawah:
            // 'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        ]);
    })
    ->withProviders([
        App\Providers\GateServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
