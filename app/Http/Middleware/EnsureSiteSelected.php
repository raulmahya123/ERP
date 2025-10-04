<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class EnsureSiteSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow override via query: ?site=UUID
        if ($request->filled('site')) {
            $candidate = Site::query()->whereKey($request->string('site'))->first();
            if ($candidate) {
                $request->session()->put('site_id', $candidate->getKey());
            }
        }

        // Kalau belum ada site_id di session, coba auto-pilih jika cuma 1
        if (!$request->session()->has('site_id')) {
            $sites = Site::query()
                // ->whereHas('users', fn($q)=>$q->where('users.id', $request->user()->id)) // kalau mau filter akses
                ->orderBy('name')
                ->get();

            if ($sites->count() === 1) {
                $request->session()->put('site_id', $sites->first()->getKey());
            }
        }

        // Masih belum ada? Arahkan ke halaman pemilih site
        if (!$request->session()->has('site_id')) {
            if ($request->expectsJson()) {
                abort(400, 'Pilih site terlebih dahulu.');
            }
            // simpan intended agar balik ke URL semula setelah pilih
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->route('sites.select');
        }

        return $next($request);
    }
}
