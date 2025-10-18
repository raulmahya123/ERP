<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StorePicaRequest;
use App\Http\Requests\Hse\UpdatePicaRequest;
use App\Models\Pica;
use Illuminate\Http\Request;

class PicaController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $items = Pica::query()
            ->with(['incident:id,code', 'hazard:id,code', 'owner:id,name'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                   ->orWhere('problem_statement', 'like', "%{$q}%");
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->orderByRaw("CASE status 
                WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'pending_review' THEN 2 
                WHEN 'effective' THEN 3 WHEN 'ineffective' THEN 4 ELSE 5 END")
            ->orderBy('due_date')
            ->paginate(20);

        return view('admin.hse.picas.index', compact('items', 'q', 'status'));
    }

    public function create()
    {
        $pica = new Pica();
        return view('admin.hse.picas.create', compact('pica'));
    }

    public function store(StorePicaRequest $request)
    {
        $pica = Pica::create($request->validated());
        return redirect()->route('admin.hse.picas.edit', $pica)->with('success', 'PICA created.');
    }

    public function edit(Pica $pica)
    {
        return view('admin.hse.picas.edit', compact('pica'));
    }

    public function update(UpdatePicaRequest $request, Pica $pica)
    {
        $pica->update($request->validated());
        return back()->with('success', 'PICA updated.');
    }

    public function destroy(Pica $pica)
    {
        $pica->delete();
        return redirect()->route('admin.hse.picas.index')->with('success', 'PICA deleted.');
    }

    /** Aksi opsional */
    public function markEffective(Pica $pica)
    {
        $pica->update(['status' => 'effective', 'closed_at' => now()]);
        return back()->with('success', 'PICA marked effective.');
    }

    public function markIneffective(Pica $pica)
    {
        $pica->update(['status' => 'ineffective']);
        return back()->with('success', 'PICA marked ineffective.');
    }

    public function close(Pica $pica)
    {
        $pica->update(['status' => 'closed', 'closed_at' => now()]);
        return back()->with('success', 'PICA closed.');
    }
}
