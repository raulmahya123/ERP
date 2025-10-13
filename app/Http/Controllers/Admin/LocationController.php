<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Site;

class LocationController extends Controller
{
    /** Ambil site aktif: request -> session -> user->default_site_id / user->site_id */
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
        $u = $r->user();
        return $u?->default_site_id ?? $u?->site_id ?? null;
    }

    public function index(Request $r)
    {
        $siteId = $this->resolveActiveSiteId($r);

        $rows = Location::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($r->filled('q'), function ($q) use ($r) {
                $kw = trim($r->input('q'));
                $q->where(function ($qq) use ($kw) {
                    $qq->where('name', 'like', "%{$kw}%");
                });
            })
            ->with([
                'site:id,code,name',
                'creator:id,name',
            ])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // kirim data site aktif utk header/label
        $activeSite   = $siteId ? Site::select('id','code','name')->find($siteId) : null;
        $sites        = $activeSite ? [$activeSite] : [];
        $activeSiteId = $siteId;

        return view('admin.locations.index', compact('rows','sites','activeSiteId'));
    }

    public function create(Request $r)
    {
        $activeSiteId = $this->resolveActiveSiteId($r);
        if (!$activeSiteId) {
            return redirect()->route('admin.locations.index')->with('error','Pilih site dulu.');
        }
        return view('admin.locations.create', compact('activeSiteId'));
    }

    public function store(Request $r)
    {
        $siteId = $this->resolveActiveSiteId($r);
        if (!$siteId) {
            return back()->withInput()->with('error','Pilih site dulu.');
        }

        $data = $r->validate([
            'name'               => ['required','string','max:191'],
            'latitude'           => ['required','numeric','between:-90,90'],
            'longitude'          => ['required','numeric','between:-180,180'],
            'geofence_radius_m'  => ['nullable','integer','min:10','max:5000'],
        ]);

        $loc = new Location($data);
        $loc->site_id = $siteId;
        if (!$loc->geofence_radius_m) $loc->geofence_radius_m = 100;
        $loc->created_by = optional($r->user())->id;
        $loc->save();

        return redirect()
            ->route('admin.locations.index', ['site_id'=>$siteId])
            ->with('success','Lokasi dibuat.');
    }

    public function edit(Request $r, Location $location)
    {
        // kunci edit tetap di site aktif agar konsisten
        $activeSiteId = $this->resolveActiveSiteId($r);
        return view('admin.locations.edit', compact('location','activeSiteId'));
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
        // kalau mau set updated_by, bisa lewat model booted()
        $location->save();

        return redirect()
            ->route('admin.locations.index', ['site_id'=>$location->site_id])
            ->with('success','Lokasi diupdate.');
    }

    public function destroy(Location $location)
    {
        $sid = $location->site_id;
        $location->delete();
        return redirect()
            ->route('admin.locations.index', ['site_id'=>$sid])
            ->with('success','Lokasi dihapus.');
    }
}
