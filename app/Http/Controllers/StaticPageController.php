<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\SiteConfig;

class StaticPageController extends Controller
{
    public function __construct()
    {
        // Pastikan hanya user login (dan terverifikasi kalau perlu)
        $this->middleware(['auth']);
        // $this->middleware(['verified']); // aktifkan kalau pakai verifikasi email
    }

    /**
     * Dashboard utama yang menyesuaikan site yang user punya akses.
     * - Jika user punya relasi many-to-many sites(), pakai itu.
     * - Jika tidak, fallback ke default_site_id (defaultSite).
     * - Query param ?site_id=... akan divalidasi: harus termasuk dalam daftar akses.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user()->loadMissing(['defaultSite']);

        // 1) Kumpulan site yang user bisa akses (Collection<Site>)
        $accessibleSites = $this->getAccessibleSites($user);

        // 2) Tentukan site aktif (currentSite) + validasi kalau ada ?site_id
        $requestedSiteId = $request->string('site_id')->toString() ?: null;

        if ($requestedSiteId) {
            $currentSite = $accessibleSites->firstWhere('id', $requestedSiteId);
            if (! $currentSite) {
                // Hard fail jika user coba akses site yang bukan miliknya
                abort(403, 'Anda tidak memiliki akses ke site ini.');
            }
        } else {
            // Tanpa query, pilih yang pertama dari daftar akses
            $currentSite = $accessibleSites->first();
        }

        // 3) Muat SiteConfig + commodity (opsional)
        $siteConfig    = null;
        $commodityName = null;

        if ($currentSite) {
            $siteConfig    = SiteConfig::with('commodity')->where('site_id', $currentSite->id)->first();
            $commodityName = optional($siteConfig?->commodity)->name;
        }

        // 4) Render view
        return view('dashboard', [
            'accessibleSites' => $accessibleSites, // Collection<Site>
            'currentSite'     => $currentSite,     // ?Site
            'siteConfig'      => $siteConfig,      // ?SiteConfig
            'commodityName'   => $commodityName,   // ?string
        ]);
    }

    /**
     * Ambil daftar site yang bisa diakses user:
     * - Jika ada relasi many-to-many sites(), pakai itu.
     * - Jika tidak, fallback ke defaultSite (default_site_id).
     *
     * @return \Illuminate\Support\Collection<\App\Models\Site>
     */
    private function getAccessibleSites($user)
    {
        // Jika proyekmu punya tabel pivot site_user → gunakan relasi ini
        if (method_exists($user, 'sites')) {
            try {
                $sites = $user->sites()->orderBy('name')->get(['id','name']);
                if ($sites->isNotEmpty()) {
                    return $sites;
                }
            } catch (\Throwable $e) {
                // Jika tabel pivot belum ada atau error lain, lanjut ke fallback
            }
        }

        // Fallback: hanya defaultSite
        return $user->defaultSite
            ? collect([$user->defaultSite])  // pastikan berupa koleksi Site model
            : collect();                      // user belum punya akses site
    }
}
