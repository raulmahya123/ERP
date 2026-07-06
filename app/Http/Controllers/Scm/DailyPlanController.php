<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scm\{StoreDailyPlanRequest, UpdateDailyPlanRequest};
use App\Models\Scm\{DailyPlan, DailyPlanItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyPlanController extends Controller
{
    public function index(Request $r)
    {
        $siteId = (string) session('site_id');

        $q = DailyPlan::with('items')
            ->where('site_id', $siteId)
            // gunakan input('date') dan whereDate agar aman
            ->when($r->filled('date'), fn ($w) => $w->whereDate('plan_date', $r->input('date')))
            ->when($r->filled('shift_id'), fn ($w) => $w->where('shift_id', $r->input('shift_id')))
            ->orderByDesc('plan_date')
            ->orderBy('shift_id');

        $items = $q->paginate(15)->withQueryString();

        // shifts utk filter + map id=>name utk tampilan
        $shifts   = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $shiftMap = $shifts->pluck('name', 'id');

        return view('admin.scm.daily-plans.index', compact('items', 'shifts', 'shiftMap'));
    }

    public function create()
    {
        $siteId = (string) session('site_id');
        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $pits   = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();

        return view('admin.scm.daily-plans.form', [
            'item'   => new DailyPlan(),
            'items'  => collect(),
            'shifts' => $shifts,
            'pits'   => $pits,
        ]);
    }

    public function store(StoreDailyPlanRequest $req)
    {
        $siteId = (string) session('site_id');
        $v = $req->validated();

        // unique composite
        $exists = DailyPlan::where('site_id', $siteId)
            ->whereDate('plan_date', $v['plan_date'])
            ->where('shift_id', $v['shift_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['shift_id' => 'Plan untuk tanggal+shift ini sudah ada.'])
                ->withInput();
        }

        DB::transaction(function () use ($v, $siteId) {
            $plan = DailyPlan::create([
                'site_id'   => $siteId,
                'plan_date' => $v['plan_date'],
                'shift_id'  => $v['shift_id'],
                'remarks'   => $v['remarks'] ?? null,
                'extra'     => [],
            ]);

            foreach ($v['items'] as $it) {
                DailyPlanItem::create([
                    'daily_plan_id'  => $plan->id,
                    'pit_id'         => $it['pit_id'],
                    'target_ton'     => $it['target_ton'],
                    'target_ritase'  => $it['target_ritase'],
                    'notes'          => $it['notes'] ?? null,
                ]);
            }
        });

        return to_route('scm.daily-plans.index')->with('ok', 'Daily plan dibuat.');
    }

    public function edit(string $id)
    {
        $siteId = (string) session('site_id');
        $item   = DailyPlan::where('site_id', $siteId)->with('items')->findOrFail($id);

        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $pits   = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();

        return view('admin.scm.daily-plans.form', [
            'item'   => $item,
            'items'  => $item->items,
            'shifts' => $shifts,
            'pits'   => $pits,
        ]);
    }

    public function update(UpdateDailyPlanRequest $req, string $id)
    {
        $siteId = (string) session('site_id');
        $v = $req->validated();

        DB::transaction(function () use ($id, $v, $siteId) {
            $plan = DailyPlan::where('site_id', $siteId)->findOrFail($id);

            $dup = DailyPlan::where('site_id', $siteId)
                ->whereDate('plan_date', $v['plan_date'])
                ->where('shift_id', $v['shift_id'])
                ->where('id', '!=', $plan->id)
                ->exists();
            if ($dup) {
                abort(422, 'Plan untuk tanggal+shift ini sudah ada.');
            }

            $plan->update([
                'plan_date' => $v['plan_date'],
                'shift_id'  => $v['shift_id'],
                'remarks'   => $v['remarks'] ?? null,
            ]);

            // reset items
            $plan->items()->delete();
            foreach ($v['items'] as $it) {
                DailyPlanItem::create([
                    'daily_plan_id'  => $plan->id,
                    'pit_id'         => $it['pit_id'],
                    'target_ton'     => $it['target_ton'],
                    'target_ritase'  => $it['target_ritase'],
                    'notes'          => $it['notes'] ?? null,
                ]);
            }
        });

        return to_route('scm.daily-plans.index')->with('ok', 'Daily plan diupdate.');
    }

    public function destroy(string $id)
    {
        $plan = DailyPlan::where('site_id', session('site_id'))->findOrFail($id);

        DB::transaction(function () use ($plan) {
            // aman utk FK
            $plan->items()->delete();
            $plan->delete();
        });

        // selalu balik ke index (fix delete dari show)
        return to_route('scm.daily-plans.index')->with('ok', 'Daily plan dihapus.');
    }

    public function show(string $id)
    {
        $siteId = (string) session('site_id');

        $plan = DailyPlan::where('site_id', $siteId)->with('items')->findOrFail($id);

        // Join items + pits supaya dapat code/name PIT
        $items = DB::table('scm_daily_plan_items as i')
            ->leftJoin('pits as p', 'p.id', '=', 'i.pit_id')
            ->where('i.daily_plan_id', $plan->id)
            ->orderBy('p.code')
            ->get([
                'i.id',
                'i.pit_id',
                'i.target_ton',
                'i.target_ritase',
                'i.notes',
                'p.code as pit_code',
                'p.name as pit_name'
            ]);

        $shiftName = DB::table('shifts')->where('id', $plan->shift_id)->value('name');
        $sumTon = (float) $items->sum('target_ton');
        $sumRit = (int) $items->sum('target_ritase');

        return view('admin.scm.daily-plans.show', compact('plan', 'items', 'shiftName', 'sumTon', 'sumRit'));
    }
}
