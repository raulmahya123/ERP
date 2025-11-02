<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
    public function index(Request $r)
    {
        $siteId = (string) session('site_id');

        $q = Trip::where('site_id', $siteId)
            ->when($r->filled('status'), fn($w) => $w->where('status', $r->status))
            ->latest('date');

        $trips = $q->paginate(15)->withQueryString();

        // label map untuk hindari tampil UUID
        $shiftNames = DB::table('shifts')->pluck('name', 'id');
        $assetNames = DB::table('assets')
            ->where(function ($qq) use ($siteId) {
                $qq->where('site_id', $siteId)->orWhereNull('site_id');
            })
            ->select('id', DB::raw("CONCAT(code,' — ',name) as label"))
            ->pluck('label', 'id');
        $pitLabels = DB::table('pits')
            ->where('site_id', $siteId)
            ->select('id', DB::raw("CONCAT(COALESCE(code,'PIT'),' — ',COALESCE(name,'')) as label"))
            ->pluck('label', 'id');

        return view('admin.scm.trips.index', compact('trips', 'shiftNames', 'assetNames', 'pitLabels'));
    }


    public function create()
    {
        $siteId = (string) session('site_id');

        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        // ➜ ambil asset milik site aktif saja
        $assets = DB::table('assets')
            ->where('site_id', $siteId)
            ->select('id', 'code', 'name')->orderBy('code')->get();

        $commodities = DB::table('commodities')->select('id', 'name')->orderBy('name')->get();
        $pits = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();

        return view('admin.scm.trips.form', [
            'trip' => new \App\Models\Scm\Trip(),
            'shifts' => $shifts,
            'assets' => $assets,
            'commodities' => $commodities,
            'pits' => $pits,
        ]);
    }
    public function store(Request $r)
    {
        $this->authorize('create', Trip::class);
        $siteId = (string) session('site_id');

        $data = $r->validate([
            'date'         => ['required', 'date'],
            'shift_id'     => ['required', 'uuid', 'exists:shifts,id'],
            // ➜ unit harus milik site aktif
            'unit_id'      => ['required', 'uuid', Rule::exists('assets', 'id')->where('site_id', $siteId)],
            'commodity_id' => ['required', 'uuid', 'exists:commodities,id'],
            'pit_id'       => ['nullable', 'uuid', 'exists:pits,id'],
            'tonnage'      => ['nullable', 'numeric', 'min:0'],
            'distance_km'  => ['nullable', 'numeric', 'min:0'],
            'start_time'   => ['nullable', 'date'],
            'end_time'     => ['nullable', 'date', 'after_or_equal:start_time'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $data['site_id']    = $siteId;
        $data['status']     = 'draft';
        $data['created_by'] = (string) auth()->id();

        Trip::create($data);
        return redirect()->route('scm.trips.index')->with('success', 'Trip tersimpan.');
    }

    public function edit(\App\Models\Scm\Trip $trip)
    {
        $this->authorize('update', $trip);
        $siteId = (string) session('site_id');

        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $assets = DB::table('assets')
            ->where('site_id', $siteId)
            ->select('id', 'code', 'name')->orderBy('code')->get();

        $commodities = DB::table('commodities')->select('id', 'name')->orderBy('name')->get();
        $pits = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();

        return view('admin.scm.trips.form', compact('trip', 'shifts', 'assets', 'commodities', 'pits'));
    }

    public function update(Request $r, Trip $trip)
    {
        $this->authorize('update', $trip);
        $siteId = (string) session('site_id');

        $data = $r->validate([
            'date'         => ['required', 'date'],
            'shift_id'     => ['required', 'uuid', 'exists:shifts,id'],
            'unit_id'      => ['required', 'uuid', Rule::exists('assets', 'id')->where('site_id', $siteId)],
            'commodity_id' => ['required', 'uuid', 'exists:commodities,id'],
            'pit_id'       => ['nullable', 'uuid', 'exists:pits,id'],
            'tonnage'      => ['nullable', 'numeric', 'min:0'],
            'distance_km'  => ['nullable', 'numeric', 'min:0'],
            'start_time'   => ['nullable', 'date'],
            'end_time'     => ['nullable', 'date', 'after_or_equal:start_time'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $data['site_id'] = $trip->site_id; // lock
        $trip->update($data);

        return back()->with('success', 'Perubahan disimpan.');
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();
        return redirect()->route('scm.trips.index')->with('success', 'Trip dihapus.');
    }

    // === Detail ===
    public function show(Trip $trip)
    {
        $this->authorize('view', $trip);
        $siteId = (string) session('site_id');

        $labels = [
            'shift' => DB::table('shifts')->where('id', $trip->shift_id)->value('name'),
            'unit'  => DB::table('assets')->where('id', $trip->unit_id)->select(DB::raw("CONCAT(code,' — ',name) AS x"))->value('x'),
            'cmdty' => DB::table('commodities')->where('id', $trip->commodity_id)->value('name'),
            'pit'   => $trip->pit_id
                ? DB::table('pits')->where('site_id', $siteId)->where('id', $trip->pit_id)
                ->select(DB::raw("CONCAT(COALESCE(code,'PIT'),' — ',COALESCE(name,'')) AS x"))->value('x')
                : null,
        ];

        return view('admin.scm.trips.show', compact('trip', 'labels'));
    }

    // Actions — status flow
    public function submit(Trip $trip)
    {
        $this->authorize('submit', $trip);
        $trip->update(['status' => 'submitted']);
        return back()->with('success', 'Trip submitted.');
    }
    public function validateData(Trip $trip)
    {
        $this->authorize('validate', $trip);
        $trip->update(['status' => 'validated']);
        return back()->with('success', 'Trip validated.');
    }
    public function approve(Trip $trip)
    {
        $this->authorize('approve', $trip);
        $trip->update(['status' => 'approved']);
        return back()->with('success', 'Trip approved.');
    }
}
