<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scm\{StoreDispatchRequest, UpdateDispatchRequest};
use App\Models\Scm\DispatchAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    public function index(Request $r)
    {
        $siteId = (string) session('site_id');
        $q = DispatchAllocation::where('site_id', $siteId)
            ->when($r->filled('date'), fn($w) => $w->where('work_date', $r->date))
            ->when($r->filled('shift_id'), fn($w) => $w->where('shift_id', $r->shift_id))
            ->when($r->filled('pit_id'), fn($w) => $w->where('pit_id', $r->pit_id))
            ->orderByDesc('work_date')->orderBy('shift_id');

        $items = $q->paginate(20)->withQueryString();
        return view('admin.scm.dispatches.index', compact('items'));
    }
    public function store(StoreDispatchRequest $req)
    {
        $siteId = (string) session('site_id');
        DispatchAllocation::create(array_merge($req->validated(), ['site_id' => $siteId]));
        return redirect()->route('scm.dispatches.index')->with('ok', 'Dispatch dibuat.');
    }

    public function create()
    {
        $siteId = (string) session('site_id');
        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $pits   = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();
        $assets = DB::table('assets')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();
        $operators = DB::table('users')->select('id', 'name', 'email')->orderBy('name')->limit(500)->get();

        return view('admin.scm.dispatches.form', [
            'item' => $item ?? new \App\Models\Scm\DispatchAllocation(),
            'shifts' => $shifts,
            'pits' => $pits,
            'assets' => $assets,
            'operators' => $operators
        ]);
    }

    public function edit(string $id)
    {
        $siteId = (string) session('site_id');
        $item = DispatchAllocation::where('site_id', $siteId)->findOrFail($id);

        $shifts   = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $pits     = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();
        $assets   = DB::table('assets')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();
        $operators = DB::table('users')->select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.scm.dispatches.form', compact('item', 'shifts', 'pits', 'assets', 'operators'));
    }
    public function update(UpdateDispatchRequest $req, string $id)
    {
        $item = DispatchAllocation::where('site_id', session('site_id'))->findOrFail($id);
        $item->update($req->validated());
        return redirect()->route('scm.dispatches.index')->with('ok', 'Dispatch diupdate.');
    }

    public function destroy(string $id)
    {
        $item = DispatchAllocation::where('site_id', session('site_id'))->findOrFail($id);
        $item->delete();
        return back()->with('ok', 'Dispatch dihapus.');
    }
}
