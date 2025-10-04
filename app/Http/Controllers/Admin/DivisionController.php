<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $divisions = Division::when($q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('key', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Log hanya ketika ada pencarian agar tidak bising
        if ($q !== '') {
            AuditLogger::log('division_search', 'Division', [
                'q' => $q,
                'result_count' => $divisions->total(),
            ]);
        }

        return view('admin.divisions.index', compact('divisions', 'q'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.divisions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'         => 'required|string|max:50|unique:divisions,key',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $division = Division::create($validated);

            AuditLogger::log('division_created', $division, [
                'after' => $division->toArray(),
            ]);

            return redirect()
                ->route('admin.divisions.index')
                ->with('success', 'Divisi berhasil ditambahkan.');
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Division $division)
    {
        return view('admin.divisions.edit', compact('division'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'key'         => 'required|string|max:50|unique:divisions,key,' . $division->id,
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $before = $division->toArray();

        return DB::transaction(function () use ($division, $validated, $before) {
            $division->update($validated);
            $after = $division->fresh()->toArray();

            AuditLogger::log('division_updated', $division, [
                'before' => $before,
                'after'  => $after,
            ]);

            return redirect()
                ->route('admin.divisions.index')
                ->with('success', 'Divisi berhasil diperbarui.');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $before = $division->toArray();

        return DB::transaction(function () use ($division, $before) {
            $division->delete();

            AuditLogger::log('division_deleted', $division, [
                'before' => $before,
            ]);

            return redirect()
                ->route('admin.divisions.index')
                ->with('success', 'Divisi berhasil dihapus.');
        });
    }
}
