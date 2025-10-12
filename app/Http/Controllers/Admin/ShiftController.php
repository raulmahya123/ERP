<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
    /** Resolusi site aktif: request → session → user → first site */
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

    public function index(Request $r)
    {
        $perPage      = max(1, min(200, (int) $r->input('per_page', 50)));
        $activeSiteId = $this->resolveActiveSiteId($r);

        $q = Shift::query()
            ->when($activeSiteId, fn($qb, $sid) => $qb->where('site_id', $sid))
            ->when($r->filled('q'), function ($qb) use ($r) {
                $term = strtolower((string) $r->input('q'));
                $qb->where(function ($w) use ($term) {
                    $w->whereRaw('LOWER(code) like ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(name) like ?', ["%{$term}%"]);
                });
            })
            ->orderBy('code');

        // UI → view, API → JSON (paginated)
        if (! $r->wantsJson()) {
            $shifts = $q->paginate($perPage)->withQueryString();
            $sites  = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.shifts.index', compact('shifts','sites','activeSiteId'));
        }

        return response()->json($q->paginate($perPage));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $r)
    {
        // Pakai site aktif kalau site_id tidak dikirim
        $siteId = $r->input('site_id') ?: $this->resolveActiveSiteId($r);
        if ($siteId && !$r->filled('site_id')) {
            $r->merge(['site_id' => $siteId]);
        }

        $data = $r->validate([
            'site_id'       => ['required','uuid'],
            'code'          => ['required','string','max:20'],
            'name'          => ['required','string','max:50'],
            'start_at'      => ['required','date_format:H:i'],
            'end_at'        => ['required','date_format:H:i'],
            'break_minutes' => ['nullable','integer','min:0'],
            'overnight'     => ['boolean'],
            'meta'          => ['nullable','array'],
        ]);

        $data['id'] = (string) Str::uuid();

        Shift::updateOrCreate(
            ['site_id' => $data['site_id'], 'code' => $data['code']],
            collect($data)->except(['id','site_id','code'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.shifts.index')->with('success','Shift disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $r, Shift $shift)
    {
        $data = $r->validate([
            'name'          => ['sometimes','string','max:50'],
            'start_at'      => ['sometimes','date_format:H:i'],
            'end_at'        => ['sometimes','date_format:H:i'],
            'break_minutes' => ['nullable','integer','min:0'],
            'overnight'     => ['boolean'],
            'meta'          => ['nullable','array'],
        ]);

        $shift->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Shift diperbarui.');
        }

        return response()->json($shift->refresh());
    }

    public function destroy(Request $r, Shift $shift)
    {
        $shift->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Shift dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
