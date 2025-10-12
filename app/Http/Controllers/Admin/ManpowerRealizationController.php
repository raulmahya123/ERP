<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManpowerRealization;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManpowerRealizationController extends Controller
{
    /** Slot shift baku */
    private array $slots = ['A','B','C','D','NON'];

    /* ===========================
     * Helpers: Active Site / Resolve Site / LIKE operator (ORM only)
     * =========================== */
    private function resolveActiveSiteId(Request $r): ?string
    {
        if ($r->filled('site_id')) {
            $sid = (string) $r->input('site_id');
            session(['site_id' => $sid]);
            return $sid;
        }
        if (session()->has('site_id')) {
            return (string) session('site_id');
        }
        $userSid = optional($r->user())->site_id;
        if ($userSid) {
            session(['site_id' => (string) $userSid]);
            return (string) $userSid;
        }
        $first = Site::orderBy('name')->value('id');
        if ($first) {
            session(['site_id' => (string) $first]);
            return (string) $first;
        }
        return null;
    }

    /** Coba resolve site dari site_id / site_code / site_name; fallback ke active site */
    private function resolveSiteIdFromRequest(Request $r): ?string
    {
        if ($r->filled('site_id')) return (string) $r->input('site_id');

        if ($r->filled('site_code')) {
            $id = Site::where('code', $r->input('site_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('site_name')) {
            $term = $r->input('site_name');
            // ORM-only, no raw:
            $op = $this->likeOp();
            $id = Site::where('name', $op, "%{$term}%")
                ->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return $this->resolveActiveSiteId($r);
    }

    /** Pilih operator LIKE yang case-insensitive untuk PgSQL (ILIKE) tanpa raw */
    private function likeOp(): string
    {
        $conn = config('database.default');
        $driver = config("database.connections.{$conn}.driver");
        return $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
    }

    /* ===========================
     * INDEX
     * =========================== */
    public function index(Request $r)
    {
        $perPage       = max(1, min(200, (int) $r->input('per_page', 25)));
        $activeSiteId  = $this->resolveActiveSiteId($r);
        $op            = $this->likeOp();

        $q = ManpowerRealization::query()
            ->when($activeSiteId, fn(Builder $qq, $sid) => $qq->where('site_id', $sid))
            ->when($r->date, fn(Builder $qq, $d) => $qq->whereDate('date', $d))
            ->when($r->shift_slot, fn(Builder $qq, $s) => $qq->where('shift_slot', $s))
            ->when($r->filled('department'), fn(Builder $qq) => $qq->where('department', $op, '%'.request('department').'%'))
            // fulltext ringan via ORM (tanpa raw)
            ->when($r->filled('q'), function (Builder $qq) use ($op, $r) {
                $term = $r->input('q');
                $qq->where(function (Builder $w) use ($op, $term) {
                    $w->where('department', $op, "%{$term}%")
                      ->orWhere('shift_slot', $op, "%{$term}%");
                });
            })
            ->orderByDesc('date')
            ->orderBy('department');

        $reals = $q->paginate($perPage)->withQueryString();

        if (! $r->wantsJson()) {
            $shiftSlots = $this->slots;
            $sites      = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.manpower_reals.index', compact('reals','shiftSlots','sites','activeSiteId'));
        }

        return response()->json($reals);
    }

    /* ===========================
     * CREATE
     * =========================== */
    public function create()
    {
        $shiftSlots = $this->slots;
        return view('admin.manpower_reals.create', compact('shiftSlots'));
    }

    /* ===========================
     * STORE
     * =========================== */
    public function store(Request $r)
    {
        // Auto-resolve site jika tidak kirim UUID (ORM only)
        $resolvedSiteId = $this->resolveSiteIdFromRequest($r);
        if ($resolvedSiteId && !$r->filled('site_id')) {
            $r->merge(['site_id' => $resolvedSiteId]);
        }

        $data = $r->validate([
            'site_id'            => ['required','uuid'],
            'date'               => ['required','date'],
            'shift_slot'         => ['required','in:'.implode(',', $this->slots)],
            'department'         => ['required','string','max:50'],
            'actual_headcount'   => ['required','integer','min:0'],
            'actual_operators'   => ['nullable','integer','min:0'],
            'actual_mechanics'   => ['nullable','integer','min:0'],
            'actual_helpers'     => ['nullable','integer','min:0'],
            'actual_others'      => ['nullable','integer','min:0'],
            'production_tonnage' => ['nullable','numeric','min:0'],
            'manhours'           => ['nullable','numeric','min:0'],
            'meta'               => ['nullable','array'],
        ]);

        $data['id'] = (string) Str::uuid();

        ManpowerRealization::updateOrCreate(
            [
                'site_id'    => $data['site_id'],
                'date'       => $data['date'],
                'shift_slot' => $data['shift_slot'],
                'department' => $data['department'],
            ],
            collect($data)->except(['site_id','date','shift_slot','department'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.manpower-reals.index')->with('success','Realisasi manpower disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    /* ===========================
     * EDIT
     * =========================== */
    public function edit(ManpowerRealization $manpowerRealization)
    {
        $shiftSlots = $this->slots;
        return view('admin.manpower_reals.edit', ['real'=>$manpowerRealization,'shiftSlots'=>$shiftSlots]);
    }

    /* ===========================
     * UPDATE
     * =========================== */
    public function update(Request $r, ManpowerRealization $manpowerRealization)
    {
        $data = $r->validate([
            'actual_headcount'   => ['sometimes','integer','min:0'],
            'actual_operators'   => ['sometimes','integer','min:0'],
            'actual_mechanics'   => ['sometimes','integer','min:0'],
            'actual_helpers'     => ['sometimes','integer','min:0'],
            'actual_others'      => ['sometimes','integer','min:0'],
            'production_tonnage' => ['sometimes','numeric','min:0'],
            'manhours'           => ['sometimes','numeric','min:0'],
            'meta'               => ['nullable','array'],
        ]);

        $manpowerRealization->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Realisasi manpower diperbarui.');
        }

        return response()->json($manpowerRealization->refresh());
    }

    /* ===========================
     * DESTROY
     * =========================== */
    public function destroy(Request $r, ManpowerRealization $manpowerRealization)
    {
        $manpowerRealization->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Realisasi manpower dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
