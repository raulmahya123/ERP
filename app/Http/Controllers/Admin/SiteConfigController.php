<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SiteConfig, Site, Commodity};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteConfigController extends Controller
{
    /**
     * List Konfigurasi Site — filter manual (Apply)
     * - Filter Site via text search: cocokkan ke code/name site (LIKE)
     * - Filter Komoditas tetap via dropdown
     * - Tidak auto-filter setelah create/update/destroy
     */
    public function index(Request $request)
    {
        // Apakah user benar-benar klik "Terapkan"?
        $manual = $request->boolean('apply');

        // Ambil query pencarian site (text)
        $siteSearch = $request->query('site_q'); // ganti dari 'site' => 'site_q'

        // Preselect / Prefill nilai UI (tidak memaksa filter)
        // - Jika datang dari submit (apply=1), tampilkan kembali nilai yang diketik user
        // - Jika dari redirect store/update/destroy, gunakan flash session('ui_site_id') hanya untuk "Create" nanti
        $uiSiteSearch  = $request->query('apply') ? $siteSearch : null;
        $uiCommodityId = $request->query('apply') ? $request->query('commodity') : null;

        // Query utama
        $configsQ = SiteConfig::with(['site','commodity']);

        // Terapkan filter HANYA saat manual apply
        if ($manual) {
            // Filter Site via LIKE ke code/name
            if (filled($siteSearch)) {
                $matchIds = Site::query()
                    ->where(function ($qq) use ($siteSearch) {
                        $term = '%' . trim($siteSearch) . '%';
                        $qq->where('code', 'like', $term)
                           ->orWhere('name', 'like', $term);
                    })
                    ->pluck('id');

                // Jika tidak ada yang cocok, pastikan hasil kosong
                $configsQ->whereIn('site_id', $matchIds ?: [-1]);
            }

            // Filter Komoditas (ID dropdown)
            if ($request->filled('commodity')) {
                $configsQ->where('commodity_id', $request->query('commodity'));
            }
        }

        $configs = $configsQ
            // Urutkan rapi tanpa join berat
            ->orderByRaw('(SELECT code FROM sites WHERE sites.id = site_configs.site_id) asc')
            ->orderByRaw('(SELECT code FROM commodities WHERE commodities.id = site_configs.commodity_id) asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.site_configs.index', [
            'configs'              => $configs,
            'sites'                => Site::orderBy('code')->get(['id','code','name']),  // masih dipakai untuk create/form
            'commodities'          => Commodity::orderBy('code')->get(['id','code','name']),
            'uiSiteSearch'         => $uiSiteSearch,      // untuk isian teks filter site
            'selectedCommodityId'  => $uiCommodityId,     // untuk preselect komoditas saat apply
            'uiSiteIdForCreateBtn' => session('ui_site_id') ?? session('site_id') ?? $request->user()?->default_site_id,
        ]);
    }

    /**
     * Form Create — boleh preselect site untuk kenyamanan (tidak memicu filter index)
     */
    public function create(Request $request)
    {
        return view('admin.site_configs.form', [
            'config'         => new SiteConfig(),
            'sites'          => Site::orderBy('code')->get(['id','code','name']),
            'commodities'    => Commodity::orderBy('code')->get(['id','code','name']),
            'selectedSiteId' => $request->query('site')
                ?? session('ui_site_id')
                ?? session('site_id')
                ?? $request->user()?->default_site_id,
        ]);
    }

    /**
     * Simpan
     */
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

        // Redirect TANPA query supaya index tidak auto-filter
        // Kirim flash 'ui_site_id' agar tombol "+ Tambah Konfigurasi" tetap nyaman
        return redirect()
            ->route('admin.site_config.index')
            ->with('ui_site_id', $request->site_id)
            ->with('success', 'Konfigurasi site berhasil dibuat.');
    }

    /**
     * Form Edit
     */
    public function edit(SiteConfig $site_config)
    {
        return view('admin.site_configs.form', [
            'config'         => $site_config,
            'sites'          => Site::orderBy('code')->get(['id','code','name']),
            'commodities'    => Commodity::orderBy('code')->get(['id','code','name']),
            'selectedSiteId' => $site_config->site_id,
        ]);
    }

    /**
     * Update
     */
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
            ->route('admin.site_config.index')
            ->with('ui_site_id', $request->site_id)
            ->with('success', 'Konfigurasi site berhasil diperbarui.');
    }

    /**
     * Hapus
     */
    public function destroy(SiteConfig $site_config)
    {
        $siteId = $site_config->site_id;
        $site_config->delete();

        return redirect()
            ->route('admin.site_config.index')
            ->with('ui_site_id', $siteId)
            ->with('success', 'Konfigurasi site berhasil dihapus.');
    }
}
