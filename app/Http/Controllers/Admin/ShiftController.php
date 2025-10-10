<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
    public function index(Request $r)
    {
        $q = Shift::query()
            ->when($r->site_id ?? session('site_id'), fn($q,$sid)=>$q->where('site_id',$sid))
            ->orderBy('code');

        // UI: table + filter; API: json list
        if (! $r->wantsJson()) {
            $shifts = $q->paginate(50)->appends($r->query());
            return view('admin.shifts.index', compact('shifts'));
        }

        return response()->json($q->get());
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'site_id'       => ['nullable','uuid'],
            'code'          => ['required','string','max:20'],
            'name'          => ['required','string','max:50'],
            'start_at'      => ['required','date_format:H:i'],
            'end_at'        => ['required','date_format:H:i'],
            'break_minutes' => ['nullable','integer','min:0'],
            'overnight'     => ['boolean'],
            'meta'          => ['nullable','array'],
        ]);

        $data['id'] = (string) Str::uuid();

        Shift::updateOrCreate(
            ['site_id' => $data['site_id'] ?? session('site_id'), 'code' => $data['code']],
            collect($data)->except(['id','site_id','code'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.shifts.index')->with('success','Shift disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $r, Shift $shift)
    {
        $data = $r->validate([
            'name'          => ['sometimes','string','max:50'],
            'start_at'      => ['sometimes','date_format:H:i'],
            'end_at'        => ['sometimes','date_format:H:i'],
            'break_minutes' => ['nullable','integer','min:0'],
            'overnight'     => ['boolean'],
            'meta'          => ['nullable','array'],
        ]);

        $shift->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Shift diperbarui.');
        }

        return response()->json($shift->refresh());
    }

    public function destroy(Request $r, Shift $shift)
    {
        $shift->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Shift dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
