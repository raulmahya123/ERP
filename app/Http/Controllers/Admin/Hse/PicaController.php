<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StorePicaRequest;
use App\Http\Requests\Hse\UpdatePicaRequest;
use App\Models\Incident;
use App\Models\HazardReport;
use App\Models\Pica;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PicaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Pica::class, 'pica');

        $this->middleware('can:markEffective,pica')->only('markEffective');
        $this->middleware('can:markIneffective,pica')->only('markIneffective');
        $this->middleware('can:close,pica')->only('close');
    }

    /** GET /picas */
    public function index(Request $request): View
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $items = Pica::query()
            ->with([
                'incident:id,code,site_id',
                'hazard:id,code,site_id',
                'owner:id,name',
            ])
            // filter site via incident/hazard
            ->when($siteId, function ($qq) use ($siteId) {
                $qq->where(function ($w) use ($siteId) {
                    $w->whereHas('incident', fn($i) => $i->where('site_id', $siteId))
                      ->orWhereHas('hazard',   fn($h) => $h->where('site_id', $siteId));
                });
            })
            // cari di code, reference, judul & konten, serta kode incident/hazard terkait
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('reference', 'like', "%{$q}%")
                      ->orWhere('title', 'like', "%{$q}%")
                      ->orWhere('problem_statement', 'like', "%{$q}%")
                      ->orWhere('root_cause', 'like', "%{$q}%")
                      ->orWhere('preventive_action', 'like', "%{$q}%")
                      ->orWhereHas('incident', fn($i) => $i->where('code', 'like', "%{$q}%"))
                      ->orWhereHas('hazard',   fn($h) => $h->where('code', 'like', "%{$q}%"));
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->orderBy('status')      // urut per status enum
            ->orderBy('due_date')    // lalu due date
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.picas.index', compact('items', 'q', 'status'));
    }

    /** GET /picas/create */
    public function create(): View
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')->limit(50)
            ->get(['id','code','occurred_at','site_id']);

        $hazards = HazardReport::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('observed_at')->limit(50)
            ->get(['id','code','observed_at','site_id']);

        $owners = User::query()
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')->get(['id','name','email']);

        $pica = new Pica();

        return view('admin.hse.picas.create', compact('pica','incidents','hazards','owners'));
    }

    /** POST /picas */
    public function store(StorePicaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Defaults penting
        $data['status'] = $data['status'] ?? 'open';

        // Tentukan reference & site code dari relasi terpilih
        [$reference, $siteCode] = $this->resolveReferenceAndSiteCode(
            $data['related_incident_id'] ?? null,
            $data['related_hazard_id'] ?? null
        );

        $data['reference'] = $data['reference'] ?? $reference;
        $data['code']      = $data['code'] ?? $this->makePicaCode('PCA', $siteCode);

        // Optionally set owner default ke user login jika kosong
        if (empty($data['owner_id']) && auth()->check()) {
            $data['owner_id'] = auth()->id();
        }

        $pica = Pica::create($data);

        return redirect()
            ->route('admin.hse.picas.edit', $pica)
            ->with('success', 'PICA created.');
    }

    /** GET /picas/{pica}/edit */
    public function edit(Pica $pica): View
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')->limit(50)
            ->get(['id','code','occurred_at','site_id']);

        $hazards = HazardReport::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('observed_at')->limit(50)
            ->get(['id','code','observed_at','site_id']);

        $owners = User::query()
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')->get(['id','name','email']);

        return view('admin.hse.picas.edit', compact('pica','incidents','hazards','owners'));
    }

    /** PUT/PATCH /picas/{pica} */
    public function update(UpdatePicaRequest $request, Pica $pica): RedirectResponse
    {
        $data = $request->validated();

        // Pertahankan nilai inti kalau gak diisi
        $data['status'] = $data['status'] ?? $pica->status ?? 'open';
        $data['owner_id'] = $data['owner_id'] ?? $pica->owner_id ?? (auth()->id() ?: null);

        // Jika relasi incident/hazard berubah, refresh reference & regenerate code bila belum ada
        $relatedIncidentId = $data['related_incident_id'] ?? $pica->related_incident_id;
        $relatedHazardId   = $data['related_hazard_id'] ?? $pica->related_hazard_id;

        if (empty($data['reference']) || $relatedIncidentId !== $pica->related_incident_id || $relatedHazardId !== $pica->related_hazard_id) {
            [$reference, $siteCode] = $this->resolveReferenceAndSiteCode($relatedIncidentId, $relatedHazardId);
            $data['reference'] = $data['reference'] ?? $reference;
            if (empty($pica->code) && empty($data['code'])) {
                $data['code'] = $this->makePicaCode('PCA', $siteCode);
            }
        }

        // Pastikan code tidak kosong
        if (empty($data['code'])) {
            // pakai site code dari existing relasi
            [$ref, $sc] = $this->resolveReferenceAndSiteCode($relatedIncidentId, $relatedHazardId);
            $data['code'] = $pica->code ?? $this->makePicaCode('PCA', $sc);
        }

        $pica->update($data);

        return back()->with('success', 'PICA updated.');
    }

    /** DELETE /picas/{pica} */
    public function destroy(Pica $pica): RedirectResponse
    {
        $pica->delete();

        return redirect()
            ->route('admin.hse.picas.index')
            ->with('success', 'PICA deleted.');
    }

    /** POST /picas/{pica}/mark-effective */
    public function markEffective(Pica $pica): RedirectResponse
    {
        $pica->update([
            'status'    => 'effective',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'PICA marked effective.');
    }

    /** POST /picas/{pica}/mark-ineffective */
    public function markIneffective(Pica $pica): RedirectResponse
    {
        $pica->update(['status' => 'ineffective']);

        return back()->with('success', 'PICA marked ineffective.');
    }

    /** POST /picas/{pica}/close */
    public function close(Pica $pica): RedirectResponse
    {
        $pica->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'PICA closed.');
    }

    /* =========================
     | Helpers
     |=========================*/
    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }

    /**
     * Kembalikan [reference, siteCode] dari incident/hazard terpilih.
     * - Prioritas incident jika keduanya diisi.
     */
    protected function resolveReferenceAndSiteCode(?string $incidentId, ?string $hazardId): array
    {
        if ($incidentId) {
            $inc = Incident::query()->select('code','site_id')->find($incidentId);
            if ($inc) {
                $siteCode = $this->getSiteCode($inc->site_id);
                return [$inc->code, $siteCode];
            }
        }
        if ($hazardId) {
            $hz = HazardReport::query()->select('code','site_id')->find($hazardId);
            if ($hz) {
                $siteCode = $this->getSiteCode($hz->site_id);
                return [$hz->code, $siteCode];
            }
        }
        return [null, 'GEN'];
    }

    protected function getSiteCode(?string $siteId): string
    {
        if (!$siteId) return 'GEN';
        return strtoupper((string) (\App\Models\Site::query()->whereKey($siteId)->value('code') ?? 'GEN'));
    }

    protected function makePicaCode(string $prefix, ?string $siteCode = 'GEN'): string
    {
        $siteCode = $siteCode ? strtoupper($siteCode) : 'GEN';
        return sprintf('%s-%s-%s-%s',
            $prefix,
            $siteCode,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }
}
