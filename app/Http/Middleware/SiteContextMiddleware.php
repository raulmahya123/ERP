<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteContextMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika sudah ada di session, lanjut
        if ($request->session()->has('site_id') && $request->session()->get('site_id')) {
            return $next($request);
        }

        // Coba isi otomatis dari default_site_id user (kalau ada)
        $user = $request->user();
        if ($user && !empty($user->default_site_id)) {
            $request->session()->put('site_id', (string) $user->default_site_id);
            return $next($request);
        }

        // Kalau ada route pemilihan site, redirect ke sana
        if (app('router')->has('sites.select')) {
            // Simpan intended agar balik ke halaman tujuan setelah memilih
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->route('sites.select')
                ->with('warning', 'Pilih site terlebih dahulu.');
        }

        // Fallback: 403 kalau tidak ada route pemilihan site
        abort(403, 'Site context is required. Please set site_id.');
    }
}
