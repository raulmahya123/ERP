<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scm\{StoreTripRequest, UpdateTripRequest};
use App\Models\Scm\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function index(Request $r)
    {
        $siteId = (string) session('site_id');
        $q = Trip::where('site_id', $siteId)
            ->when($r->filled('status'), fn($w) => $w->where('status', $r->status))
            ->latest('date');

        $trips = $q->paginate(15)->withQueryString();
        return view('admin.scm.trips.index', compact('trips'));
    }

    public function create()
    {
        $siteId = (string) session('site_id');

        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $assets = DB::table('assets')->where(function ($q) use ($siteId) {
            $q->where('site_id', $siteId)->orWhereNull('site_id');
        })->select('id', 'code', 'name')->orderBy('code')->get();
        $commodities = DB::table('commodities')->select('id', 'name')->orderBy('name')->get();

        return view('admin.scm.trips.form', [
            'trip' => new \App\Models\Scm\Trip(),
            'shifts' => $shifts,
            'assets' => $assets,
            'commodities' => $commodities,
        ]);
    }
    public function store(Request $r)
    {
        $this->authorize('create', Trip::class);

        $data = $r->validate([
            'date'         => ['required', 'date'],
            'shift_id'     => ['required', 'uuid', 'exists:shifts,id'],
            'unit_id'      => ['required', 'uuid', 'exists:assets,id'],
            'commodity_id' => ['required', 'uuid', 'exists:commodities,id'],
            'tonnage'      => ['nullable', 'numeric', 'min:0'],
            'distance_km'  => ['nullable', 'numeric', 'min:0'],
            'start_time'   => ['nullable', 'date'],
            'end_time'     => ['nullable', 'date', 'after_or_equal:start_time'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $data['site_id']    = (string) session('site_id'); // <-- penting utk sameSite
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
        $assets = DB::table('assets')->where(function ($q) use ($siteId) {
            $q->where('site_id', $siteId)->orWhereNull('site_id');
        })->select('id', 'code', 'name')->orderBy('code')->get();
        $commodities = DB::table('commodities')->select('id', 'name')->orderBy('name')->get();

        return view('admin.scm.trips.form', compact('trip', 'shifts', 'assets', 'commodities'));
    }

    public function update(Request $r, Trip $trip)
    {
        $this->authorize('update', $trip);

        $data = $r->validate([
            'date'         => ['required', 'date'],
            'shift_id'     => ['required', 'uuid', 'exists:shifts,id'],
            'unit_id'      => ['required', 'uuid', 'exists:assets,id'],
            'commodity_id' => ['required', 'uuid', 'exists:commodities,id'],
            'tonnage'      => ['nullable', 'numeric', 'min:0'],
            'distance_km'  => ['nullable', 'numeric', 'min:0'],
            'start_time'   => ['nullable', 'date'],
            'end_time'     => ['nullable', 'date', 'after_or_equal:start_time'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        // jangan ubah site_id
        $data['site_id'] = $trip->site_id;

        $trip->update($data);

        return back()->with('success', 'Perubahan disimpan.');
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();
        return redirect()->route('scm.trips.index')->with('success', 'Trip dihapus.');
    }

    // Actions — status flow
    public function submit(Trip $trip)
    {
        $this->authorize('submit', $trip);
        $trip->update(['status' => 'submitted']);
        // event(new \App\Events\Scm\TripSubmitted($trip)); // aktifkan kalau events sudah dibuat
        return back()->with('success', 'Trip submitted.');
    }

    public function validateData(Trip $trip)
    {
        $this->authorize('validate', $trip);
        $trip->update(['status' => 'validated']);
        // event(new \App\Events\Scm\TripValidated($trip));
        return back()->with('success', 'Trip validated.');
    }

    public function approve(Trip $trip)
    {
        $this->authorize('approve', $trip);
        $trip->update(['status' => 'approved']);
        // event(new \App\Events\Scm\TripApproved($trip));
        return back()->with('success', 'Trip approved.');
    }
}
