<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// middleware kustom kamu
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureSiteSelected;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ===== Alias middleware kustom =====
        $middleware->alias([
            'hasrole'       => EnsureUserHasRole::class,
            'site.selected' => EnsureSiteSelected::class,

            // Jika juga pakai Spatie (opsional):
            // 'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        ]);

        // (opsional) tambahkan middleware global / group di sini
        // $middleware->append(\Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class);
    })
    ->withProviders([
        // ===== Provider kamu sendiri (opsional, jika ada rule Gate tambahan) =====
        App\Providers\GateServiceProvider::class,

        // ===== WAJIB: mapping policy HrDailyEntry =====
        App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // 403 — AuthorizationException
        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak berwenang melakukan aksi ini.',
                ], 403);
            }
            return redirect()->back()->with('error', 'Anda tidak berwenang melakukan aksi ini.')->setStatusCode(403);
        });

        // 422 — ValidationException
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validasi gagal.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // 404 — ModelNotFoundException (opsional)
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }
        });
    })
    ->create();
