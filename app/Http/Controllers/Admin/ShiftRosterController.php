<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftRoster;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftRosterController extends Controller
{
    public function index(Request $r)
    {
        $q = ShiftRoster::query()
            ->with(['user','shift','site'])
            ->when($r->site_id ?? session('site_id'), fn($q,$sid)=>$q->where('site_id',$sid))
            ->when($r->date, fn($q,$d)=>$q->whereDate('roster_date',$d))
            ->when($r->user_id, fn($q,$u)=>$q->where('user_id',$u))
            ->orderByDesc('roster_date');

        // UI → view, API → JSON
        if (! $r->wantsJson()) {
            $rosters = $q->paginate($r->integer('per_page',25))->appends($r->query());
            return view('admin.shift_rosters.index', compact('rosters'));
        }

        return response()->json($q->paginate($r->integer('per_page',25)));
    }

    public function create()
    {
        return view('admin.shift_rosters.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'site_id'     => ['required','uuid'],
            'user_id'     => ['required','uuid'],
            'roster_date' => ['required','date'],
            'shift_id'    => ['nullable','uuid'],
            'crew_code'   => ['nullable','string','max:20'],
            'remarks'     => ['nullable','string','max:255'],
        ]);

        $data['id'] = (string) Str::uuid();

        ShiftRoster::updateOrCreate(
            [
                'site_id'     => $data['site_id'],
                'user_id'     => $data['user_id'],
                'roster_date' => $data['roster_date'],
            ],
            collect($data)->except(['id','site_id','user_id','roster_date'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.shift-rosters.index')->with('success','Roster shift disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(ShiftRoster $shiftRoster)
    {
        return view('admin.shift_rosters.edit', ['roster'=>$shiftRoster]);
    }

    public function update(Request $r, ShiftRoster $shiftRoster)
    {
        $data = $r->validate([
            'shift_id'  => ['nullable','uuid'],
            'crew_code' => ['nullable','string','max:20'],
            'remarks'   => ['nullable','string','max:255'],
        ]);

        $shiftRoster->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Roster shift diperbarui.');
        }

        return response()->json($shiftRoster->refresh());
    }

    public function destroy(Request $r, ShiftRoster $shiftRoster)
    {
        $shiftRoster->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Roster shift dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
