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
            ->when($r->filled('date'), fn($w) => $w->where('plan_date', $r->date))
            ->when($r->filled('shift_id'), fn($w) => $w->where('shift_id', $r->shift_id))
            ->orderByDesc('plan_date')->orderBy('shift_id');

        $items = $q->paginate(15)->withQueryString();
        return view('admin.scm.daily-plans.index', compact('items'));
    }

    public function create()
    {
        return view('admin.scm.daily-plans.form', ['item' => new DailyPlan(), 'items' => collect()]);
    }

    public function store(StoreDailyPlanRequest $req)
    {
        $siteId = (string) session('site_id');
        $v = $req->validated();

        // extra safety: unique composite
        $exists = DailyPlan::where('site_id', $siteId)->where('plan_date', $v['plan_date'])->where('shift_id', $v['shift_id'])->exists();
        if ($exists) {
            return back()->withErrors(['shift_id' => 'Plan untuk tanggal+shift ini sudah ada.'])->withInput();
        }

        DB::transaction(function () use ($v, $siteId) {
            $plan = DailyPlan::create([
                'site_id' => $siteId,
                'plan_date' => $v['plan_date'],
                'shift_id' => $v['shift_id'],
                'remarks' => $v['remarks'] ?? null,
                'extra' => [],
            ]);

            foreach ($v['items'] as $it) {
                DailyPlanItem::create([
                    'daily_plan_id' => $plan->id,
                    'pit_id' => $it['pit_id'],
                    'target_ton' => $it['target_ton'],
                    'target_ritase' => $it['target_ritase'],
                    'notes' => $it['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('scm.daily-plans.index')->with('ok', 'Daily plan dibuat.');
    }

    public function edit(string $id)
    {
        $item = DailyPlan::where('site_id', session('site_id'))->with('items')->findOrFail($id);
        return view('admin.scm.daily-plans.form', ['item' => $item, 'items' => $item->items]);
    }

    public function update(UpdateDailyPlanRequest $req, string $id)
    {
        $siteId = (string) session('site_id');
        $v = $req->validated();

        DB::transaction(function () use ($id, $v, $siteId) {
            $plan = DailyPlan::where('site_id', $siteId)->findOrFail($id);

            // Cek unique (kecuali diri sendiri)
            $dup = DailyPlan::where('site_id', $siteId)
                ->where('plan_date', $v['plan_date'])->where('shift_id', $v['shift_id'])
                ->where('id', '!=', $plan->id)->exists();
            if ($dup) abort(422, 'Plan untuk tanggal+shift ini sudah ada.');

            $plan->update([
                'plan_date' => $v['plan_date'],
                'shift_id' => $v['shift_id'],
                'remarks' => $v['remarks'] ?? null,
            ]);

            // Sync items sederhana: hapus-ulang (bisa dioptimalkan nanti)
            DailyPlanItem::where('daily_plan_id', $plan->id)->delete();
            foreach ($v['items'] as $it) {
                DailyPlanItem::create([
                    'daily_plan_id' => $plan->id,
                    'pit_id' => $it['pit_id'],
                    'target_ton' => $it['target_ton'],
                    'target_ritase' => $it['target_ritase'],
                    'notes' => $it['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('scm.daily-plans.index')->with('ok', 'Daily plan diupdate.');
    }

    public function destroy(string $id)
    {
        $plan = DailyPlan::where('site_id', session('site_id'))->findOrFail($id);
        DB::transaction(fn() => $plan->delete());
        return back()->with('ok', 'Daily plan dihapus.');
    }
}
