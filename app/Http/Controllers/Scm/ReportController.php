<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function targetVsActual(Request $r)
    {
        $siteId = (string) session('site_id');
        $date   = $r->query('date') ?: now()->toDateString();
        $shift  = $r->query('shift_id'); // opsional

        // === PLANNED (per pit) ===
        $planQ = DB::table('scm_daily_plans as p')
            ->join('scm_daily_plan_items as i','i.daily_plan_id','=','p.id')
            ->select('i.pit_id',
                DB::raw('SUM(i.target_ritase) as plan_ritase'),
                DB::raw('SUM(i.target_ton) as plan_ton')
            )
            ->where('p.site_id',$siteId)
            ->where('p.plan_date',$date);

        if ($shift) $planQ->where('p.shift_id',$shift);
        $plans = $planQ->groupBy('i.pit_id')->get()->keyBy('pit_id');

        // === ACTUAL RITASE via TRIPS (per pit) ===
        // Asumsi: trips punya asset_id, date, shift_id. Pit diturunkan dari dispatch allocation (asset->pit mapping di hari+shift itu).
        $tripQ = DB::table('scm_trips as t')
            ->join('scm_dispatch_allocations as d', function($j){
                $j->on('d.asset_id','=','t.asset_id')
                  ->on('d.work_date','=','t.date')
                  ->on('d.shift_id','=','t.shift_id');
            })
            ->select('d.pit_id', DB::raw('COUNT(*) as actual_ritase'))
            ->where('t.site_id',$siteId)
            ->where('t.date',$date);
        if ($shift) $tripQ->where('t.shift_id',$shift);
        $actualRit = $tripQ->groupBy('d.pit_id')->get()->keyBy('pit_id');

        // === ACTUAL TON via WB Tickets (per pit) ===
        // Asumsi: wb_tickets punya asset_id, date (atau ticket_date), shift_id, dan kolom net tonase (ganti 'net_ton' sesuai skema kamu).
        $wbQ = DB::table('scm_wb_tickets as w')
            ->join('scm_dispatch_allocations as d', function($j){
                $j->on('d.asset_id','=','w.asset_id')
                  ->on('d.work_date','=','w.date')     // ganti ke kolom tanggal ticket kamu jika berbeda
                  ->on('d.shift_id','=','w.shift_id');
            })
            ->select('d.pit_id', DB::raw('SUM(w.net_ton) as actual_ton')) // ganti net_ton -> kolom aktual kamu: net, netto_kg/1000, dsb.
            ->where('w.site_id',$siteId)
            ->where('w.date',$date);
        if ($shift) $wbQ->where('w.shift_id',$shift);
        $actualTon = $wbQ->groupBy('d.pit_id')->get()->keyBy('pit_id');

        // Gabungkan per PIT
        $pitIds = collect($plans->keys())->merge($actualRit->keys())->merge($actualTon->keys())->unique()->values();

        $rows = $pitIds->map(function($pit) use ($plans,$actualRit,$actualTon){
            $pR = (float) ($plans[$pit]->plan_ritase ?? 0);
            $pT = (float) ($plans[$pit]->plan_ton ?? 0);
            $aR = (int)   ($actualRit[$pit]->actual_ritase ?? 0);
            $aT = (float) ($actualTon[$pit]->actual_ton ?? 0);

            $gapR = $aR - $pR;
            $gapT = $aT - $pT;
            $achR = $pR > 0 ? round($aR / $pR * 100, 1) : null;
            $achT = $pT > 0 ? round($aT / $pT * 100, 1) : null;

            return (object)[
                'pit_id'=>$pit,
                'plan_ritase'=>$pR, 'actual_ritase'=>$aR, 'gap_ritase'=>$gapR, 'ach_ritase'=>$achR,
                'plan_ton'=>$pT,     'actual_ton'=>$aT,     'gap_ton'=>$gapT,   'ach_ton'=>$achT,
            ];
        });

        // (opsional) ambil label pit
        $pitLabels = DB::table('pits')->whereIn('id',$pitIds)->pluck(DB::raw("CONCAT(COALESCE(code,'PIT'),' — ',COALESCE(name,''))"),'id');

        return view('scm/reports/target-vs-actual', [
            'date'=>$date,'shift_id'=>$shift,
            'rows'=>$rows,'pitLabels'=>$pitLabels,
        ]);
    }
}
