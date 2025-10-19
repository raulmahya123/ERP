<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBreakdownRequest;
use App\Http\Requests\UpdateBreakdownRequest;
use App\Models\Scm\Breakdown;
use App\Models\Site;
use App\Models\Asset; // diasumsikan nama model Unit = Asset
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class BreakdownController extends Controller
{
    public function index(Request $request)
    {
        $siteId = (string)($request->query('site') ?? $request->input('site'));
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $unitId   = $request->query('unit_id');
        $category = $request->query('category');

        $sites = Site::orderBy('code')->get(['id','code','name']);

        // Jika site belum dipilih, pakai yang pertama
        if (!$siteId && $sites->count() > 0) {
            $siteId = $sites->first()->id;
        }

        $units = collect();
        if ($siteId) {
            $units = Asset::where('site_id', $siteId)
                ->orderBy('code')
                ->get(['id','code','name']);
        }

        $categories = [
            'planned'   => 'Planned',
            'unplanned' => 'Unplanned',
            'standby'   => 'Standby',
        ];

        $q = Breakdown::with(['unit'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($unitId, fn($qq) => $qq->where('unit_id', $unitId))
            ->when($category, fn($qq) => $qq->where('category', $category))
            ->when($dateFrom, function ($qq) use ($dateFrom) {
                $from = Carbon::parse($dateFrom);
                $qq->where('start_at', '>=', $from);
            })
            ->when($dateTo, function ($qq) use ($dateTo) {
                $to = Carbon::parse($dateTo);
                // pakai end_of_minute agar inklusif
                $qq->where('start_at', '<=', $to);
            })
            ->orderByDesc('start_at');

        $items = $q->paginate(15)->withQueryString();

        return view('admin.scm.breakdowns.index', [
            'items'      => $items,
            'sites'      => $sites,
            'units'      => $units,
            'categories' => $categories,
            'siteId'     => $siteId,
        ]);
    }

    public function create(Request $request)
    {
        $siteId = (string)($request->query('site') ?? $request->input('site'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        if (!$siteId && $sites->count() > 0) {
            $siteId = $sites->first()->id;
        }

        $units = collect();
        if ($siteId) {
            $units = Asset::where('site_id', $siteId)->orderBy('code')->get(['id','code','name']);
        }

        $categories = [
            'planned'   => 'Planned',
            'unplanned' => 'Unplanned',
            'standby'   => 'Standby',
        ];

        return view('admin.scm.breakdowns.create', [
            'sites'      => $sites,
            'units'      => $units,
            'categories' => $categories,
            'siteId'     => $siteId,
        ]);
    }

    public function store(StoreBreakdownRequest $request)
    {
        $data = $request->validated();

        $start = Carbon::parse($data['start_at']);
        $end   = isset($data['end_at']) && $data['end_at'] ? Carbon::parse($data['end_at']) : null;

        $durationHours = $end
            ? $end->floatDiffInHours($start)
            : 0;

        $breakdown = Breakdown::create([
            'site_id'        => $data['site_id'],
            'unit_id'        => $data['unit_id'],
            'category'       => $data['category'],
            'cause_code'     => $data['cause_code'] ?? null,
            'start_at'       => $start->format('Y-m-d H:i:s'),
            'end_at'         => $end?->format('Y-m-d H:i:s'),
            'duration_hours' => $durationHours,
            'notes'          => $data['notes'] ?? null,
            'created_by'     => $request->user()->id,
        ]);

        $indexRoute = Route::has('scm.breakdowns.index') ? 'scm.breakdowns.index' : 'breakdowns.index';

        return redirect()
            ->route($indexRoute, ['site' => $breakdown->site_id])
            ->with('success', 'Breakdown berhasil dibuat.');
    }

    public function edit(Request $request, Breakdown $breakdown)
    {
        $siteId = (string)($request->query('site') ?? $breakdown->site_id);

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $units = Asset::where('site_id', $siteId)->orderBy('code')->get(['id','code','name']);

        $categories = [
            'planned'   => 'Planned',
            'unplanned' => 'Unplanned',
            'standby'   => 'Standby',
        ];

        return view('admin.scm.breakdowns.edit', [
            'breakdown' => $breakdown, // PENTING: bukan $item
            'sites'      => $sites,
            'units'      => $units,
            'categories' => $categories,
            'siteId'     => $siteId,
        ]);
    }

    public function update(UpdateBreakdownRequest $request, Breakdown $breakdown)
    {
        $data  = $request->validated();
        $start = Carbon::parse($data['start_at']);
        $end   = isset($data['end_at']) && $data['end_at'] ? Carbon::parse($data['end_at']) : null;

        $durationHours = $end
            ? $end->floatDiffInHours($start)
            : 0;

        $breakdown->update([
            'site_id'        => $data['site_id'] ?? $breakdown->site_id,
            'unit_id'        => $data['unit_id'],
            'category'       => $data['category'],
            'cause_code'     => $data['cause_code'] ?? null,
            'start_at'       => $start->format('Y-m-d H:i:s'),
            'end_at'         => $end?->format('Y-m-d H:i:s'),
            'duration_hours' => $durationHours,
            'notes'          => $data['notes'] ?? null,
        ]);

        $indexRoute = Route::has('scm.breakdowns.index') ? 'scm.breakdowns.index' : 'breakdowns.index';

        return redirect()
            ->route($indexRoute, ['site' => $breakdown->site_id])
            ->with('success', 'Breakdown berhasil diperbarui.');
    }

    public function destroy(Breakdown $breakdown)
    {
        $siteId = $breakdown->site_id;
        $breakdown->delete();

        $indexRoute = Route::has('scm.breakdowns.index') ? 'scm.breakdowns.index' : 'breakdowns.index';

        return redirect()
            ->route($indexRoute, ['site' => $siteId])
            ->with('success', 'Breakdown berhasil dihapus.');
    }
}
