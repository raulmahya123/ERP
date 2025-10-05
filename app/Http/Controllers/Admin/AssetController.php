<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Support\SiteContext;

class AssetController extends Controller
{
    /* ==========================
     |  Helpers (DRY)
     |==========================*/
    /**
     * Ambil options master_records untuk entity tertentu, scoped ke site aktif
     * dengan fallback global (site_id NULL).
     */
    protected function masterOptions(string $entity, ?string $sid)
    {
        return DB::table('master_records')
            ->where('entity', $entity)
            ->when($sid, function ($qq) use ($sid) {
                $qq->where(function ($w) use ($sid) {
                    $w->where('site_id', $sid)->orWhereNull('site_id');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Buat Rule::exists untuk entity master tertentu
     * yang mengizinkan kepemilikan site aktif ATAU global (NULL).
     */
    protected function existsInMaster(string $entity, ?string $sid)
    {
        return Rule::exists('master_records', 'id')
            ->where(function ($q) use ($entity, $sid) {
                $q->where('entity', $entity);
                if ($sid) {
                    $q->where(function ($w) use ($sid) {
                        $w->where('site_id', $sid)->orWhereNull('site_id');
                    });
                }
            });
    }

    /* ==========================
     |  Actions
     |==========================*/
    public function index(Request $r)
    {
        $sid = SiteContext::currentSiteId($r->user());

        $q = Asset::query()
            ->with(['site','category','costCenter']) // ⬅️ include site utk tabel
            ->when($sid, fn ($qq) => $qq->where('site_id', $sid));

        if ($s = trim((string) $r->get('q', ''))) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('serial_no', 'like', "%{$s}%")
                  ->orWhere('plate_no', 'like', "%{$s}%");
            });
        }

        $assets = $q->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.assets.index', compact('assets'));
    }

    public function create(Request $r)
    {
        $sid = SiteContext::currentSiteId($r->user());

        return view('admin.assets.form', [
            'asset'       => new Asset(),
            'categories'  => $this->masterOptions('asset_categories', $sid),
            'costCenters' => $this->masterOptions('cost_centers', $sid),
        ]);
    }

    public function store(Request $r)
    {
        $sid = SiteContext::currentSiteId($r->user());

        $data = $r->validate([
            'code'   => ['nullable', 'max:100', Rule::unique('assets', 'code')->where('site_id', $sid)],
            'name'   => ['required', 'max:255'],

            'asset_category_id' => ['nullable', 'uuid', $this->existsInMaster('asset_categories', $sid)],
            'cost_center_id'    => ['nullable', 'uuid', $this->existsInMaster('cost_centers', $sid)],

            'brand'           => ['nullable', 'max:100'],
            'model'           => ['nullable', 'max:100'],
            'serial_no'       => ['nullable', 'max:150'],
            'plate_no'        => ['nullable', 'max:50'],
            'engine_no'       => ['nullable', 'max:150'],
            'frame_no'        => ['nullable', 'max:150'],
            'status'          => ['nullable', 'in:active,inactive,repair,sold,disposed'],
            'commissioned_at' => ['nullable', 'date'],
            'location'        => ['nullable', 'max:150'],
            'assigned_to_user_id' => ['nullable', 'uuid'],
            'acq_cost'        => ['nullable', 'numeric'],
            'acq_date'        => ['nullable', 'date'],
            'extra'           => ['nullable'],
        ]);

        $payload = $data + [
            'site_id'    => $sid,                         // enforced by middleware
            'created_by' => optional($r->user())->id,
        ];

        // Normalisasi extra (boleh JSON/string)
        if (is_string($payload['extra'] ?? null)) {
            try {
                $payload['extra'] = json_decode($payload['extra'], true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) { /* keep as string */ }
        }

        $asset = Asset::create($payload);

        return redirect()->route('admin.assets.edit', $asset)->with('status', 'Asset created.');
    }

    public function edit(Request $r, Asset $asset)
    {
        $sid = SiteContext::currentSiteId($r->user());
        if ($sid && $asset->site_id !== $sid) abort(404);

        return view('admin.assets.form', [
            'asset'       => $asset->load(['site','category','costCenter']),
            'categories'  => $this->masterOptions('asset_categories', $sid),
            'costCenters' => $this->masterOptions('cost_centers', $sid),
        ]);
    }

    public function update(Request $r, Asset $asset)
    {
        $sid = SiteContext::currentSiteId($r->user());
        if ($sid && $asset->site_id !== $sid) abort(404);

        $data = $r->validate([
            'code'   => ['nullable', 'max:100', Rule::unique('assets', 'code')->where('site_id', $sid)->ignore($asset->id)],
            'name'   => ['required', 'max:255'],

            'asset_category_id' => ['nullable', 'uuid', $this->existsInMaster('asset_categories', $sid)],
            'cost_center_id'    => ['nullable', 'uuid', $this->existsInMaster('cost_centers', $sid)],

            'brand'           => ['nullable', 'max:100'],
            'model'           => ['nullable', 'max:100'],
            'serial_no'       => ['nullable', 'max:150'],
            'plate_no'        => ['nullable', 'max:50'],
            'engine_no'       => ['nullable', 'max:150'],
            'frame_no'        => ['nullable', 'max:150'],
            'status'          => ['nullable', 'in:active,inactive,repair,sold,disposed'],
            'commissioned_at' => ['nullable', 'date'],
            'location'        => ['nullable', 'max:150'],
            'assigned_to_user_id' => ['nullable', 'uuid'],
            'acq_cost'        => ['nullable', 'numeric'],
            'acq_date'        => ['nullable', 'date'],
            'extra'           => ['nullable'],
        ]);

        if (is_string($data['extra'] ?? null)) {
            try {
                $data['extra'] = json_decode($data['extra'], true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) { /* keep as-is */ }
        }

        $asset->update($data);

        return back()->with('status', 'Asset updated.');
    }

    public function destroy(Request $r, Asset $asset)
    {
        $sid = SiteContext::currentSiteId($r->user());
        if ($sid && $asset->site_id !== $sid) abort(404);

        $asset->delete();

        return redirect()->route('admin.assets.index')->with('status', 'Asset deleted.');
    }
}
