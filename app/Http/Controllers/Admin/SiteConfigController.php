<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SiteConfig, Site, Commodity};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteConfigController extends Controller
{
    public function index(Request $request)
    {
        $siteId      = $request->query('site');
        $commodityId = $request->query('commodity');

        $configs = SiteConfig::with(['site','commodity'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($commodityId, fn($q) => $q->where('commodity_id', $commodityId))
            // Urutkan rapi berdasarkan code site & commodity (tanpa join berat)
            ->orderByRaw('(SELECT code FROM sites WHERE sites.id = site_configs.site_id) asc')
            ->orderByRaw('(SELECT code FROM commodities WHERE commodities.id = site_configs.commodity_id) asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.site_configs.index', [
            'configs'              => $configs,
            'sites'                => Site::orderBy('code')->get(['id','code','name']),
            'commodities'          => Commodity::orderBy('code')->get(['id','code','name']),
            'selectedSiteId'       => $siteId,
            'selectedCommodityId'  => $commodityId,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.site_configs.form', [
            'config'         => new SiteConfig(),
            'sites'          => Site::orderBy('code')->get(['id','code','name']),
            'commodities'    => Commodity::orderBy('code')->get(['id','code','name']),
            'selectedSiteId' => $request->query('site'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_id'        => ['required','exists:sites,id'],
            'commodity_id'   => [
                'required','exists:commodities,id',
                Rule::unique('site_configs', 'commodity_id')->where('site_id', $request->site_id),
            ],
            'hba'            => ['nullable','numeric'],
            'ni_grade_min'   => ['nullable','numeric'],
            'assay_method'   => ['nullable','string','max:255'],
            'shift_roster'   => ['nullable','array'],
            'shift_roster.*' => ['nullable','string','max:100'],
        ]);

        $params = array_filter([
            'hba'          => $request->hba,
            'ni_grade_min' => $request->ni_grade_min,
            'assay_method' => $request->assay_method,
            'shift_roster' => $request->shift_roster,
        ], fn($v) => !is_null($v) && $v !== '');

        SiteConfig::create([
            'site_id'      => $request->site_id,
            'commodity_id' => $request->commodity_id,
            'params'       => $params,
        ]);

        return redirect()
            ->route('admin.site_config.index', ['site' => $request->site_id])
            ->with('success', 'Konfigurasi site berhasil dibuat.');
    }

    public function edit(SiteConfig $site_config)
    {
        return view('admin.site_configs.form', [
            'config'         => $site_config,
            'sites'          => Site::orderBy('code')->get(['id','code','name']),
            'commodities'    => Commodity::orderBy('code')->get(['id','code','name']),
            'selectedSiteId' => $site_config->site_id,
        ]);
    }

    public function update(Request $request, SiteConfig $site_config)
    {
        $request->validate([
            'site_id'        => ['required','exists:sites,id'],
            'commodity_id'   => [
                'required','exists:commodities,id',
                Rule::unique('site_configs', 'commodity_id')
                    ->where('site_id', $request->site_id)
                    ->ignore($site_config->id, 'id'),
            ],
            'hba'            => ['nullable','numeric'],
            'ni_grade_min'   => ['nullable','numeric'],
            'assay_method'   => ['nullable','string','max:255'],
            'shift_roster'   => ['nullable','array'],
            'shift_roster.*' => ['nullable','string','max:100'],
        ]);

        $params = array_filter([
            'hba'          => $request->hba,
            'ni_grade_min' => $request->ni_grade_min,
            'assay_method' => $request->assay_method,
            'shift_roster' => $request->shift_roster,
        ], fn($v) => !is_null($v) && $v !== '');

        $site_config->update([
            'site_id'      => $request->site_id,
            'commodity_id' => $request->commodity_id,
            'params'       => $params,
        ]);

        return redirect()
            ->route('admin.site_config.index', ['site' => $request->site_id])
            ->with('success', 'Konfigurasi site berhasil diperbarui.');
    }

    public function destroy(SiteConfig $site_config)
    {
        $siteId = $site_config->site_id;
        $site_config->delete();

        return redirect()
            ->route('admin.site_config.index', ['site' => $siteId])
            ->with('success', 'Konfigurasi site berhasil dihapus.');
    }
}
