<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\Reservation;
use App\Models\Scm\MaterialMaster;
use App\Models\Site;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = Reservation::with(['site', 'material'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('reservation_number'), fn($qq) => $qq->where('reservation_number', 'like', '%' . $request->reservation_number . '%'))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('reservation_type'), fn($qq) => $qq->where('reservation_type', $request->reservation_type))
            ->when($request->filled('material_id'), fn($qq) => $qq->where('material_id', $request->material_id))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::where('is_active', true)->orderBy('material_name')->get(['id', 'material_code', 'material_name']);

        return view('admin.scm.reservations.index', compact('items', 'sites', 'materials', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::where('is_active', true)->orderBy('material_name')->get(['id', 'material_code', 'material_name', 'base_uom']);

        $reservation = new Reservation([
            'site_id' => $siteId,
            'status' => 'draft',
        ]);

        return view('admin.scm.reservations.create', compact(
            'reservation', 'sites', 'materials', 'siteId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'reservation_number' => 'required|string|max:50|unique:scm_reservations,reservation_number',
            'material_id' => 'required|string',
            'quantity' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'reservation_type' => 'nullable|string',
            'movement_type' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
            'requested_by' => 'nullable|string',
        ]);

        Reservation::create($data);

        return redirect()
            ->route('scm.reservations.index', ['site' => $data['site_id']])
            ->with('success', 'Reservation tersimpan.');
    }

    public function edit(Request $request, Reservation $reservation)
    {
        $siteId = $reservation->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::where('is_active', true)->orderBy('material_name')->get(['id', 'material_code', 'material_name', 'base_uom']);

        return view('admin.scm.reservations.edit', compact(
            'reservation', 'sites', 'materials', 'siteId'
        ));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'reservation_number' => 'required|string|max:50|unique:scm_reservations,reservation_number,' . $reservation->id,
            'material_id' => 'required|string',
            'quantity' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'reservation_type' => 'nullable|string',
            'movement_type' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
            'requested_by' => 'nullable|string',
        ]);

        $reservation->update($data);

        return redirect()
            ->route('scm.reservations.index', ['site' => $data['site_id']])
            ->with('success', 'Reservation diperbarui.');
    }

    public function destroy(Request $request, Reservation $reservation)
    {
        $siteId = $reservation->site_id;
        $reservation->delete();

        return redirect()
            ->route('scm.reservations.index', ['site' => $siteId])
            ->with('success', 'Reservation dihapus.');
    }

    public function approve(Request $request, Reservation $reservation)
    {
        $reservation->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('scm.reservations.index', ['site' => $reservation->site_id])
            ->with('success', 'Reservation disetujui.');
    }
}
