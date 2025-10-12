<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function index(Request $r)
    {
        $siteId = $r->input('site_id', session('site_id'));
        $rows = Location::when($siteId, fn($q)=>$q->where('site_id',$siteId))
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.locations.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $r)
    {
        $siteId = $r->input('site_id', session('site_id'));

        $data = $r->validate([
            'name'               => ['required','string','max:191'],
            'latitude'           => ['required','numeric','between:-90,90'],
            'longitude'          => ['required','numeric','between:-180,180'],
            'geofence_radius_m'  => ['nullable','integer','min:10','max:5000'],
        ]);

        $loc = new Location($data);
        $loc->site_id = $siteId;
        if (!$loc->geofence_radius_m) $loc->geofence_radius_m = 100;
        $loc->created_by = $r->user()->id;
        $loc->save();

        return redirect()->route('admin.locations.index')->with('success','Lokasi dibuat.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $r, Location $location)
    {
        $data = $r->validate([
            'name'               => ['required','string','max:191'],
            'latitude'           => ['required','numeric','between:-90,90'],
            'longitude'          => ['required','numeric','between:-180,180'],
            'geofence_radius_m'  => ['nullable','integer','min:10','max:5000'],
        ]);

        $location->fill($data);
        if (!$location->geofence_radius_m) $location->geofence_radius_m = 100;
        $location->save();

        return redirect()->route('admin.locations.index')->with('success','Lokasi diupdate.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return back()->with('success','Lokasi dihapus.');
    }
}
