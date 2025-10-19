<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreEnvironmentalSampleRequest;
use App\Http\Requests\Hse\UpdateEnvironmentalSampleRequest;
use App\Models\EnvironmentalSample;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnvironmentalSampleController extends Controller
{
    public function __construct()
    {
        // Resource policy untuk EnvironmentalSample (route param: 'sample' sesuai routes/web.php)
        $this->authorizeResource(EnvironmentalSample::class, 'sample');
    }

    /**
     * GET /environmental-samples
     */
    public function index(Request $request): View
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $type   = $request->query('type'); // 'air' | 'emission' | 'noise'

        $items = EnvironmentalSample::query()
            ->with('site:id,name,code')
            ->when($siteId, fn ($qq) => $qq->where('site_id', $siteId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('parameter', 'like', "%{$q}%")
                      ->orWhere('location', 'like', "%{$q}%")
                      ->orWhere('method', 'like', "%{$q}%")
                      ->orWhere('instrument', 'like', "%{$q}%");
                });
            })
            ->when($type, fn ($qq) => $qq->where('type', $type))
            ->orderByDesc('sampled_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.env_samples.index', compact('items', 'q', 'type'));
    }

    /**
     * GET /environmental-samples/create
     */
    public function create(): View
    {
        $sample = new EnvironmentalSample();
        return view('admin.hse.env_samples.create', compact('sample'));
    }

    /**
     * POST /environmental-samples
     */
    public function store(StoreEnvironmentalSampleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['site_id'] = $data['site_id'] ?? $this->currentSiteId();

        $model = EnvironmentalSample::create($data);

        return redirect()
            ->route('admin.hse.environmental-samples.edit', $model)
            ->with('success', 'Sample created.');
    }

    /**
     * GET /environmental-samples/{sample}/edit
     */
    public function edit(EnvironmentalSample $sample): View
    {
        return view('admin.hse.env_samples.edit', compact('sample'));
    }

    /**
     * PUT/PATCH /environmental-samples/{sample}
     */
    public function update(UpdateEnvironmentalSampleRequest $request, EnvironmentalSample $sample): RedirectResponse
    {
        $sample->update($request->validated());

        return back()->with('success', 'Sample updated.');
    }

    /**
     * DELETE /environmental-samples/{sample}
     */
    public function destroy(EnvironmentalSample $sample): RedirectResponse
    {
        $sample->delete();

        return redirect()
            ->route('admin.hse.environmental-samples.index')
            ->with('success', 'Sample deleted.');
    }

    /* =========================
     | Helpers
     |=========================*/
    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }
}
