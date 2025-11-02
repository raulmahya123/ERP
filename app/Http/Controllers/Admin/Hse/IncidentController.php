<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreIncidentRequest;
use App\Http\Requests\Hse\UpdateIncidentRequest;
use App\Models\Incident;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

final class IncidentController extends Controller
{
    /** Whitelist status agar aman untuk filter & update */
    private const STATUSES = ['reported', 'under_investigation', 'action_in_progress', 'closed'];

    public function __construct()
    {
        // Kaitkan ke IncidentPolicy (map di AuthServiceProvider)
        $this->authorizeResource(Incident::class, 'incident');
    }

    /** GET /hse/incidents */
    public function index(Request $request): View
    {
        $siteId = $this->currentSiteId();

        $qRaw   = (string) $request->query('q', '');
        $q      = $this->sanitizeSearch($qRaw);

        $statusRaw = $request->query('status');
        $status    = is_string($statusRaw) && in_array($statusRaw, self::STATUSES, true) ? $statusRaw : null;

        $from = $this->tryParseDate($request->query('from'));
        $to   = $this->tryParseDate($request->query('to'));

        // per_page clamp 5..100
        $perPage = (int) $request->integer('per_page', 20);
        $perPage = max(5, min($perPage, 100));

        $items = Incident::query()
            ->select([
                'id','code','site_id','reporter_id','occurred_at',
                'location','category','severity','description','status','created_at',
            ])
            ->with([
                'site:id,code,name',
                'reporter:id,name,email',
            ])
            ->when($siteId, fn ($qq) => $qq->where('site_id', $siteId))
            ->when($q !== '', function ($qq) use ($q) {
                $like = "%{$q}%";
                $qq->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                        ->orWhere('category', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when($status, fn ($qq) => $qq->where('status', $status))
            ->when($from, fn ($qq) => $qq->where('occurred_at', '>=', $from->copy()->startOfDay()))
            ->when($to,   fn ($qq) => $qq->where('occurred_at', '<=', $to->copy()->endOfDay()))
            ->orderByDesc('occurred_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.hse.incidents.index', [
            'items'  => $items,
            'q'      => $q,
            'status' => $status,
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    /** GET /hse/incidents/create */
    public function create(): View
    {
        $incident = new Incident([
            'status'      => 'reported',
            'occurred_at' => now(),
        ]);

        return view('admin.hse.incidents.create', compact('incident'));
    }

    /** POST /hse/incidents */
    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $data   = $request->validated();
        $siteId = $data['site_id'] ?? $this->currentSiteId();

        // 🔒 Sederhana: kalau tidak ada/invalid → balik dengan pesan singkat
        if (empty($siteId) || !Str::isUuid($siteId) || !$this->siteExists($siteId)) {
            return back()
                ->withInput()
                ->with('flash_error', 'Kamu lupa pilih site.');
        }

        $data['site_id'] = $siteId;

        // whitelist status (jaga-jaga bila FormRequest longgar)
        $incomingStatus  = $data['status'] ?? 'reported';
        $data['status']  = in_array($incomingStatus, self::STATUSES, true) ? $incomingStatus : 'reported';

        $data['occurred_at'] = $data['occurred_at'] ?? now();
        $data['code']        = $data['code']        ?? $this->generateCode('INC', $data['site_id']);

        $incident = Incident::create($data);

        return redirect()
            ->route('admin.hse.incidents.index')
            ->with('success', 'Incident created.')
            ->with('highlight_id', $incident->id);
    }

    /** GET /hse/incidents/{incident}/edit */
    public function edit(Incident $incident): View
    {
        $incident->loadMissing([
            'site:id,code,name',
            'reporter:id,name,email',
        ]);

        return view('admin.hse.incidents.edit', compact('incident'));
    }

    /** PUT/PATCH /hse/incidents/{incident} */
    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $data = $request->validated();

        // amankan site_id
        $incomingSite    = $data['site_id'] ?? $incident->site_id ?? $this->currentSiteId();
        $data['site_id'] = ($incomingSite && Str::isUuid($incomingSite) && $this->siteExists($incomingSite))
            ? $incomingSite
            : ($incident->site_id ?? $this->currentSiteId());

        // whitelist status (fallback ke nilai lama)
        if (isset($data['status']) && !in_array($data['status'], self::STATUSES, true)) {
            unset($data['status']);
        }

        $data['status']      = $data['status']      ?? ($incident->status ?? 'reported');
        $data['occurred_at'] = $data['occurred_at'] ?? ($incident->occurred_at ?? now());
        $data['code']        = $data['code']        ?? ($incident->code ?? $this->generateCode('INC', $data['site_id']));

        $incident->update($data);

        return redirect()
            ->route('admin.hse.incidents.index')
            ->with('success', 'Incident updated.')
            ->with('highlight_id', $incident->id);
    }

    /** DELETE /hse/incidents/{incident} */
    public function destroy(Incident $incident): RedirectResponse
    {
        $incident->delete();

        return redirect()
            ->route('admin.hse.incidents.index')
            ->with('success', 'Incident deleted.');
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
        if ($q === '') return '';
        $q = mb_substr($q, 0, 60);
        // hanya huruf/angka/spasi/- . _  (hindari wildcard/regex inject utk LIKE)
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
     * Generator kode: {PREFIX}-{SITECODE}-{YYYYMMDD}-{RANDOM}
     */
    protected function generateCode(string $prefix, ?string $siteId = null): string
    {
        $siteCode = 'GEN';
        if ($siteId && Str::isUuid($siteId)) {
            try {
                $code = Site::query()->whereKey($siteId)->value('code');
                if (is_string($code) && $code !== '') {
                    $siteCode = strtoupper($code);
                }
            } catch (\Throwable) {
                // fallback GEN
            }
        }

        return sprintf(
            '%s-%s-%s-%s',
            strtoupper($prefix),
            $siteCode,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }
}
