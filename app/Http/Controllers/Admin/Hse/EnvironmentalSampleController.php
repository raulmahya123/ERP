<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreEnvironmentalSampleRequest;
use App\Http\Requests\Hse\UpdateEnvironmentalSampleRequest;
use App\Http\Requests\Hse\UpdateEnvironmentalSampleStatusRequest;
use App\Models\EnvironmentalSample;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

final class EnvironmentalSampleController extends Controller
{
    /** whitelist untuk filter (hindari nilai liar di query) */
    private const TYPES   = ['air','emission','noise'];
    private const STATUSES = ['draft','submitted','verified'];

    public function __construct()
    {
        // Policy otomatis via {sample}
        $this->authorizeResource(EnvironmentalSample::class, 'sample');
    }

    /** GET /environmental-samples */
    public function index(Request $request): View
    {
        $sessionSiteId = $this->currentSiteId();

        $qRaw   = (string) $request->query('q', '');
        $q      = $this->sanitizeSearch($qRaw);

        // whitelist type/status supaya aman untuk query
        $typeRaw   = $request->query('type');
        $statusRaw = $request->query('status');

        $type   = is_string($typeRaw)   && in_array($typeRaw, self::TYPES, true)     ? $typeRaw   : null;
        $status = is_string($statusRaw) && in_array($statusRaw, self::STATUSES, true) ? $statusRaw : null;

        $from = $this->tryParseDate($request->query('from'));
        $to   = $this->tryParseDate($request->query('to'));

        // optional explicit site filter -> harus UUID & ada di DB
        $siteFilter = $request->query('site_id');
        $siteFilter = (is_string($siteFilter) && Str::isUuid($siteFilter) && $this->siteExists($siteFilter))
            ? $siteFilter
            : null;

        $effectiveSiteId = $siteFilter ?: $sessionSiteId;

        // per_page clamp 5..100
        $perPage = (int) $request->integer('per_page', 20);
        $perPage = max(5, min($perPage, 100));

        $items = EnvironmentalSample::query()
            ->select([
                'id','code','site_id','sampled_at','type','location',
                'parameter','value','unit','method','instrument',
                'limit_value','is_compliant','status','created_at'
            ])
            ->with(['site:id,code,name'])
            ->when($effectiveSiteId, fn ($q2) => $q2->where('site_id', $effectiveSiteId))
            ->when($q !== '', function ($q2) use ($q) {
                $like = "%{$q}%";
                $q2->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                      ->orWhere('parameter', 'like', $like)
                      ->orWhere('location', 'like', $like)
                      ->orWhere('method', 'like', $like)
                      ->orWhere('instrument', 'like', $like);
                });
            })
            ->when($type,   fn ($q2) => $q2->where('type', $type))
            ->when($status, fn ($q2) => $q2->where('status', $status))
            ->when($from,   fn ($q2) => $q2->where('sampled_at', '>=', $from->copy()->startOfDay()))
            ->when($to,     fn ($q2) => $q2->where('sampled_at', '<=', $to->copy()->endOfDay()))
            ->orderByDesc('sampled_at')
            ->paginate($perPage)
            ->withQueryString();

        // opsi Site utk filter (dipakai di index.blade)
        $sites = Site::query()
            ->select('id','code','name')
            ->orderBy('code')
            ->limit(200)
            ->get();

        // konsisten pakai folder: admin/hse/env_samples/*
        return view('admin.hse.env_samples.index', [
            'items'  => $items,
            'sites'  => $sites,
            'q'      => $q,
            'type'   => $type,
            'status' => $status,
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    /** GET /environmental-samples/create */
    public function create(): View
    {
        $sample = new EnvironmentalSample([
            'status'     => 'draft',
            'sampled_at' => now(),
        ]);

        $sites = Site::query()
            ->select('id','code','name')
            ->orderBy('code')
            ->limit(200)
            ->get();

        return view('admin.hse.env_samples.create', compact('sample','sites'));
    }

    /** POST /environmental-samples */
    public function store(StoreEnvironmentalSampleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // pastikan site_id valid (kalau dikirim manual dari form)
        $siteId = $data['site_id'] ?? $this->currentSiteId();
        $data['site_id'] = ($siteId && Str::isUuid($siteId) && $this->siteExists($siteId)) ? $siteId : $this->currentSiteId();

        $data['status']     = $data['status']     ?? 'draft';
        $data['sampled_at'] = $data['sampled_at'] ?? now();
        $data['code']       = $data['code']       ?? $this->makeSampleCode($data['site_id']);

        $model = EnvironmentalSample::create($data);

        // balik ke INDEX + optional highlight
        return redirect()
            ->route('admin.hse.environmental-samples.index')
            ->with('success', 'Sample created.')
            ->with('highlight_id', $model->id);
    }

    /** GET /environmental-samples/{sample} */
    public function show(EnvironmentalSample $sample): View
    {
        $sample->loadMissing(['site:id,code,name']);

        return view('admin.hse.env_samples.show', compact('sample'));
    }

    /** GET /environmental-samples/{sample}/edit */
    public function edit(EnvironmentalSample $sample): View
    {
        $sample->loadMissing(['site:id,code,name']);

        $sites = Site::query()
            ->select('id','code','name')
            ->orderBy('code')
            ->limit(200)
            ->get();

        return view('admin.hse.env_samples.edit', compact('sample','sites'));
    }

    /** PUT/PATCH /environmental-samples/{sample} */
    public function update(UpdateEnvironmentalSampleRequest $request, EnvironmentalSample $sample): RedirectResponse
    {
        $data = $request->validated();

        // amankan perubahan site_id (hanya jika valid & exist)
        $incomingSite = $data['site_id'] ?? $sample->site_id ?? $this->currentSiteId();
        $data['site_id'] = ($incomingSite && Str::isUuid($incomingSite) && $this->siteExists($incomingSite))
            ? $incomingSite
            : ($sample->site_id ?? $this->currentSiteId());

        // status/type sebaiknya sudah divalidasi di FormRequest; fallback jaga-jaga
        if (isset($data['status']) && !in_array($data['status'], self::STATUSES, true)) {
            unset($data['status']);
        }

        if (isset($data['type']) && !in_array($data['type'], self::TYPES, true)) {
            unset($data['type']);
        }

        $data['status']     = $data['status']     ?? ($sample->status ?? 'draft');
        $data['sampled_at'] = $data['sampled_at'] ?? ($sample->sampled_at ?? now());
        $data['code']       = $data['code']       ?? ($sample->code ?? $this->makeSampleCode($data['site_id']));

        $sample->update($data);

        return redirect()
            ->route('admin.hse.environmental-samples.index')
            ->with('success', 'Sample updated.')
            ->with('highlight_id', $sample->id);
    }

    /** DELETE /environmental-samples/{sample} */
    public function destroy(EnvironmentalSample $sample): RedirectResponse
    {
        $sample->delete();

        return redirect()
            ->route('admin.hse.environmental-samples.index')
            ->with('success', 'Sample deleted.');
    }

    /** PATCH /environmental-samples/{sample}/status (ubah status cepat dari halaman show) */
    public function updateStatus(UpdateEnvironmentalSampleStatusRequest $request, EnvironmentalSample $sample): RedirectResponse
    {
        $status = $request->string('status')->toString();

        // jaga-jaga kalau FormRequest belum whitelist
        if (!in_array($status, self::STATUSES, true)) {
            return back()->with('error', 'Invalid status value.');
        }

        $sample->update(['status' => $status]);

        return back()->with('success', 'Status updated.');
    }

    /* ================= Helpers ================ */

    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }

    private function siteExists(?string $siteId): bool
    {
        if (!$siteId || !Str::isUuid($siteId)) return false;
        try {
            return Site::query()->whereKey($siteId)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function sanitizeSearch(string $q): string
    {
        $q = trim($q);
        if ($q == '') return '';
        $q = mb_substr($q, 0, 60);
        // hanya huruf/angka/spasi/- . _  (hindari wildcard/regex inject)
        return trim(preg_replace('/[^\p{L}\p{N}\s\-\._]/u', '', $q) ?? '');
    }

    private function tryParseDate(?string $date): ?Carbon
    {
        if (!$date) return null;
        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Format code: ENV-{SITECODE}-{YYYYMMDD}-{RANDOM}
     */
    private function makeSampleCode(?string $siteId): string
    {
        $siteCode = 'GEN';
        if ($siteId) {
            try {
                $code = Site::query()->whereKey($siteId)->value('code');
                if (is_string($code) && $code !== '') {
                    $siteCode = strtoupper($code);
                }
            } catch (\Throwable) {
                // fallback tetap 'GEN'
            }
        }

        return sprintf('ENV-%s-%s-%s', $siteCode, now()->format('Ymd'), Str::upper(Str::random(6)));
    }
}
