<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftRoster;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftRosterController extends Controller
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

    /** Coba resolve site_id dari site_id / site_code / site_name; fallback active site */
    private function resolveSiteIdFromRequest(Request $r): ?string
    {
        if ($r->filled('site_id')) return (string) $r->input('site_id');

        if ($r->filled('site_code')) {
            $id = Site::where('code', $r->input('site_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('site_name')) {
            $term = Str::lower($r->input('site_name'));
            $id = Site::whereRaw('LOWER(name) like ?', ["%{$term}%"])
                ->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return $this->resolveActiveSiteId($r);
    }

    /** Coba resolve user_id dari user_id / employee_code / user_name */
    private function resolveUserIdFromRequest(Request $r): ?string
    {
        if ($r->filled('user_id')) return (string) $r->input('user_id');

        if ($r->filled('employee_code')) {
            $id = User::where('employee_code', $r->input('employee_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('user_name')) {
            $term = Str::lower($r->input('user_name'));
            $id = User::whereRaw('LOWER(name) like ?', ["%{$term}%"])
                ->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return null;
    }

    public function index(Request $r)
    {
        $perPage      = max(1, min(200, (int) $r->input('per_page', 25)));
        $activeSiteId = $this->resolveActiveSiteId($r);

        $q = ShiftRoster::query()
            ->with([
                'user:id,name,employee_code',
                'shift:id,name',
                'site:id,code,name',
            ])
            // site dikunci (active)
            ->when($activeSiteId, fn ($qb, $sid) => $qb->where('site_id', $sid))

            // tanggal
            ->when($r->date, fn ($qb, $d) => $qb->whereDate('roster_date', $d))

            // USER: dukung UUID atau nama/kode pada param user_id
            ->when($r->filled('user_id'), function (Builder $qb) use ($r) {
                $u = trim((string) $r->input('user_id'));
                $isUuid = preg_match(
                    '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                    $u
                );
                if ($isUuid) {
                    $qb->where('user_id', $u);
                } else {
                    $term = Str::lower($u);
                    $qb->whereHas('user', function (Builder $uq) use ($term) {
                        $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                           ->orWhereRaw('LOWER(employee_code) like ?', ["%{$term}%"]);
                    });
                }
            })

            // USER alternatif: param "user" (nama/kode)
            ->when($r->filled('user'), function (Builder $qb) use ($r) {
                $term = Str::lower($r->input('user'));
                $qb->whereHas('user', function (Builder $uq) use ($term) {
                    $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                       ->orWhereRaw('LOWER(employee_code) like ?', ["%{$term}%"]);
                });
            })

            // pencarian ringan (crew_code / remarks / shift name / user name)
            ->when($r->filled('q'), function (Builder $qb) use ($r) {
                $term = Str::lower($r->q);
                $qb->where(function (Builder $w) use ($term) {
                    $w->whereRaw('LOWER(crew_code) like ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(remarks) like ?', ["%{$term}%"])
                      ->orWhereHas('shift', fn ($sq) => $sq->whereRaw('LOWER(name) like ?', ["%{$term}%"]))
                      ->orWhereHas('user', fn ($uq)  => $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                                                      ->orWhereRaw('LOWER(employee_code) like ?', ["%{$term}%"]));
                });
            })
            ->orderByDesc('roster_date');

        // UI → view, API → JSON
        if (! $r->wantsJson()) {
            $rosters = $q->paginate($perPage)->withQueryString();
            $sites   = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.shift_rosters.index', compact('rosters','sites','activeSiteId'));
        }

        return response()->json($q->paginate($perPage));
    }

    public function create()
    {
        return view('admin.shift_rosters.create');
    }

    public function store(Request $r)
    {
        // Auto-resolve site & user kalau tidak kirim UUID
        $resolvedSiteId = $this->resolveSiteIdFromRequest($r);
        if ($resolvedSiteId && !$r->filled('site_id')) {
            $r->merge(['site_id' => $resolvedSiteId]);
        }

        $resolvedUserId = $this->resolveUserIdFromRequest($r);
        if ($resolvedUserId && !$r->filled('user_id')) {
            $r->merge(['user_id' => $resolvedUserId]);
        }

        $data = $r->validate([
            'site_id'     => ['required','uuid'],
            'user_id'     => ['required','uuid'],
            'roster_date' => ['required','date'],
            'shift_id'    => ['nullable','uuid'],
            'crew_code'   => ['nullable','string','max:20'],
            'remarks'     => ['nullable','string','max:255'],
        ]);

        $data['id'] = (string) Str::uuid();

        ShiftRoster::updateOrCreate(
            [
                'site_id'     => $data['site_id'],
                'user_id'     => $data['user_id'],
                'roster_date' => $data['roster_date'],
            ],
            collect($data)->except(['id','site_id','user_id','roster_date'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.shift-rosters.index')->with('success','Roster shift disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(ShiftRoster $shiftRoster)
    {
        return view('admin.shift_rosters.edit', ['roster'=>$shiftRoster]);
    }

    public function update(Request $r, ShiftRoster $shiftRoster)
    {
        $data = $r->validate([
            'shift_id'  => ['nullable','uuid'],
            'crew_code' => ['nullable','string','max:20'],
            'remarks'   => ['nullable','string','max:255'],
        ]);

        $shiftRoster->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Roster shift diperbarui.');
        }

        return response()->json($shiftRoster->refresh());
    }

    public function destroy(Request $r, ShiftRoster $shiftRoster)
    {
        $shiftRoster->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Roster shift dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
