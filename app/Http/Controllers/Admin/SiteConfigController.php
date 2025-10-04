<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Site;
use App\Models\SiteConfig;
use Illuminate\Http\Request;

class SiteConfigController extends Controller
{
    // GET /admin/site-config (middleware: auth + hasrole:gm)
    public function edit(Request $r)
    {
        $siteId = session('site_id'); // diasumsikan sudah dipasang middleware SetCurrentSite, tapi untuk fokus kita cukup via session
        abort_unless($siteId, 400, 'Pilih site terlebih dahulu.');

        $site = Site::findOrFail($siteId);
        $commodities = Commodity::orderBy('code')->get();
        $configs = SiteConfig::where('site_id', $siteId)->get()->keyBy('commodity_id');

        // View belum diminta — return JSON sementara untuk verifikasi
        // (nanti tinggal ganti ke view blade)
        return response()->json([
            'site' => $site,
            'commodities' => $commodities,
            'configs' => $configs,
        ]);
    }

    // POST /admin/site-config (middleware: auth + hasrole:gm)
    public function update(Request $r)
    {
        $siteId = session('site_id');
        abort_unless($siteId, 400, 'Pilih site terlebih dahulu.');

        // payload bentuk:
        // params[<commodity_uuid>] => [ hba => ..., ni_grade_min => ..., assay_method => ..., shift_roster => [...] ]
        $payload = $r->input('params', []);
        abort_if(!is_array($payload), 422, 'Format params tidak valid.');

        // Validasi key commodity_id
        $commodityIds = array_keys($payload);
        foreach ($commodityIds as $cid) {
            abort_unless(\Ramsey\Uuid\Uuid::isValid($cid), 422, "Commodity id tidak valid: {$cid}");
        }

        foreach ($payload as $commodityId => $params) {
            SiteConfig::updateOrCreate(
                ['site_id' => $siteId, 'commodity_id' => $commodityId],
                ['params' => is_array($params) ? $params : []]
            );
        }

        return response()->json(['message' => 'Konfigurasi tersimpan.']);
    }
}
