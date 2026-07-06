<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function targetVsActual(Request $r)
    {
        $siteId = (string) session('site_id');
        $date   = $r->query('date') ?: now()->toDateString();
        $shift  = $r->query('shift_id'); // opsional

        // === PLANNED ===
        $planQ = DB::table('scm_daily_plans as p')
            ->join('scm_daily_plan_items as i', 'i.daily_plan_id', '=', 'p.id')
            ->select(
                'i.pit_id',
                DB::raw('SUM(i.target_ritase) as plan_ritase'),
                DB::raw('SUM(i.target_ton)    as plan_ton')
            )
            ->where('p.site_id', $siteId)
            ->where('p.plan_date', $date);
        if ($shift) $planQ->where('p.shift_id', $shift);
        $plans = $planQ->groupBy('i.pit_id')->get()->keyBy('pit_id');

        // Helper tanggal
        $tripDateCol = Schema::hasColumn('scm_trips', 'date') ? 'date' : (Schema::hasColumn('scm_trips', 'trip_date') ? 'trip_date' : null);
        $wbDateCol   = Schema::hasColumn('scm_wb_tickets', 'date') ? 'date' : (Schema::hasColumn('scm_wb_tickets', 'ticket_date') ? 'ticket_date' : (Schema::hasColumn('scm_wb_tickets', 'weigh_date') ? 'weigh_date' : null));

        // === ACTUAL RITASE ===
        if (Schema::hasColumn('scm_trips', 'pit_id')) {
            // Pakai pit_id langsung
            $tripQ = DB::table('scm_trips as t')
                ->select('t.pit_id', DB::raw('COUNT(*) as actual_ritase'))
                ->where('t.site_id', $siteId);
            if ($tripDateCol) $tripQ->where("t.$tripDateCol", $date);
            else              $tripQ->whereRaw('DATE(t.created_at)=?', [$date]);
            if ($shift && Schema::hasColumn('scm_trips', 'shift_id')) $tripQ->where('t.shift_id', $shift);
            $actualRit = $tripQ->groupBy('t.pit_id')->get()->keyBy('pit_id');
        } else {
            // Join ke dispatch (map asset/operator -> PIT)
            $tripKey = Schema::hasColumn('scm_trips', 'asset_id')   ? 'asset_id' : (Schema::hasColumn('scm_trips', 'unit_id')   ? 'unit_id' : (Schema::hasColumn('scm_trips', 'operator_id') ? 'operator_id' : null));
            $actualRit = collect();
            if ($tripKey) {
                $tripQ = DB::table('scm_trips as t')
                    ->join('scm_dispatch_allocations as d', function ($j) use ($tripKey, $tripDateCol, $siteId) {
                        $dispatchKey = $tripKey === 'operator_id' ? 'operator_id' : 'asset_id';
                        $j->on("d.$dispatchKey", '=', "t.$tripKey");
                        if ($tripDateCol) $j->on('d.work_date', '=', 't.' . $tripDateCol);
                        else              $j->on('d.work_date', '=', DB::raw('DATE(t.created_at)'));
                        if (Schema::hasColumn('scm_trips', 'shift_id')) $j->on('d.shift_id', '=', 't.shift_id');
                        $j->where('d.site_id', '=', $siteId);
                    })
                    ->select('d.pit_id', DB::raw('COUNT(*) as actual_ritase'))
                    ->where('t.site_id', $siteId);
                if ($tripDateCol) $tripQ->where("t.$tripDateCol", $date);
                else              $tripQ->whereRaw('DATE(t.created_at)=?', [$date]);
                if ($shift && Schema::hasColumn('scm_trips', 'shift_id')) $tripQ->where('t.shift_id', $shift);
                $actualRit = $tripQ->groupBy('d.pit_id')->get()->keyBy('pit_id');
            }
        }

        // === ACTUAL TON ===
        $wbNetCol = Schema::hasColumn('scm_wb_tickets', 'net_ton')   ? 'net_ton' : (Schema::hasColumn('scm_wb_tickets', 'netto_ton') ? 'netto_ton' : (Schema::hasColumn('scm_wb_tickets', 'netto_kg')  ? 'netto_kg' : null));

        if (Schema::hasColumn('scm_wb_tickets', 'pit_id')) {
            $wbQ = DB::table('scm_wb_tickets as w')
                ->select('w.pit_id');
            if ($wbNetCol === 'netto_kg') $wbQ->addSelect(DB::raw('SUM(w.netto_kg)/1000 as actual_ton'));
            else                           $wbQ->addSelect(DB::raw("SUM(w.$wbNetCol) as actual_ton"));
            $wbQ->where('w.site_id', $siteId);
            if ($wbDateCol) $wbQ->whereRaw("DATE(w.$wbDateCol)=?", [$date]);
            else $wbQ->whereRaw('DATE(w.created_at)=?', [$date]);
            if ($shift && Schema::hasColumn('scm_wb_tickets', 'shift_id')) $wbQ->where('w.shift_id', $shift);
            $actualTon = $wbQ->groupBy('w.pit_id')->get()->keyBy('pit_id');
        } else {
            $wbKey = Schema::hasColumn('scm_wb_tickets', 'asset_id')   ? 'asset_id' : (Schema::hasColumn('scm_wb_tickets', 'unit_id')   ? 'unit_id' : (Schema::hasColumn('scm_wb_tickets', 'operator_id') ? 'operator_id' : null));
            $actualTon = collect();
            if ($wbKey && $wbNetCol) {
                $wbQ = DB::table('scm_wb_tickets as w')
                    ->join('scm_dispatch_allocations as d', function ($j) use ($wbKey, $wbDateCol, $siteId) {
                        $dispatchKey = $wbKey === 'operator_id' ? 'operator_id' : 'asset_id';
                        $j->on("d.$dispatchKey", '=', 'w.' . $wbKey);
                        if ($wbDateCol) $j->on('d.work_date', '=', DB::raw("DATE(w.$wbDateCol)"));
                        else            $j->on('d.work_date', '=', DB::raw('DATE(w.created_at)'));
                        if (Schema::hasColumn('scm_wb_tickets', 'shift_id')) $j->on('d.shift_id', '=', 'w.shift_id');
                        $j->where('d.site_id', '=', $siteId);
                    })
                    ->select('d.pit_id');
                if ($wbNetCol === 'netto_kg') $wbQ->addSelect(DB::raw('SUM(w.netto_kg)/1000 as actual_ton'));
                else                           $wbQ->addSelect(DB::raw("SUM(w.$wbNetCol) as actual_ton"));
                $wbQ->where('w.site_id', $siteId);
                if ($wbDateCol) $wbQ->whereRaw("DATE(w.$wbDateCol)=?", [$date]);
                else $wbQ->whereRaw('DATE(w.created_at)=?', [$date]);
                if ($shift && Schema::hasColumn('scm_wb_tickets', 'shift_id')) $wbQ->where('w.shift_id', $shift);
                $actualTon = $wbQ->groupBy('d.pit_id')->get()->keyBy('pit_id');
            }
        }

        // === Gabung per PIT ===
        $pitIds = collect($plans->keys())->merge($actualRit->keys())->merge($actualTon->keys())->unique()->values();

        $rows = $pitIds->map(function ($pit) use ($plans, $actualRit, $actualTon) {
            $pR = (float)($plans[$pit]->plan_ritase ?? 0);
            $pT = (float)($plans[$pit]->plan_ton ?? 0);
            $aR = (int)  ($actualRit[$pit]->actual_ritase ?? 0);
            $aT = (float)($actualTon[$pit]->actual_ton ?? 0);
            return (object)[
                'pit_id' => $pit,
                'plan_ritase' => $pR,
                'actual_ritase' => $aR,
                'gap_ritase' => $aR - $pR,
                'ach_ritase' => $pR > 0 ? round($aR / $pR * 100, 1) : null,
                'plan_ton' => $pT,
                'actual_ton' => $aT,
                'gap_ton' => $aT - $pT,
                'ach_ton' => $pT > 0 ? round($aT / $pT * 100, 1) : null,
            ];
        });

        $pitLabels = DB::table('pits')->whereIn('id', $pitIds)
            ->pluck(DB::raw("CONCAT(COALESCE(code,'PIT'),' — ',COALESCE(name,''))"), 'id');

        return view('admin.scm.reports.target-vs-actual', [
            'date'       => $date,
            'shift_id'   => $shift,   // <— penting: kirimkan shift_id
            'rows'       => $rows,
            'pitLabels'  => $pitLabels,
        ]);
    }
}
