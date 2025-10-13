<?php
// app/Http/Controllers/Admin/MasterEntityController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterEntity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MasterEntityController extends Controller
{
    public function index()
    {
        $rows = MasterEntity::orderBy('sort')->orderBy('label')->get();
        return view('admin.master_entities.index', compact('rows'));
    }

    public function create()
    {
        return view('admin.master_entities.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'key'        => ['required','regex:/^[a-z0-9_]+$/','max:50', Rule::unique('master_entities','key')],
            'label'      => ['required','string','max:100'],
            'enabled'    => ['nullable','boolean'],
            'sort'       => ['nullable','integer','min:0'],
            'schema'     => ['nullable'], // json string/array
            'icon'       => ['nullable','string','max:255'],
            'color_from' => ['nullable','string','max:50'],
            'color_to'   => ['nullable','string','max:50'],
        ]);

        $data['key']     = Str::slug($data['key'], '_');
        $data['enabled'] = $r->boolean('enabled');

        if (is_string($data['schema'] ?? null)) {
            $data['schema'] = json_decode($data['schema'], true);
        }

        MasterEntity::create($data);

        return redirect()->route('admin.master_entities.index')->with('status', 'Entity created.');
    }

    public function edit(MasterEntity $master_entity)
    {
        return view('admin.master_entities.edit', ['row' => $master_entity]);
    }

    public function update(Request $r, MasterEntity $master_entity)
    {
        $data = $r->validate([
            'key'        => ['required','regex:/^[a-z0-9_]+$/','max:50', Rule::unique('master_entities','key')->ignore($master_entity->id)],
            'label'      => ['required','string','max:100'],
            'enabled'    => ['nullable','boolean'],
            'sort'       => ['nullable','integer','min:0'],
            'schema'     => ['nullable'],
            'icon'       => ['nullable','string','max:255'],
            'color_from' => ['nullable','string','max:50'],
            'color_to'   => ['nullable','string','max:50'],
        ]);

        $data['key']     = Str::slug($data['key'], '_');
        $data['enabled'] = $r->boolean('enabled');

        if (is_string($data['schema'] ?? null)) {
            $data['schema'] = json_decode($data['schema'], true);
        }

        $master_entity->update($data);

        return redirect()->route('admin.master_entities.index')->with('status', 'Entity updated.');
    }

    public function destroy(Request $r, MasterEntity $master_entity)
    {
        $relatedCount = $master_entity->masterRecords()->count();

        if ($relatedCount > 0 && ! $r->boolean('force')) {
            return back()->withErrors([
                'delete' => "Entity masih dipakai oleh {$relatedCount} record. Nonaktifkan (enabled=0) atau pindahkan data terlebih dulu. Atau centang 'Force delete' untuk menghapus beserta datanya.",
            ]);
        }

        // Hapus relasi via Eloquent (tanpa DB facade)
        $master_entity->load(['masterRecords.permissions']);

        foreach ($master_entity->masterRecords as $rec) {
            $rec->permissions()->delete();
            $rec->delete();
        }

        $master_entity->delete();

        return redirect()->route('admin.master_entities.index')->with('status', 'Entity deleted.');
    }
}
