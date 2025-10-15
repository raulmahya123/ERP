<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
    /** Resolve active site: request → session → user → first site */
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
            // filter by active site when available
            ->when($activeSiteId, fn($qb) => $qb->where('site_id', $activeSiteId))
            // quick search
            ->when($r->filled('q'), function ($qb) use ($r) {
                $term = strtolower((string) $r->input('q'));
                $qb->where(function ($w) use ($term) {
                    $w->whereRaw('LOWER(code) like ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(name) like ?', ["%{$term}%"]);
                });
            })
            ->orderBy('code');

        if (! $r->wantsJson()) {
            $shifts = $q->paginate($perPage)->withQueryString();
            $sites  = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.shifts.index', compact('shifts','sites','activeSiteId'));
        }

        return response()->json($q->paginate($perPage));
    }

    public function create(Request $r)
    {
        $activeSiteId = $this->resolveActiveSiteId($r);
        $sites        = Site::orderBy('name')->get(['id','code','name']);
        // pass data so the Blade can show locked site label or dropdown
        return view('admin.shifts.create', compact('sites','activeSiteId'));
    }

    public function store(Request $r)
    {
        // Use active site if site_id not posted
        $siteId = $r->input('site_id') ?: $this->resolveActiveSiteId($r);
        if ($siteId && ! $r->filled('site_id')) {
            $r->merge(['site_id' => $siteId]);
        }

        // normalize checkbox & meta_json → meta
        $payload = $r->all();
        $payload['overnight'] = $r->boolean('overnight');
        if ($r->filled('meta_json') && ! $r->filled('meta')) {
            // if you post meta_json from form, you can JSON.parse on client;
            // this server fallback accepts stringified JSON as well.
            $decoded = json_decode((string) $r->input('meta_json'), true);
            if (is_array($decoded)) $payload['meta'] = $decoded;
        }
        $r->replace($payload);

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

        // ensure stable upsert by (site_id, code)
        Shift::updateOrCreate(
            ['site_id' => $data['site_id'], 'code' => $data['code']],
            collect($data)->except(['site_id','code'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.shifts.index')->with('success','Shift disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(Request $r, Shift $shift)
    {
        // supply sites & activeSiteId for UI consistency (locked display / dropdown)
        $sites        = Site::orderBy('name')->get(['id','code','name']);
        $activeSiteId = $shift->site_id ?: $this->resolveActiveSiteId($r);
        return view('admin.shifts.edit', compact('shift','sites','activeSiteId'));
    }

    public function update(Request $r, Shift $shift)
    {
        // normalize checkbox & meta_json → meta
        $payload = $r->all();
        if ($r->has('overnight')) {
            $payload['overnight'] = $r->boolean('overnight');
        }
        if ($r->filled('meta_json') && ! $r->filled('meta')) {
            $decoded = json_decode((string) $r->input('meta_json'), true);
            if (is_array($decoded)) $payload['meta'] = $decoded;
        }
        $r->replace($payload);

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
