<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserHasRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias middleware kustom kita → "hasrole"
        // (Hindari menimpa alias "role" milik Spatie, jika kamu pakai Spatie.)
        $middleware->alias([
            'hasrole' => EnsureUserHasRole::class,

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
