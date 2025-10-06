<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Asset;
use App\Models\MasterRecord;
// Jika kamu sudah punya helper SiteContext:
use App\Support\SiteContext;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard utama.
     */
    public function index()
    {
        // === Site aktif (untuk scope master data) ===
        $sid = method_exists(SiteContext::class, 'id') ? SiteContext::id() : null;

        // === Dropdown kategori & cost center untuk modal quick-create (site + global) ===
        $categories = MasterRecord::query()
            ->where('entity', 'asset_categories')
            ->when($sid, fn($q) => $q->where(fn($w) => $w->where('site_id', $sid)->orWhereNull('site_id')))
            ->orderBy('name')
            ->get(['id', 'name']);

        $costCenters = MasterRecord::query()
            ->where('entity', 'cost_centers')
            ->when($sid, fn($q) => $q->where(fn($w) => $w->where('site_id', $sid)->orWhereNull('site_id')))
            ->orderBy('name')
            ->get(['id', 'name']);

        // === Daftar asset terbaru (campur: ada site & belum ada site) ===
        $recentAssets = Asset::query()
            ->with(['site:id,code', 'category:id,name'])
            ->latest()
            ->take(8)
            ->get();

        // === Hitung KPI summary ===
        $kpi = [
            'total'      => Asset::count(),
            'no_site'    => Asset::whereNull('site_id')->count(),
            'with_site'  => Asset::whereNotNull('site_id')->count(),
            'categories' => MasterRecord::where('entity', 'asset_categories')->count(),
        ];

        return view('dashboard', compact('categories', 'costCenters', 'recentAssets', 'kpi'));
    }

    /**
     * Simpan asset cepat dari dashboard (tanpa site).
     */
    public function quickStore(Request $request)
    {
        // === Normalisasi angka acq_cost: "1.231.231,00" -> "1231231.00" ===
        if (is_string($request->input('acq_cost'))) {
            $norm = str_replace(['.', ','], ['', '.'], $request->input('acq_cost'));
            $request->merge(['acq_cost' => $norm]);
        }

        $sid = method_exists(SiteContext::class, 'id') ? SiteContext::id() : null;

        $validated = $request->validate([
            'code'              => ['nullable', 'string', 'max:100'],
            'name'              => ['required', 'string', 'max:255'],

            // Master: kategori & cost center wajib benar entitasnya dan di-scope ke site aktif + global
            'asset_category_id' => [
                'nullable', 'uuid',
                Rule::exists('master_records', 'id')->where(function ($q) use ($sid) {
                    $q->where('entity', 'asset_categories');
                    if ($sid) $q->where(function($w) use ($sid){ $w->where('site_id',$sid)->orWhereNull('site_id'); });
                }),
            ],
            'cost_center_id' => [
                'nullable', 'uuid',
                Rule::exists('master_records', 'id')->where(function ($q) use ($sid) {
                    $q->where('entity', 'cost_centers');
                    if ($sid) $q->where(function($w) use ($sid){ $w->where('site_id',$sid)->orWhereNull('site_id'); });
                }),
            ],

            // Detail aset
            'brand'            => ['nullable', 'string', 'max:150'],
            'model'            => ['nullable', 'string', 'max:150'],
            'serial_no'        => ['nullable', 'string', 'max:150'],
            'plate_no'         => ['nullable', 'string', 'max:150'],

            // Status & tanggal
            'status'           => ['nullable', 'in:active,inactive,retired'],
            'commissioned_at'  => ['nullable', 'date'],

            // Perolehan
            'acq_date'         => ['nullable', 'date'],
            'acq_cost'         => ['nullable', 'numeric'],

            // Lain-lain
            'location'         => ['nullable', 'string', 'max:255'],
            'extra'            => ['nullable', 'array'], // ex: extra[notes]
        ]);

        $data = array_merge($validated, [
            'site_id'    => null,          // belum ditransfer ke site mana pun
            'created_by' => Auth::id(),
        ]);

        Asset::create($data);

        return back()->with('success', 'Asset berhasil dibuat tanpa site. Silakan transfer nanti untuk menetapkan site.');
    }
}
