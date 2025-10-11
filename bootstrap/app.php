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
        // ===== Alias middleware kustom =====
        $middleware->alias([
            'hasrole'       => EnsureUserHasRole::class,
            'site.selected' => EnsureSiteSelected::class,

            // Jika juga pakai Spatie (opsional):
            // 'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        ]);

        // (Opsional) Bisa juga menambah middleware global / group di sini sesuai kebutuhan.
        // $middleware->append(\Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class);
    })
    ->withProviders([
        // ===== Provider yang sudah ada =====
        App\Providers\GateServiceProvider::class,

        // ===== Wajib untuk Policy approve/reject HrDailyEntry =====
 App\Providers\GateServiceProvider::class,   // punyamu sendiri
    App\Providers\AuthServiceProvider::class,   // wajib untuk policy
        // ===== Opsional: kalau kamu pakai Event untuk notifikasi approval =====
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // 403 — AuthorizationException → tampilkan pesan yang ramah
        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak berwenang melakukan aksi ini.',
                ], 403);
            }
            return back()->with('error', 'Anda tidak berwenang melakukan aksi ini.')->setStatusCode(403);
        });

        // 422 — ValidationException → JSON rapi untuk API
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validasi gagal.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // (Opsional) 404 untuk model tidak ditemukan
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }
        });
    })
    ->create();
