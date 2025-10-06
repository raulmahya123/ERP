<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\SiteContext;

class AssetController extends Controller
{
    /* ==========================
     |  Helpers (DRY)
     |==========================*/

    /**
     * Cek apakah user adalah GM (mendukung spatie/permission ataupun kolom role sederhana).
     */
    protected function isGM($user): bool
    {
        if (!$user) return false;

        // Spatie\Permission (jika ada)
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('gm')) return true;
        }

        // Fallback: baca relasi/kolom role
        $raw = is_object($user->role ?? null)
            ? ($user->role->key ?? $user->role->slug ?? $user->role->name ?? '')
            : (is_string($user->role ?? null) ? $user->role : '');

        $norm = Str::of($raw)->lower()->replace(['_', '-'], ' ')->squish()->toString();

        return in_array($norm, ['gm', 'general manager', 'generalmanager'], true);
    }

    /**
     * Ambil options dari master_records untuk entity tertentu,
     * diskop ke site aktif + fallback global (site_id NULL).
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
     * Rule::exists untuk memastikan nilai ada di master_records
     * milik site aktif ATAU global (site_id NULL).
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

    /**
     * INDEX — Strict per-site, semua status. Pencarian + filter status.
     */
    public function index(Request $r)
    {
        $user       = $r->user();
        $sid        = SiteContext::currentSiteId($user);
        $currentSite = method_exists(SiteContext::class, 'currentSite')
            ? SiteContext::currentSite($user)
            : null;

        $q = Asset::query()
            ->with([
                'site',
                'category',
                'costCenter',
                // Eager load untuk tabel (hindari N+1)
                'latestAssignment.toSite',
                'latestAssignment.fromSite',
                'latestAssignment.toUser',
            ])
            // STRICT PER-SITE: hanya aset milik site aktif
            ->when($sid, fn ($qq) => $qq->where('site_id', $sid));

        // Pencarian bebas
        if ($s = trim((string) $r->get('q', ''))) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('serial_no', 'like', "%{$s}%")
                  ->orWhere('plate_no', 'like', "%{$s}%");
            });
        }

        // Filter status (opsional)
        if ($st = (string) $r->get('status', '')) {
            $q->where('status', $st);
        }

        $assets = $q->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.assets.index', [
            'assets'      => $assets,
            'currentSite' => $currentSite,
            'isGM'        => $this->isGM($user), // <— dikirim ke view
        ]);
    }

    /**
     * CREATE
     */
    public function create(Request $r)
    {
        $sid         = SiteContext::currentSiteId($r->user());
        $currentSite = method_exists(SiteContext::class, 'currentSite')
            ? SiteContext::currentSite($r->user())
            : null;

        return view('admin.assets.form', [
            'asset'       => new Asset(),
            'categories'  => $this->masterOptions('asset_categories', $sid),
            'costCenters' => $this->masterOptions('cost_centers', $sid),
            'currentSite' => $currentSite,
        ]);
    }

    /**
     * STORE
     */
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
            'site_id'    => $sid, // strict per-site
            'created_by' => optional($r->user())->id,
        ];

        // Normalisasi extra: izinkan JSON string
        if (is_string($payload['extra'] ?? null)) {
            try {
                $decoded = json_decode($payload['extra'], true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $payload['extra'] = $decoded;
                }
            } catch (\Throwable $e) {
                // biarkan sebagai string
            }
        }

        $asset = Asset::create($payload);

        return redirect()->route('admin.assets.edit', $asset)->with('status', 'Asset created.');
    }

    /**
     * EDIT
     */
    public function edit(Request $r, Asset $asset)
    {
        $sid         = SiteContext::currentSiteId($r->user());
        $currentSite = method_exists(SiteContext::class, 'currentSite')
            ? SiteContext::currentSite($r->user())
            : null;

        // STRICT PER-SITE: hanya boleh mengakses aset site aktif
        if ($sid && $asset->site_id !== $sid) {
            abort(404);
        }

        return view('admin.assets.form', [
            'asset'       => $asset->load(['site', 'category', 'costCenter']),
            'categories'  => $this->masterOptions('asset_categories', $sid),
            'costCenters' => $this->masterOptions('cost_centers', $sid),
            'currentSite' => $currentSite,
        ]);
    }

    /**
     * UPDATE
     */
    public function update(Request $r, Asset $asset)
    {
        $sid = SiteContext::currentSiteId($r->user());

        // STRICT PER-SITE
        if ($sid && $asset->site_id !== $sid) {
            abort(404);
        }

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
                $decoded = json_decode($data['extra'], true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $data['extra'] = $decoded;
                }
            } catch (\Throwable $e) {
                // biarkan sebagai string
            }
        }

        $asset->update($data);

        return back()->with('status', 'Asset updated.');
    }

    /**
     * DESTROY
     */
    public function destroy(Request $r, Asset $asset)
    {
        $sid = SiteContext::currentSiteId($r->user());

        // STRICT PER-SITE
        if ($sid && $asset->site_id !== $sid) {
            abort(404);
        }

        $asset->delete();

        return redirect()->route('admin.assets.index')->with('status', 'Asset deleted.');
    }

    /**
     * BULK DELETE
     */
    public function bulkDelete(Request $r)
    {
        $sid = SiteContext::currentSiteId($r->user());

        $data = $r->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['uuid', 'exists:assets,id'],
        ]);

        // STRICT PER-SITE: hapus hanya milik site aktif
        $count = Asset::query()
            ->when($sid, fn($q) => $q->where('site_id', $sid))
            ->whereIn('id', $data['ids'])
            ->delete();

        return redirect()
            ->route('admin.assets.index')
            ->with('status', "{$count} aset dihapus.");
    }
}
