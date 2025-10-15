<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftRoster;
use App\Models\Site;
use App\Models\User;
use App\Models\Location; // ⬅️ tambahkan ini
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

    /** Resolve site_id dari parameter lokasi (location_*) bila ada */
    private function resolveSiteIdFromLocation(Request $r): ?string
    {
        // Prioritas: location_id (UUID) → location_code → location_name (LIKE)
        if ($r->filled('location_id')) {
            $loc = Location::query()->select('site_id')->find($r->input('location_id'));
            if ($loc?->site_id) return (string) $loc->site_id;
        }

        if ($r->filled('location_code')) {
            $loc = Location::query()->select('site_id')
                ->where('code', $r->input('location_code'))
                ->first();
            if ($loc?->site_id) return (string) $loc->site_id;
        }

        if ($r->filled('location_name')) {
            $term = (string) $r->input('location_name');
            $loc = Location::query()->select('site_id')
                ->where('name', 'LIKE', "%{$term}%")
                ->orderBy('name')
                ->first();
            if ($loc?->site_id) return (string) $loc->site_id;
        }

        return null;
    }

    /** Coba resolve site_id dari: location_* → site_id → site_code → site_name → active site */
    private function resolveSiteIdFromRequest(Request $r): ?string
    {
        // 1) Lokasi (paling “manusiawi”) → site_id
        if ($sid = $this->resolveSiteIdFromLocation($r)) {
            return $sid;
        }

        // 2) Langsung site_id (UUID)
        if ($r->filled('site_id')) return (string) $r->input('site_id');

        // 3) site_code
        if ($r->filled('site_code')) {
            $id = Site::query()->where('code', $r->input('site_code'))->value('id');
            if ($id) return (string) $id;
        }

        // 4) site_name (LIKE, case-insensitive by collation)
        if ($r->filled('site_name')) {
            $term = (string) $r->input('site_name');
            $id = Site::query()->where('name', 'LIKE', "%{$term}%")
                ->orderBy('name')
                ->value('id');
            if ($id) return (string) $id;
        }

        // 5) fallback ke active site
        return $this->resolveActiveSiteId($r);
    }

    /** Coba resolve user_id dari user_id / employee_code / user_name */
    private function resolveUserIdFromRequest(Request $r): ?string
    {
        if ($r->filled('user_id')) return (string) $r->input('user_id');

        if ($r->filled('employee_code')) {
            $id = User::query()->where('employee_code', $r->input('employee_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('user_name')) {
            $term = (string) $r->input('user_name');
            $id = User::query()
                ->where('name', 'LIKE', "%{$term}%")
                ->orderBy('name')
                ->value('id');
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
            // Kunci site default (active). Bisa ditimpa via ?site_id / ?site_code / ?site_name / ?location_name
            ->when($sid = $this->resolveSiteIdFromRequest($r), fn ($qb) => $qb->where('site_id', $sid))

            // tanggal
            ->when($r->filled('date'), fn ($qb) => $qb->whereDate('roster_date', $r->input('date')))

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
                    $qb->whereHas('user', function (Builder $uq) use ($u) {
                        $uq->where('name', 'LIKE', "%{$u}%")
                           ->orWhere('employee_code', 'LIKE', "%{$u}%");
                    });
                }
            })

            // USER alternatif: param "user" (nama/kode)
            ->when($r->filled('user'), function (Builder $qb) use ($r) {
                $term = (string) $r->input('user');
                $qb->whereHas('user', function (Builder $uq) use ($term) {
                    $uq->where('name', 'LIKE', "%{$term}%")
                       ->orWhere('employee_code', 'LIKE', "%{$term}%");
                });
            })

            // pencarian ringan
            ->when($r->filled('q'), function (Builder $qb) use ($r) {
                $term = (string) $r->input('q');
                $qb->where(function (Builder $w) use ($term) {
                    $w->where('crew_code', 'LIKE', "%{$term}%")
                      ->orWhere('remarks', 'LIKE', "%{$term}%")
                      ->orWhereHas('shift', fn ($sq) => $sq->where('name', 'LIKE', "%{$term}%"))
                      ->orWhereHas('user', function ($uq) use ($term) {
                          $uq->where('name', 'LIKE', "%{$term}%")
                             ->orWhere('employee_code', 'LIKE', "%{$term}%");
                      })
                      ->orWhereHas('site', fn ($sq) => $sq->where('name', 'LIKE', "%{$term}%"));
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
        // Auto-resolve site & user dari input "manusiawi"
        if ($sid = $this->resolveSiteIdFromRequest($r)) {
            $r->merge(['site_id' => $sid]);
        }
        if ($uid = $this->resolveUserIdFromRequest($r)) {
            $r->merge(['user_id' => $uid]);
        }

        $data = $r->validate([
            'site_id'     => ['required','uuid'],
            'user_id'     => ['required','uuid'],
            'roster_date' => ['required','date'],
            'shift_id'    => ['nullable','uuid'],
            'crew_code'   => ['nullable','string','max:20'],
            'remarks'     => ['nullable','string','max:255'],
        ]);

        // Pakai “natural key” (site_id, user_id, roster_date). Biarkan PK id tetap UUID by model.
        $roster = ShiftRoster::updateOrCreate(
            [
                'site_id'     => $data['site_id'],
                'user_id'     => $data['user_id'],
                'roster_date' => $data['roster_date'],
            ],
            collect($data)->except(['site_id','user_id','roster_date'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.shift-rosters.index')->with('success','Roster shift disimpan.');
        }

        return response()->json(['ok'=>true, 'id' => $roster->id]);
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
