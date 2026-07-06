<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Plant\PlantStandardJob;
use Illuminate\Http\Request;

class PlantStandardJobController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PlantStandardJob::class, 'standardJob');
    }

    public function index(Request $request)
    {
        $q = PlantStandardJob::query()
            ->when($request->filled('job_code'), fn($qq) => $qq->where('job_code', 'like', '%'.$request->job_code.'%'))
            ->when($request->filled('job_name'), fn($qq) => $qq->where('job_name', 'like', '%'.$request->job_name.'%'))
            ->when($request->filled('maintenance_type'), fn($qq) => $qq->where('maintenance_type', $request->maintenance_type))
            ->when($request->filled('is_active'), fn($qq) => $qq->where('is_active', $request->boolean('is_active')))
            ->orderBy('job_code');

        $items = $q->paginate(15)->withQueryString();

        $maintenanceTypes = ['preventive' => 'Preventive', 'corrective' => 'Corrective', 'predictive' => 'Predictive', 'breakdown' => 'Breakdown'];
        $durationUoms = ['hour' => 'Hour', 'day' => 'Day', 'week' => 'Week', 'month' => 'Month'];

        return view('admin.plant.plant-standard-jobs.index', compact('items','maintenanceTypes','durationUoms'));
    }

    public function create()
    {
        $maintenanceTypes = ['preventive' => 'Preventive', 'corrective' => 'Corrective', 'predictive' => 'Predictive', 'breakdown' => 'Breakdown'];
        $durationUoms = ['hour' => 'Hour', 'day' => 'Day', 'week' => 'Week', 'month' => 'Month'];

        $plantStandardJob = new PlantStandardJob(['is_active' => true]);

        return view('admin.plant.plant-standard-jobs.create', compact('plantStandardJob','maintenanceTypes','durationUoms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'job_code'          => 'required|string|max:50|unique:plant_standard_jobs,job_code',
            'job_name'          => 'required|string|max:255',
            'equipment_class_id' => 'nullable|string|max:50',
            'description'       => 'nullable|string',
            'estimated_duration' => 'nullable|integer|min:0',
            'duration_uom'      => 'nullable|string|max:20',
            'maintenance_type'  => 'nullable|string|max:50',
            'safety_notes'      => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        PlantStandardJob::create($data);

        return redirect()
            ->route('plant.plant_standard_jobs.index')
            ->with('success', 'Standard Job tersimpan.');
    }

    public function edit(Request $request, PlantStandardJob $plantStandardJob)
    {
        $maintenanceTypes = ['preventive' => 'Preventive', 'corrective' => 'Corrective', 'predictive' => 'Predictive', 'breakdown' => 'Breakdown'];
        $durationUoms = ['hour' => 'Hour', 'day' => 'Day', 'week' => 'Week', 'month' => 'Month'];

        return view('admin.plant.plant-standard-jobs.edit', compact('plantStandardJob','maintenanceTypes','durationUoms'));
    }

    public function update(Request $request, PlantStandardJob $plantStandardJob)
    {
        $data = $request->validate([
            'job_code'          => 'required|string|max:50|unique:plant_standard_jobs,job_code,'.$plantStandardJob->id,
            'job_name'          => 'required|string|max:255',
            'equipment_class_id' => 'nullable|string|max:50',
            'description'       => 'nullable|string',
            'estimated_duration' => 'nullable|integer|min:0',
            'duration_uom'      => 'nullable|string|max:20',
            'maintenance_type'  => 'nullable|string|max:50',
            'safety_notes'      => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        $plantStandardJob->update($data);

        return redirect()
            ->route('plant.plant_standard_jobs.index')
            ->with('success', 'Standard Job diperbarui.');
    }

    public function destroy(Request $request, PlantStandardJob $plantStandardJob)
    {
        $plantStandardJob->delete();

        return redirect()
            ->route('plant.plant_standard_jobs.index')
            ->with('success', 'Standard Job dihapus.');
    }
}
