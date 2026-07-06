<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\MaterialMaster;
use Illuminate\Http\Request;

class MaterialMasterController extends Controller
{
    public function index(Request $request)
    {
        $q = MaterialMaster::query()
            ->when($request->filled('material_code'), fn($qq) => $qq->where('material_code', 'like', '%' . $request->material_code . '%'))
            ->when($request->filled('material_name'), fn($qq) => $qq->where('material_name', 'like', '%' . $request->material_name . '%'))
            ->when($request->filled('material_type'), fn($qq) => $qq->where('material_type', $request->material_type))
            ->when($request->filled('material_group'), fn($qq) => $qq->where('material_group', $request->material_group))
            ->when($request->filled('is_active'), fn($qq) => $qq->where('is_active', $request->is_active))
            ->orderBy('material_code');

        $items = $q->paginate(15)->withQueryString();

        $materialTypes = MaterialMaster::select('material_type')->distinct()->whereNotNull('material_type')->pluck('material_type');
        $materialGroups = MaterialMaster::select('material_group')->distinct()->whereNotNull('material_group')->pluck('material_group');

        return view('admin.scm.material-masters.index', compact('items', 'materialTypes', 'materialGroups'));
    }

    public function create()
    {
        $materialMaster = new MaterialMaster(['is_active' => true]);
        return view('admin.scm.material-masters.create', compact('materialMaster'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'material_code' => 'required|string|max:50|unique:scm_material_masters,material_code',
            'material_name' => 'required|string|max:255',
            'material_type' => 'nullable|string|max:100',
            'material_group' => 'nullable|string|max:100',
            'base_uom' => 'nullable|string|max:20',
            'weight' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        MaterialMaster::create($data);

        return redirect()
            ->route('scm.material_masters.index')
            ->with('success', 'Material Master tersimpan.');
    }

    public function edit(MaterialMaster $material_master)
    {
        return view('admin.scm.material-masters.edit', compact('material_master'));
    }

    public function update(Request $request, MaterialMaster $material_master)
    {
        $data = $request->validate([
            'material_code' => 'required|string|max:50|unique:scm_material_masters,material_code,' . $material_master->id,
            'material_name' => 'required|string|max:255',
            'material_type' => 'nullable|string|max:100',
            'material_group' => 'nullable|string|max:100',
            'base_uom' => 'nullable|string|max:20',
            'weight' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $material_master->update($data);

        return redirect()
            ->route('scm.material_masters.index')
            ->with('success', 'Material Master diperbarui.');
    }

    public function destroy(MaterialMaster $material_master)
    {
        $material_master->delete();

        return redirect()
            ->route('scm.material_masters.index')
            ->with('success', 'Material Master dihapus.');
    }
}
