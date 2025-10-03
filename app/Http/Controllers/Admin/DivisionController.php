<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = $request->get('q');

        $divisions = Division::when($q, function ($query, $q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('key', 'like', "%$q%");
            })
            ->orderBy('name')
            ->paginate(10);

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
            'key' => 'required|string|max:50|unique:divisions,key',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        Division::create($validated);

        return redirect()->route('admin.divisions.index')
                         ->with('success', 'Divisi berhasil ditambahkan.');
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
            'key' => 'required|string|max:50|unique:divisions,key,' . $division->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $division->update($validated);

        return redirect()->route('admin.divisions.index')
                         ->with('success', 'Divisi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $division->delete();

        return redirect()->route('admin.divisions.index')
                         ->with('success', 'Divisi berhasil dihapus.');
    }
}
