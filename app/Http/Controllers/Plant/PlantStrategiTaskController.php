<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Plant\PlantStrategiTask;
use App\Models\Site;
use Illuminate\Http\Request;

class PlantStrategiTaskController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PlantStrategiTask::class, 'strategiTask');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PlantStrategiTask::query()
            ->with('site')
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('task_type'), fn($qq) => $qq->where('task_type', $request->task_type))
            ->when($request->filled('frequency'), fn($qq) => $qq->where('frequency', $request->frequency))
            ->when($request->filled('is_active'), fn($qq) => $qq->where('is_active', $request->boolean('is_active')))
            ->orderBy('task_code');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $taskTypes = ['preventive' => 'Preventive', 'predictive' => 'Predictive', 'condition_based' => 'Condition Based', 'calendar' => 'Calendar'];
        $frequencies = ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly', 'custom' => 'Custom'];
        $intervalUoms = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'];

        return view('admin.plant.plant-strategi-tasks.index', compact('items','sites','taskTypes','frequencies','intervalUoms','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $taskTypes = ['preventive' => 'Preventive', 'predictive' => 'Predictive', 'condition_based' => 'Condition Based', 'calendar' => 'Calendar'];
        $frequencies = ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly', 'custom' => 'Custom'];
        $intervalUoms = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'];

        $plantStrategiTask = new PlantStrategiTask([
            'site_id'  => $siteId,
            'is_active' => true,
        ]);

        return view('admin.plant.plant-strategi-tasks.create', compact('plantStrategiTask','sites','taskTypes','frequencies','intervalUoms','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'        => 'required|uuid|exists:sites,id',
            'task_code'      => 'required|string|max:50|unique:plant_strategi_tasks,task_code',
            'task_name'      => 'required|string|max:255',
            'task_type'      => 'required|string|max:50',
            'frequency'      => 'nullable|string|max:50',
            'interval_value' => 'nullable|integer|min:0',
            'interval_uom'   => 'nullable|string|max:20',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        PlantStrategiTask::create($data);

        return redirect()
            ->route('plant.plant_strategi_tasks.index', ['site' => $data['site_id']])
            ->with('success', 'Strategi Task tersimpan.');
    }

    public function edit(Request $request, PlantStrategiTask $plantStrategiTask)
    {
        $siteId = $plantStrategiTask->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $taskTypes = ['preventive' => 'Preventive', 'predictive' => 'Predictive', 'condition_based' => 'Condition Based', 'calendar' => 'Calendar'];
        $frequencies = ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly', 'custom' => 'Custom'];
        $intervalUoms = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'];

        return view('admin.plant.plant-strategi-tasks.edit', compact('plantStrategiTask','sites','taskTypes','frequencies','intervalUoms','siteId'));
    }

    public function update(Request $request, PlantStrategiTask $plantStrategiTask)
    {
        $data = $request->validate([
            'site_id'        => 'required|uuid|exists:sites,id',
            'task_code'      => 'required|string|max:50|unique:plant_strategi_tasks,task_code,'.$plantStrategiTask->id,
            'task_name'      => 'required|string|max:255',
            'task_type'      => 'required|string|max:50',
            'frequency'      => 'nullable|string|max:50',
            'interval_value' => 'nullable|integer|min:0',
            'interval_uom'   => 'nullable|string|max:20',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $plantStrategiTask->update($data);

        return redirect()
            ->route('plant.plant_strategi_tasks.index', ['site' => $data['site_id']])
            ->with('success', 'Strategi Task diperbarui.');
    }

    public function destroy(Request $request, PlantStrategiTask $plantStrategiTask)
    {
        $siteId = $plantStrategiTask->site_id;
        $plantStrategiTask->delete();

        return redirect()
            ->route('plant.plant_strategi_tasks.index', ['site' => $siteId])
            ->with('success', 'Strategi Task dihapus.');
    }
}
