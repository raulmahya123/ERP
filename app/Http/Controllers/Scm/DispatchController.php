<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scm\{StoreDispatchRequest, UpdateDispatchRequest};
use App\Models\Scm\DispatchAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    private function currentSiteId(): ?string
    {
        $sid = session('site_id') ?: auth()->user()?->default_site_id;
        if (!$sid) {
            $sid = DB::table('sites')->value('id'); // fallback: ambil site pertama
            if ($sid) {
                session(['site_id' => $sid]);
            }
        }
        return $sid;
    }

    public function index(Request $r)
    {
        $siteId = (string) session('site_id');

        $q = \App\Models\Scm\DispatchAllocation::from('scm_dispatch_allocations as da')
            ->where('da.site_id', $siteId)
            ->when($r->filled('date'), fn($w) => $w->whereDate('da.work_date', $r->date))
            ->when($r->filled('shift_id'), fn($w) => $w->where('da.shift_id', $r->shift_id))
            ->when($r->filled('pit_id'),   fn($w) => $w->where('da.pit_id',   $r->pit_id))
            ->leftJoin('shifts as s', 's.id', '=', 'da.shift_id')
            ->leftJoin('pits as p', function ($j) use ($siteId) {
                $j->on('p.id', '=', 'da.pit_id')->where('p.site_id', '=', $siteId);
            })
            // ⬇️ HANYA join by id; jangan difilter site supaya tetap kebaca
            ->leftJoin('assets as a', 'a.id', '=', 'da.asset_id')
            ->leftJoin('users as u', 'u.id', '=', 'da.operator_id')
            ->select([
                'da.*',
                's.name  as shift_name',
                'p.code  as pit_code',
                'p.name  as pit_name',
                'a.code  as asset_code',
                'a.name  as asset_name',
                'u.name  as operator_name',
                // flag apakah asset masih satu site
                DB::raw("CASE WHEN a.site_id = '{$siteId}' THEN 1 ELSE 0 END as asset_in_site"),
            ])
            ->orderByDesc('da.work_date')
            ->orderBy('da.shift_id');

        $items = $q->paginate(20)->withQueryString();

        return view('admin.scm.dispatches.index', compact('items'));
    }

    public function create()
    {
        $siteId = (string) $this->currentSiteId();

        $shifts = DB::table('shifts')
            ->select('id', 'name')
            ->orderBy('name')->get();

        $pits = DB::table('pits')
            ->where('site_id', $siteId)
            ->select('id', 'code', 'name')
            ->orderBy('code')->get();

        $assets = DB::table('assets')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->select('id', 'code', 'name')
            ->orderBy('code')->limit(500)->get();

        // DEV fallback: jika kosong saat environment local, tampilkan semua supaya tidak buntu saat testing
        if ($assets->isEmpty() && app()->isLocal()) {
            $assets = DB::table('assets')->select('id', 'code', 'name')->orderBy('code')->limit(500)->get();
        }

        $operators = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')->limit(500)->get();

        return view('admin.scm.dispatches.form', [
            'item'      => new DispatchAllocation(),
            'shifts'    => $shifts,
            'pits'      => $pits,
            'assets'    => $assets,
            'operators' => $operators,
        ]);
    }

    public function edit(string $id)
    {
        $siteId = (string) $this->currentSiteId();

        $item = DispatchAllocation::where('site_id', $siteId)->findOrFail($id);

        $shifts = DB::table('shifts')
            ->select('id', 'name')
            ->orderBy('name')->get();

        $pits = DB::table('pits')
            ->where('site_id', $siteId)
            ->select('id', 'code', 'name')
            ->orderBy('code')->get();

        $assets = DB::table('assets')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->select('id', 'code', 'name')
            ->orderBy('code')->limit(500)->get();

        if ($assets->isEmpty() && app()->isLocal()) {
            $assets = DB::table('assets')->select('id', 'code', 'name')->orderBy('code')->limit(500)->get();
        }

        $operators = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')->limit(500)->get();

        return view('admin.scm.dispatches.form', compact('item', 'shifts', 'pits', 'assets', 'operators'));
    }

    public function store(StoreDispatchRequest $req)
    {
        $siteId = (string) $this->currentSiteId();
        DispatchAllocation::create(array_merge($req->validated(), ['site_id' => $siteId]));
        return redirect()->route('scm.dispatches.index')->with('ok', 'Dispatch dibuat.');
    }

    public function update(UpdateDispatchRequest $req, string $id)
    {
        $item = DispatchAllocation::where('site_id', $this->currentSiteId())->findOrFail($id);
        $item->update($req->validated());
        return redirect()->route('scm.dispatches.index')->with('ok', 'Dispatch diupdate.');
    }

    public function destroy(string $id)
    {
        $item = DispatchAllocation::where('site_id', $this->currentSiteId())->findOrFail($id);
        $item->delete();
        return back()->with('ok', 'Dispatch dihapus.');
    }
}
