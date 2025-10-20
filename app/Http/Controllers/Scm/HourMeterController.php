<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHourMeterRequest;
use App\Http\Requests\UpdateHourMeterRequest;
use App\Models\Scm\HourMeter;
use App\Models\{Site, Shift, Asset};
use Illuminate\Http\Request;

class HourMeterController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = HourMeter::query()
            ->with(['site','shift','unit'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('date_from'), fn($qq) => $qq->where('date', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn($qq) => $qq->where('date', '<=', $request->date_to))
            ->when($request->filled('shift_id'),  fn($qq) => $qq->where('shift_id', $request->shift_id))
            ->when($request->filled('unit_id'),   fn($qq) => $qq->where('unit_id', $request->unit_id))
            ->orderByDesc('date')
            ->orderBy('shift_id')
            ->orderBy('unit_id');

        $items  = $q->paginate(15)->withQueryString();

        // dropdowns
        $sites  = Site::orderBy('code')->get(['id','code','name']);
        $shifts = Shift::orderBy('name')->get(['id','name']);
        $units  = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                       ->orderBy('code')->get(['id','code','name']);

        return view('admin.scm.hour-meters.index', compact('items','sites','shifts','units','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites  = Site::orderBy('code')->get(['id','code','name']);
        $shifts = Shift::orderBy('name')->get(['id','name']);
        $units  = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                       ->orderBy('code')->get(['id','code','name']);

        $hourMeter = new HourMeter([
            'site_id' => $siteId,
            'date'    => now()->toDateString(),
        ]);

        return view('admin.scm.hour-meters.create', compact('hourMeter','sites','shifts','units','siteId'));
    }

    public function store(StoreHourMeterRequest $request)
    {
        $data = $request->validated();

        // Hitung delta & simple anomaly flag
        $delta   = round(((float)$data['hm_end'] - (float)$data['hm_start']), 1);
        $anomaly = $delta < 0 || $delta > 24; // aturan sederhana

        HourMeter::create([
            'site_id'    => $data['site_id'],
            'date'       => $data['date'],
            'shift_id'   => $data['shift_id'],
            'unit_id'    => $data['unit_id'],
            'hm_start'   => $data['hm_start'],
            'hm_end'     => $data['hm_end'],
            'hm_delta'   => $delta,
            'anomaly'    => $data['anomaly'] ?? $anomaly,
            'client_uid' => $data['client_uid'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('scm.hour_meters.index', ['site' => $data['site_id']])
            ->with('success', 'Hour Meter tersimpan. Delta = '.$delta.($anomaly ? ' (anomali)' : ''));
    }

    public function edit(Request $request, HourMeter $hour_meter)
    {
        $siteId = $hour_meter->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites  = Site::orderBy('code')->get(['id','code','name']);
        $shifts = Shift::orderBy('name')->get(['id','name']);
        $units  = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                       ->orderBy('code')->get(['id','code','name']);

        return view('admin.scm.hour-meters.edit', [
            'hourMeter' => $hour_meter,
            'sites'     => $sites,
            'shifts'    => $shifts,
            'units'     => $units,
            'siteId'    => $siteId,
        ]);
    }

    public function update(UpdateHourMeterRequest $request, HourMeter $hour_meter)
    {
        $data = $request->validated();

        $delta   = round(((float)$data['hm_end'] - (float)$data['hm_start']), 1);
        $anomaly = array_key_exists('anomaly', $data)
            ? (bool)$data['anomaly']
            : ($delta < 0 || $delta > 24);

        $hour_meter->update([
            'site_id'    => $data['site_id'],
            'date'       => $data['date'],
            'shift_id'   => $data['shift_id'],
            'unit_id'    => $data['unit_id'],
            'hm_start'   => $data['hm_start'],
            'hm_end'     => $data['hm_end'],
            'hm_delta'   => $delta,
            'anomaly'    => $anomaly,
            'client_uid' => $data['client_uid'] ?? null,
        ]);

        return redirect()
            ->route('scm.hour_meters.index', ['site' => $data['site_id']])
            ->with('success', 'Hour Meter diperbarui. Delta = '.$delta.($anomaly ? ' (anomali)' : ''));
    }

    public function destroy(Request $request, HourMeter $hour_meter)
    {
        $siteId = $hour_meter->site_id;
        $hour_meter->delete();

        return redirect()
            ->route('scm.hour_meters.index', ['site' => $siteId])
            ->with('success', 'Hour Meter dihapus.');
    }
}
