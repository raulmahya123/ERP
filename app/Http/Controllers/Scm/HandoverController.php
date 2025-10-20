<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scm\{StoreHandoverRequest, UpdateHandoverRequest};
use App\Models\Scm\{ShiftHandover, ShiftHandoverItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HandoverController extends Controller
{
    public function index(Request $r)
    {
        $siteId = (string) session('site_id');
        $q = ShiftHandover::with('items')
            ->where('site_id', $siteId)
            ->when($r->filled('date'), fn($w) => $w->where('handover_date', $r->date))
            ->when($r->filled('from_shift_id'), fn($w) => $w->where('from_shift_id', $r->from_shift_id))
            ->when($r->filled('to_shift_id'), fn($w) => $w->where('to_shift_id', $r->to_shift_id))
            ->orderByDesc('handover_date');

        $items = $q->paginate(15)->withQueryString();
        return view('admin.scm.handovers.index', compact('items'));
    }

    public function create()
    {
        $siteId = (string) session('site_id');
        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $pits   = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();

        return view('admin.scm.handovers.form', [
            'item' => $item ?? new \App\Models\Scm\ShiftHandover(),
            'items' => $item->items ?? collect(),
            'shifts' => $shifts,
            'pits' => $pits
        ]);
    }

    public function edit(string $id)
    {
        $siteId = (string) session('site_id');
        $item = ShiftHandover::where('site_id', $siteId)->with('items')->findOrFail($id);

        $shifts = DB::table('shifts')->select('id', 'name')->orderBy('name')->get();
        $pits   = DB::table('pits')->where('site_id', $siteId)->select('id', 'code', 'name')->orderBy('code')->get();

        return view('admin.scm.handovers.form', [
            'item' => $item,
            'items' => $item->items,
            'shifts' => $shifts,
            'pits' => $pits
        ]);
    }

    public function store(StoreHandoverRequest $req)
    {
        $siteId = (string) session('site_id');
        $v = $req->validated();

        // Unique kombinasi (site, date, from_shift, to_shift)
        $exists = ShiftHandover::where('site_id', $siteId)->where('handover_date', $v['handover_date'])
            ->where('from_shift_id', $v['from_shift_id'])->where('to_shift_id', $v['to_shift_id'])->exists();
        if ($exists) return back()->withErrors(['to_shift_id' => 'Handover untuk kombinasi ini sudah ada.'])->withInput();

        DB::transaction(function () use ($v, $siteId) {
            $h = ShiftHandover::create([
                'site_id' => $siteId,
                'handover_date' => $v['handover_date'],
                'from_shift_id' => $v['from_shift_id'],
                'to_shift_id' => $v['to_shift_id'],
                'weather' => $v['weather'] ?? null,
                'issues' => $v['issues'] ?? null,
                'targets' => $v['targets'] ?? null,
                'notes' => $v['notes'] ?? null,
                'extra' => [],
            ]);

            foreach (($v['items'] ?? []) as $it) {
                ShiftHandoverItem::create([
                    'handover_id' => $h->id,
                    'pit_id' => $it['pit_id'],
                    'notes' => $it['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('scm.handovers.index')->with('ok', 'Handover dibuat.');
    }

    public function update(UpdateHandoverRequest $req, string $id)
    {
        $siteId = (string) session('site_id');
        $v = $req->validated();

        DB::transaction(function () use ($id, $v, $siteId) {
            $h = ShiftHandover::where('site_id', $siteId)->findOrFail($id);

            $dup = ShiftHandover::where('site_id', $siteId)->where('handover_date', $v['handover_date'])
                ->where('from_shift_id', $v['from_shift_id'])->where('to_shift_id', $v['to_shift_id'])
                ->where('id', '!=', $h->id)->exists();
            if ($dup) abort(422, 'Handover untuk kombinasi ini sudah ada.');

            $h->update([
                'handover_date' => $v['handover_date'],
                'from_shift_id' => $v['from_shift_id'],
                'to_shift_id' => $v['to_shift_id'],
                'weather' => $v['weather'] ?? null,
                'issues' => $v['issues'] ?? null,
                'targets' => $v['targets'] ?? null,
                'notes' => $v['notes'] ?? null,
            ]);

            ShiftHandoverItem::where('handover_id', $h->id)->delete();
            foreach (($v['items'] ?? []) as $it) {
                ShiftHandoverItem::create([
                    'handover_id' => $h->id,
                    'pit_id' => $it['pit_id'],
                    'notes' => $it['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('scm.handovers.index')->with('ok', 'Handover diupdate.');
    }

    public function destroy(string $id)
    {
        $item = ShiftHandover::where('site_id', session('site_id'))->findOrFail($id);
        $item->delete();
        return back()->with('ok', 'Handover dihapus.');
    }
}
