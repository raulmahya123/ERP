<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Site;

class SiteContext
{
    /**
     * Ambil site_id aktif dari:
     * - session('site_id')   (dipasang oleh middleware EnsureSiteSelected)
     * - fallback: $user->default_site_id
     */
    public static function currentSiteId(?Authenticatable $user = null): ?string
    {
        // middleware EnsureSiteSelected sudah berusaha mengisi session('site_id')
        $sid = (string) (Session::get('site_id') ?? '');
        if ($sid !== '') return $sid;

        if ($user && !empty($user->default_site_id)) {
            return (string) $user->default_site_id;
        }
        return null;
    }

    /**
     * Ambil objek Site aktif (di-cache ringan via service container bila ada).
     */
    public static function currentSite(?Authenticatable $user = null): ?Site
    {
        // Middleware EnsureSiteSelected mendaftarkan instance 'currentSite' ke container (kalau kamu pakai versinya).
        if (App::bound('currentSite')) {
            return App::make('currentSite');
        }

        $sid = self::currentSiteId($user);
        if (!$sid) return null;

        return Site::query()->find($sid);
    }
}
