<?php

namespace App\Support;

use App\Models\Site;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * SiteContext
 *
 * Sumber kebenaran "site aktif" untuk seluruh request.
 * Prioritas pengambilan:
 *   1) session('site_id')        → diisi oleh flow pilih-site / middleware
 *   2) $user->default_site_id    → fallback bila session kosong
 *
 * Disarankan:
 * - Gunakan middleware `site.selected` untuk memaksa user memilih site sebelum masuk modul per-site.
 * - Bind instance Site ke container dengan key 'currentSite' agar resolusi lebih cepat.
 */
class SiteContext
{
    /**
     * Ambil site_id aktif dari session, fallback ke default_site_id user.
     */
    public static function currentSiteId(?Authenticatable $user = null): ?string
    {
        $sid = (string) (Session::get('site_id') ?? '');
        if ($sid !== '') {
            return $sid;
        }

        $user = $user ?: Auth::user();
        if ($user && !empty($user->default_site_id)) {
            return (string) $user->default_site_id;
        }

        return null;
    }

    /**
     * Ambil objek Site aktif.
     * - Jika container sudah punya binding 'currentSite', pakai itu.
     * - Kalau belum, cari dari DB berdasarkan currentSiteId().
     */
    public static function currentSite(?Authenticatable $user = null): ?Site
    {
        if (App::bound('currentSite')) {
            /** @var Site $site */
            $site = App::make('currentSite');
            return $site instanceof Site ? $site : null;
        }

        $sid = self::currentSiteId($user);
        if (!$sid) {
            return null;
        }

        return Site::query()->find($sid);
    }

    /**
     * Back-compat alias: Ambil site_id aktif.
     * NB: Banyak kode lama memanggil SiteContext::getId().
     */
    public static function getId(?Authenticatable $user = null): ?string
    {
        $user = $user ?: Auth::user();
        return self::currentSiteId($user);
    }

    /**
     * Alias singkat dari getId().
     */
    public static function id(?Authenticatable $user = null): ?string
    {
        return self::getId($user);
    }

    /**
     * Set site aktif ke session (tanpa load model).
     * Gunakan ini setelah user memilih site dari UI.
     */
    public static function setCurrentSiteId(?string $siteId): void
    {
        if ($siteId) {
            Session::put('site_id', $siteId);
        } else {
            Session::forget('site_id');
        }
        // Reset binding agar tidak stale
        if (App::bound('currentSite')) {
            App::forgetInstance('currentSite');
        }
    }

    /**
     * Set & bind instance Site aktif ke container + session.
     * Berguna di middleware setelah verifikasi Site ada.
     */
    public static function setCurrentSite(?Site $site): void
    {
        if ($site) {
            Session::put('site_id', $site->getKey());
            App::instance('currentSite', $site);
        } else {
            Session::forget('site_id');
            if (App::bound('currentSite')) {
                App::forgetInstance('currentSite');
            }
        }
    }

    /**
     * Hapus konteks site (session & binding).
     */
    public static function forget(): void
    {
        Session::forget('site_id');
        if (App::bound('currentSite')) {
            App::forgetInstance('currentSite');
        }
    }

    /**
     * Ambil Site aktif, tetapi wajib ada (akan throw jika kosong).
     */
    public static function siteOrFail(?Authenticatable $user = null): Site
    {
        $site = self::currentSite($user);
        if (!$site) {
            throw new \RuntimeException('Site aktif tidak ditemukan. Pastikan user sudah memilih site.');
        }
        return $site;
    }

    /**
     * Helper: Resolusi dari request (ambil dari route query/body jika ada),
     * lalu simpan ke session & bind instance.
     */
    public static function resolveFromRequest(Request $request, bool $bindIfFound = true): ?Site
    {
        $siteId = $request->input('site_id') ?: $request->route('site_id');
        if ($siteId) {
            $site = Site::query()->find($siteId);
            if ($site && $bindIfFound) {
                self::setCurrentSite($site);
            }
            return $site;
        }

        // fallback: pakai yang sudah ada
        $site = self::currentSite();
        if ($site && $bindIfFound && !App::bound('currentSite')) {
            App::instance('currentSite', $site);
        }
        return $site;
    }
}
