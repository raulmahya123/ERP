<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreIncidentRequest;
use App\Http\Requests\Hse\UpdateIncidentRequest;
use App\Models\Incident;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class IncidentController extends Controller
{
    public function __construct()
    {
        // Kaitkan ke IncidentPolicy (butuh mapping di AuthServiceProvider)
        $this->authorizeResource(Incident::class, 'incident');
    }

    public function index(Request $request)
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');          // reported|under_investigation|action_in_progress|closed
        $from   = $request->query('from');            // yyyy-mm-dd
        $to     = $request->query('to');              // yyyy-mm-dd

        $items = Incident::query()
            ->with(['site:id,name,code', 'reporter:id,name,email'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('category', 'like', "%{$q}%")
                      ->orWhere('location', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($from, fn($qq) => $qq->where('occurred_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to,   fn($qq) => $qq->where('occurred_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.incidents.index', compact('items', 'q', 'status', 'from', 'to'));
    }

    public function create()
    {
        $incident = new Incident();
        return view('admin.hse.incidents.create', compact('incident'));
    }

    public function store(StoreIncidentRequest $request)
    {
        $data = $request->validated();

        // Default yang sering kosong dari form
        $data['site_id']     = $data['site_id']     ?? $this->currentSiteId();
        $data['occurred_at'] = $data['occurred_at'] ?? now();
        $data['status']      = $data['status']      ?? 'reported';
        $data['code']        = $data['code']        ?? $this->generateCode('INC', $data['site_id']);

        $incident = Incident::create($data);

        return redirect()
            ->route('admin.hse.incidents.edit', $incident)
            ->with('success', 'Incident created.');
    }

    public function edit(Incident $incident)
    {
        return view('admin.hse.incidents.edit', compact('incident'));
    }

    public function update(UpdateIncidentRequest $request, Incident $incident)
    {
        $data = $request->validated();

        // Jangan kosongkan nilai penting bila tidak dikirim
        $data['site_id']     = $data['site_id']     ?? $incident->site_id ?? $this->currentSiteId();
        $data['occurred_at'] = $data['occurred_at'] ?? $incident->occurred_at ?? now();
        $data['status']      = $data['status']      ?? $incident->status ?? 'reported';
        $data['code']        = $data['code']        ?? $incident->code ?? $this->generateCode('INC', $data['site_id']);

        $incident->update($data);

        return back()->with('success', 'Incident updated.');
    }

    public function destroy(Incident $incident)
    {
        $incident->delete();
        return redirect()->route('admin.hse.incidents.index')->with('success', 'Incident deleted.');
    }

    /** Helpers */
    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }

    /**
     * Generator kode: INC-{SITECODE}-{YYYYMMDD}-{RANDOM}
     */
    protected function generateCode(string $prefix, ?string $siteId = null): string
    {
        $siteCode = 'GEN';
        if ($siteId) {
            $siteCode = strtoupper((string) (Site::query()->whereKey($siteId)->value('code') ?? 'GEN'));
        }
        return sprintf('%s-%s-%s-%s',
            $prefix,
            $siteCode,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }
}
