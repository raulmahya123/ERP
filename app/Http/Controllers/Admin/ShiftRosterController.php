<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftRoster;
use App\Models\Site;
use App\Models\User;
use App\Models\Location;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
        if ($sid = $this->resolveSiteIdFromLocation($r)) return $sid;
        if ($r->filled('site_id')) return (string) $r->input('site_id');

        if ($r->filled('site_code')) {
            $id = Site::query()->where('code', $r->input('site_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('site_name')) {
            $term = (string) $r->input('site_name');
            $id = Site::query()->where('name', 'LIKE', "%{$term}%")
                ->orderBy('name')
                ->value('id');
            if ($id) return (string) $id;
        }

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
                'shift:id,name,code,site_id',
                'site:id,code,name',
            ])
            ->when($sid = $this->resolveSiteIdFromRequest($r), fn($qb) => $qb->where('site_id', $sid))
            ->when($r->filled('date'), fn($qb) => $qb->whereDate('roster_date', $r->input('date')))
            ->when($r->filled('user_id'), function (Builder $qb) use ($r) {
                $u = trim((string) $r->input('user_id'));
                $isUuid = (bool) preg_match('/^[0-9a-fA-F-]{36}$/', $u);
                if ($isUuid) {
                    $qb->where('user_id', $u);
                } else {
                    $qb->whereHas('user', function (Builder $uq) use ($u) {
                        $uq->where('name', 'LIKE', "%{$u}%")
                           ->orWhere('employee_code', 'LIKE', "%{$u}%");
                    });
                }
            })
            ->when($r->filled('user'), function (Builder $qb) use ($r) {
                $term = (string) $r->input('user');
                $qb->whereHas('user', function (Builder $uq) use ($term) {
                    $uq->where('name', 'LIKE', "%{$term}%")
                       ->orWhere('employee_code', 'LIKE', "%{$term}%");
                });
            })
            ->when($r->filled('q'), function (Builder $qb) use ($r) {
                $term = (string) $r->input('q');
                $qb->where(function (Builder $w) use ($term) {
                    $w->where('crew_code', 'LIKE', "%{$term}%")
                      ->orWhere('remarks', 'LIKE', "%{$term}%")
                      ->orWhereHas('shift', fn($sq) => $sq->where('name', 'LIKE', "%{$term}%"))
                      ->orWhereHas('user', function ($uq) use ($term) {
                          $uq->where('name', 'LIKE', "%{$term}%")
                             ->orWhere('employee_code', 'LIKE', "%{$term}%");
                      })
                      ->orWhereHas('site', fn($sq) => $sq->where('name', 'LIKE', "%{$term}%"));
                });
            })
            ->orderByDesc('roster_date');

        if (! $r->wantsJson()) {
            $rosters = $q->paginate($perPage)->withQueryString();
            $sites   = Site::orderBy('name')->get(['id', 'code', 'name']);
            return view('admin.shift_rosters.index', compact('rosters', 'sites', 'activeSiteId'));
        }

        return response()->json($q->paginate($perPage));
    }

    /** FORM CREATE – kirim dropdown sites, shifts (by site), dan users (tanpa asumsi users.site_id) */
    public function create(Request $r)
    {
        $activeSiteId = $this->resolveActiveSiteId($r);

        $sites  = Site::orderBy('name')->get(['id', 'code', 'name']);

        $shifts = Shift::query()
            ->when($activeSiteId, fn($q) => $q->where('site_id', $activeSiteId))
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        // ⚠️ TIDAK asumsikan kolom users.site_id (avoid error 1054)
        $usersQ = User::query()->select('id','name','employee_code')->orderBy('name');

        // Kalau nanti suatu saat ada kolom users.site_id, filter akan otomatis aktif.
        if ($activeSiteId && Schema::hasColumn('users','site_id')) {
            $usersQ->where('site_id', $activeSiteId);
        }
        $users = $usersQ->limit(200)->get();

        return view('admin.shift_rosters.create', [
            'sites'        => $sites,
            'shifts'       => $shifts,
            'users'        => $users,
            'activeSiteId' => $activeSiteId,
        ]);
    }

    public function store(Request $r)
    {
        // Auto-resolve dari input “manusiawi”
        if ($sid = $this->resolveSiteIdFromRequest($r)) {
            $r->merge(['site_id' => $sid]);
        }
        if ($uid = $this->resolveUserIdFromRequest($r)) {
            $r->merge(['user_id' => $uid]);
        }

        $data = $r->validate([
            'site_id'     => ['required', 'uuid'],
            'user_id'     => ['required', 'uuid'],
            'roster_date' => ['required', 'date'],
            'shift_id'    => ['nullable', 'uuid'],
            'crew_code'   => ['nullable', 'string', 'max:20'],
            'remarks'     => ['nullable', 'string', 'max:255'],
        ]);

        // Natural key: (site_id, user_id, roster_date)
        $roster = ShiftRoster::updateOrCreate(
            [
                'site_id'     => $data['site_id'],
                'user_id'     => $data['user_id'],
                'roster_date' => $data['roster_date'],
            ],
            collect($data)->except(['site_id', 'user_id', 'roster_date'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.shift-rosters.index')->with('success', 'Roster shift disimpan.');
        }

        return response()->json(['ok' => true, 'id' => $roster->id]);
    }

    public function edit(ShiftRoster $shiftRoster)
    {
        $sites = Site::orderBy('name')->get(['id', 'code', 'name']);
        $shifts = Shift::query()
            ->when($shiftRoster->site_id, fn($q) => $q->where('site_id', $shiftRoster->site_id))
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'site_id']);

        return view('admin.shift_rosters.edit', [
            'roster' => $shiftRoster,
            'sites'  => $sites,
            'shifts' => $shifts,
        ]);
    }

    public function update(Request $r, ShiftRoster $shiftRoster)
    {
        $data = $r->validate([
            'shift_id'  => ['nullable', 'uuid'],
            'crew_code' => ['nullable', 'string', 'max:20'],
            'remarks'   => ['nullable', 'string', 'max:255'],
        ]);

        $shiftRoster->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success', 'Roster shift diperbarui.');
        }

        return response()->json($shiftRoster->refresh());
    }

    public function destroy(Request $r, ShiftRoster $shiftRoster)
    {
        $shiftRoster->delete();

        if (! $r->wantsJson()) {
            return back()->with('success', 'Roster shift dihapus.');
        }

        return response()->json(['ok' => true]);
    }

    /** AJAX: ambil shifts per site untuk dropdown dinamis */
    public function shiftsBySite(Request $r)
    {
        $siteId = (string) $r->input('site_id');
        $shifts = Shift::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'site_id']);

        return response()->json($shifts);
    }

    /** (Opsional) AJAX: autocomplete users tanpa asumsi users.site_id */
    public function usersOptions(Request $r)
    {
        $term   = trim((string) $r->input('q', ''));
        $siteId = (string) $r->input('site_id', '');

        $q = User::query()->select('id','name','employee_code');

        if ($term !== '') {
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                  ->orWhere('employee_code', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // Filter by site hanya jika kolom users.site_id memang ada
        if ($siteId && Schema::hasColumn('users','site_id')) {
            $q->where('site_id', $siteId);
        }

        $users = $q->orderBy('name')->limit(50)->get();

        // Format sederhana untuk select2/HTMX dsb
        return response()->json($users->map(function ($u) {
            $label = trim($u->name . ($u->employee_code ? " ({$u->employee_code})" : ''));
            return ['id' => $u->id, 'text' => $label];
        }));
    }
}
