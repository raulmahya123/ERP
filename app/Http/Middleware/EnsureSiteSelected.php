<?php
// app/Http/Middleware/EnsureSiteSelected.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class EnsureSiteSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hindari loop pada halaman pemilihan site
        if ($request->routeIs('sites.select') || $request->routeIs('sites.choose')) {
            return $next($request);
        }

        // Enforce hanya untuk user login
        if (!$request->user()) {
            return $next($request);
        }

        /* =========================================================
         |  1) Cari kandidat site dari: ?site=, header, route param
         |=========================================================*/
        $candidateId = null;

        // Query string ?site=UUID
        if ($request->filled('site')) {
            $candidateId = (string) $request->string('site');
        }

        // Header X-Site-ID (mis. dari mobile/SPA)
        if (!$candidateId && $request->hasHeader('X-Site-ID')) {
            $candidateId = (string) $request->header('X-Site-ID');
        }

        // Route parameter {site} (kalau ada)
        if (!$candidateId && ($rp = $request->route('site'))) {
            $candidateId = is_object($rp) ? ($rp->id ?? null) : (string) $rp;
        }

        // Jika ada kandidat → validasi & set session
        if ($candidateId) {
            $site = $this->findSiteCached($candidateId);
            if ($site) {
                // (Opsional) Cek akses user ke site via Gate/Policy 'use-site'
                if (Gate::has('use-site') && Gate::denies('use-site', $site)) {
                    abort(403, 'Anda tidak berhak mengakses site ini.');
                }

                $request->session()->put('site_id', $site->id);

                // Opsional: remember=1 → simpan sebagai default_site_id user
                if ($request->boolean('remember') || empty($request->user()->default_site_id)) {
                    $request->user()->forceFill(['default_site_id' => $site->id])->save();
                }
            }
        }

        /* =========================================================
         |  2) Fallback ke default_site_id user
         |=========================================================*/
        if (!$request->session()->has('site_id')) {
            $default = (string) ($request->user()->default_site_id ?? '');
            if ($default !== '' && $this->findSiteCached($default)) {
                $request->session()->put('site_id', $default);
            }
        }

        /* =========================================================
         |  3) Jika masih kosong, auto-pilih jika hanya ada 1 site
         |=========================================================*/
        if (!$request->session()->has('site_id')) {
            $onlyOne = Site::query()
                // ->whereHas('users', fn($q) => $q->where('users.id', $request->user()->id)) // aktifkan jika perlu
                ->orderBy('name')
                ->limit(2)
                ->get(['id']); // ambil minimal field

            if ($onlyOne->count() === 1) {
                $request->session()->put('site_id', (string) $onlyOne->first()->id);
            }
        }

        /* =========================================================
         |  4) Jika tetap belum ada → redirect pilih site
         |=========================================================*/
        if (!$request->session()->has('site_id')) {
            if ($request->expectsJson()) {
                abort(400, 'Pilih site terlebih dahulu.');
            }
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->route('sites.select');
        }

        /* =========================================================
         |  5) Inject currentSiteId & currentSite (service container)
         |     dan share ke view (global)
         |=========================================================*/
        $currentSiteId = (string) $request->session()->get('site_id');
        $currentSite   = $this->findSiteCached($currentSiteId);

        app()->instance('currentSiteId', $currentSiteId);
        app()->instance('currentSite', $currentSite);
        view()->share('currentSiteId', $currentSiteId);
        view()->share('currentSite', $currentSite);

        return $next($request);
    }

    /**
     * Ambil Site via cache agar hemat query.
     */
    protected function findSiteCached(string $id): ?object
    {
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            return null;
        }

        return Cache::remember("site:{$id}", 300, function () use ($id) {
            return Site::query()->whereKey($id)->first(['id','code','name']);
        });
    }
}
