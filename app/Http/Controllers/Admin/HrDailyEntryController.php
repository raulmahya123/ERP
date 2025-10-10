<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrDailyEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HrDailyEntryController extends Controller
{
    public function index(Request $r)
    {
        $q = HrDailyEntry::query()
            ->with(['user','fromShift','toShift','site'])
            ->when($r->site_id ?? session('site_id'), fn($q,$sid)=>$q->where('site_id',$sid))
            ->when($r->user_id, fn($q,$uid)=>$q->where('user_id',$uid))
            ->when($r->type, fn($q,$t)=>$q->where('type',$t))
            ->when($r->date, fn($q,$d)=>$q->whereDate('date',$d))
            ->orderByDesc('date');

        $entries = $q->paginate($r->integer('per_page',25))->appends($r->query());

        if (! $r->wantsJson()) {
            $types = ['leave'=>'Leave','permit'=>'Permit','sick'=>'Sick','shift_change'=>'Shift Change'];
            return view('admin.hr_entries.index', compact('entries','types'));
        }

        return response()->json($entries);
    }

    public function create()
    {
        $types = ['leave'=>'Leave','permit'=>'Permit','sick'=>'Sick','shift_change'=>'Shift Change'];
        return view('admin.hr_entries.create', compact('types'));
    }

    public function store(Request $r)
    {
        $data=$r->validate([
            'site_id'       => ['required','uuid'],
            'user_id'       => ['required','uuid'],
            'date'          => ['required','date'],
            'type'          => ['required','in:leave,permit,sick,shift_change'],
            'code'          => ['nullable','string','max:20'],
            'reason'        => ['nullable','string'],
            'from_shift_id' => ['nullable','uuid'],
            'to_shift_id'   => ['nullable','uuid'],
            'meta'          => ['nullable','array'],
        ]);

        $data['id']=(string)Str::uuid();

        HrDailyEntry::updateOrCreate(
            [
                'site_id' => $data['site_id'],
                'user_id' => $data['user_id'],
                'date'    => $data['date'],
                'type'    => $data['type'],
                'code'    => $data['code'] ?? null,
            ],
            collect($data)->except(['id','site_id','user_id','date','type','code'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.hr-entries.index')->with('success','Entry HR harian disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(HrDailyEntry $entry)
    {
        $types = ['leave'=>'Leave','permit'=>'Permit','sick'=>'Sick','shift_change'=>'Shift Change'];
        return view('admin.hr_entries.edit', ['entry'=>$entry, 'types'=>$types]);
    }

    public function update(Request $r,HrDailyEntry $entry)
    {
        $data = $r->validate([
            'reason'        => ['nullable','string'],
            'from_shift_id' => ['nullable','uuid'],
            'to_shift_id'   => ['nullable','uuid'],
            'meta'          => ['nullable','array'],
        ]);

        $entry->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Entry HR harian diperbarui.');
        }

        return response()->json($entry->refresh());
    }

    public function destroy(Request $r,HrDailyEntry $entry)
    {
        $entry->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Entry HR harian dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
