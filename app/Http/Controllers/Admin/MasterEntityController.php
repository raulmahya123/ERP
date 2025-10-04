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
            'key'        => ['required', 'regex:/^[a-z0-9_]+$/', 'max:50', Rule::unique('master_entities', 'key')],
            'label'      => ['required', 'string', 'max:100'],
            'enabled'    => ['sometimes', 'boolean'],
            'sort'       => ['nullable', 'integer', 'min:0'],
            'schema'     => ['nullable'], // boleh json string
            'icon'       => ['nullable', 'string', 'max:255'],
            'color_from' => ['nullable', 'string', 'max:50'],
            'color_to'   => ['nullable', 'string', 'max:50'],
        ]);
        if (is_string($data['schema'] ?? null)) {
            $data['schema'] = json_decode($data['schema'], true);
        }
        $data['key'] = Str::slug($data['key'], '_');

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
            'key'        => ['required', 'regex:/^[a-z0-9_]+$/', 'max:50', Rule::unique('master_entities', 'key')->ignore($master_entity->id)],
            'label'      => ['required', 'string', 'max:100'],
            'enabled'    => ['sometimes', 'boolean'],
            'sort'       => ['nullable', 'integer', 'min:0'],
            'schema'     => ['nullable'],
            'icon'       => ['nullable', 'string', 'max:255'],
            'color_from' => ['nullable', 'string', 'max:50'],
            'color_to'   => ['nullable', 'string', 'max:50'],
        ]);
        if (is_string($data['schema'] ?? null)) {
            $data['schema'] = json_decode($data['schema'], true);
        }
        $data['key'] = Str::slug($data['key'], '_');

        $master_entity->update($data);

        return redirect()->route('admin.master_entities.index')->with('status', 'Entity updated.');
    }

    // app/Http/Controllers/Admin/MasterEntityController.php
    public function destroy(\Illuminate\Http\Request $r, \App\Models\MasterEntity $master_entity)
    {
        $count = \DB::table('master_records')
            ->where('master_entity_id', $master_entity->id)
            ->count();

        // jika masih dipakai & tidak minta force -> beritahu user
        if ($count > 0 && ! $r->boolean('force')) {
            return back()->withErrors([
                'delete' => "Entity masih dipakai oleh {$count} record. Nonaktifkan (enabled=0) atau pindahkan data terlebih dulu. " .
                    "Atau centang 'Force delete' untuk menghapus beserta datanya.",
            ]);
        }

        \DB::transaction(function () use ($master_entity) {
            // hapus anak2 dulu baru entity
            $ids = \DB::table('master_records')
                ->where('master_entity_id', $master_entity->id)
                ->pluck('id');

            if ($ids->isNotEmpty()) {
                \DB::table('master_record_permissions')->whereIn('master_record_id', $ids)->delete();
                \DB::table('master_records')->whereIn('id', $ids)->delete();
            }

            $master_entity->delete();
        });

        return redirect()->route('admin.master_entities.index')->with('status', 'Entity deleted.');
    }
}
