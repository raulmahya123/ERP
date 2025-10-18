<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreEnvironmentalSampleRequest;
use App\Http\Requests\Hse\UpdateEnvironmentalSampleRequest;
use App\Models\EnvironmentalSample;
use Illuminate\Http\Request;

class EnvironmentalSampleController extends Controller
{
    public function index(Request $request)
    {
        $siteId = session('site_id');
        $q      = trim((string) $request->query('q', ''));
        $type   = $request->query('type');

        $items = EnvironmentalSample::query()
            ->with('site:id,name,code')
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('parameter', 'like', "%{$q}%")
                   ->orWhere('location', 'like', "%{$q}%")
                   ->orWhere('method', 'like', "%{$q}%")
                   ->orWhere('instrument', 'like', "%{$q}%");
            })
            ->when($type, fn($qq) => $qq->where('type', $type))
            ->orderByDesc('sampled_at')
            ->paginate(20);

        return view('admin.hse.env_samples.index', compact('items', 'q', 'type'));
    }

    public function create()
    {
        $sample = new EnvironmentalSample();
        return view('admin.hse.env_samples.create', compact('sample'));
    }

    public function store(StoreEnvironmentalSampleRequest $request)
    {
        $data = $request->validated();
        $data['site_id'] = $data['site_id'] ?? session('site_id');

        $model = EnvironmentalSample::create($data);
        return redirect()->route('admin.hse.environmental-samples.edit', $model)
            ->with('success', 'Sample created.');
    }

    public function edit(EnvironmentalSample $environmental_sample)
    {
        $sample = $environmental_sample;
        return view('admin.hse.env_samples.edit', compact('sample'));
    }

    public function update(UpdateEnvironmentalSampleRequest $request, EnvironmentalSample $environmental_sample)
    {
        $environmental_sample->update($request->validated());
        return back()->with('success', 'Sample updated.');
    }

    public function destroy(EnvironmentalSample $environmental_sample)
    {
        $environmental_sample->delete();
        return redirect()->route('admin.hse.environmental-samples.index')->with('success', 'Sample deleted.');
    }
}
