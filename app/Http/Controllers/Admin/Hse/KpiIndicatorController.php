<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use App\Models\KpiDefinition;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class KpiIndicatorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(KpiIndicator::class, 'kpi');
    }

    /** GET /kpi-indicators */
    public function index(Request $request): View
    {
        $siteId   = $this->currentSiteId();
        $type     = $request->route('type') ?: $request->query('type');
        $q        = trim(preg_replace('/\s+/', ' ', (string) $request->query('q', '')));
        $from     = $request->query('from');
        $to       = $request->query('to');
        $defCode  = $request->query('def');
        $defGroup = $request->query('group');

        $from = $from ? Carbon::parse($from)->startOfMonth() : null;
        $to   = $to   ? Carbon::parse($to)->startOfMonth()   : null;

        // NOTE: join utk sorting efisien + select seminimal mungkin
        $query = KpiIndicator::query()
            ->leftJoin('kpi_definitions as kd', 'kd.id', '=', 'kpi_indicators.definition_id')
            ->with(['definition:id,code,name,group,unit', 'site:id,code,name'])
            ->select([
                'kpi_indicators.*',
                'kd.code as _def_code',
                'kd.name as _def_name',
                'kd.group as _def_group',
                'kd.unit as _def_unit',
            ])
            ->when($siteId, fn($qq) => $qq->where('kpi_indicators.site_id', $siteId))
            ->when($type,   fn($qq) => $qq->where('kpi_indicators.type', $type))
            ->when($defCode,fn($qq) => $qq->whereRaw('UPPER(kd.code) = ?', [strtoupper($defCode)]))
            ->when($defGroup, fn($qq) => $qq->where('kd.group', $defGroup))
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%'.$q.'%';
                $qq->where(function ($w) use ($like) {
                    $w->where('kpi_indicators.name', 'like', $like)
                      ->orWhere('kpi_indicators.unit', 'like', $like)
                      ->orWhere('kpi_indicators.notes', 'like', $like)
                      ->orWhere('kd.code', 'like', $like)
                      ->orWhere('kd.name', 'like', $like)
                      ->orWhere('kd.group', 'like', $like);
                });
            })
            ->when($from, fn($qq) => $qq->where('kpi_indicators.date', '>=', $from))
            ->when($to,   fn($qq) => $qq->where('kpi_indicators.date', '<=', $to));

        // Sort: date desc, lalu legacy name / def_name
        $items = $query
            ->orderByDesc('kpi_indicators.date')
            ->orderByRaw('COALESCE(NULLIF(kpi_indicators.name,""), kd.name) asc')
            ->paginate(25)
            ->withQueryString();

        return view('admin.hse.kpi_indicators.index', compact('items', 'type', 'q', 'from', 'to', 'defCode', 'defGroup'));
    }

    public function create(): View
    {
        $record = new KpiIndicator();
        $sites  = Site::orderBy('name')->get(['id','code','name']);
        // cache sederhana utk dropdown
        $defs   = KpiDefinition::orderBy('group')->orderBy('order_no')->get(['id','code','name','group','unit']);
        return view('admin.hse.kpi_indicators.create', compact('record','sites','defs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $data['site_id'] = $data['site_id'] ?? $this->currentSiteId();
        $data['date']    = $this->monthStart($data['date']);

        if (!empty($data['definition_id'])) {
            $def = KpiDefinition::find($data['definition_id'], ['id','name','unit','group']);
            if ($def) {
                $data['name'] = $data['name'] ?: $def->name;
                $data['unit'] = $data['unit'] ?: $def->unit;
                $data['type'] = $data['type'] ?: (in_array($def->group, ['leading','lagging'], true) ? $def->group : 'operational');
            }
        }

        $record = KpiIndicator::create($data);

        return redirect()->route('admin.hse.kpi-indicators.edit', ['kpi' => $record])
            ->with('success', 'KPI created.');
    }

    public function edit(KpiIndicator $kpi): View
    {
        $record = $kpi->load(['definition:id,code,name,group,unit','site:id,code,name']);
        $sites  = Site::orderBy('name')->get(['id','code','name']);
        $defs   = KpiDefinition::orderBy('group')->orderBy('order_no')->get(['id','code','name','group','unit']);
        return view('admin.hse.kpi_indicators.edit', compact('record','sites','defs'));
    }

    public function update(Request $request, KpiIndicator $kpi): RedirectResponse
    {
        $data = $this->validatedPayload($request, $kpi->id);
        $data['site_id'] = $data['site_id'] ?? $this->currentSiteId();
        $data['date']    = $this->monthStart($data['date']);

        if (!empty($data['definition_id'])) {
            $def = KpiDefinition::find($data['definition_id'], ['id','name','unit','group']);
            if ($def) {
                $data['name'] = $data['name'] ?: $def->name;
                $data['unit'] = $data['unit'] ?: $def->unit;
                $data['type'] = $data['type'] ?: (in_array($def->group, ['leading','lagging'], true) ? $def->group : 'operational');
            }
        }

        $kpi->update($data);

        return back()->with('success', 'KPI updated.');
    }

    public function destroy(KpiIndicator $kpi): RedirectResponse
    {
        $kpi->delete();

        return redirect()->route('admin.hse.kpi-indicators.index')
            ->with('success', 'KPI deleted.');
    }

    /* ===== Export / Import ===== */

    public function exportCsv(Request $request)
    {
        $siteId   = $this->currentSiteId();
        $type     = $request->query('type');
        $defCode  = $request->query('def');
        $from     = $request->query('from');
        $to       = $request->query('to');

        $from = $from ? Carbon::parse($from)->startOfMonth() : null;
        $to   = $to   ? Carbon::parse($to)->startOfMonth()   : null;

        $rows = KpiIndicator::query()
            ->leftJoin('kpi_definitions as kd', 'kd.id', '=', 'kpi_indicators.definition_id')
            ->select([
                'kpi_indicators.date','kpi_indicators.type','kpi_indicators.name',
                'kpi_indicators.value','kpi_indicators.unit','kpi_indicators.notes',
                'kd.code as def_code','kd.name as def_name','kd.group as def_group'
            ])
            ->when($siteId, fn($qq) => $qq->where('kpi_indicators.site_id', $siteId))
            ->when($type,   fn($qq) => $qq->where('kpi_indicators.type', $type))
            ->when($defCode,fn($qq) => $qq->whereRaw('UPPER(kd.code)=?', [strtoupper($defCode)]))
            ->when($from, fn($qq) => $qq->where('kpi_indicators.date', '>=', $from))
            ->when($to,   fn($qq) => $qq->where('kpi_indicators.date', '<=', $to))
            ->orderBy('kpi_indicators.date')->orderBy('kpi_indicators.type')->orderBy('kpi_indicators.name')
            ->cursor(); // hemat memori

        // Gunakan fputcsv untuk aman & cepat
        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, ['date','definition_code','definition_name','definition_group','type','name','value','unit','notes']);
        foreach ($rows as $r) {
            fputcsv($fh, [
                optional($r->date)->format('Y-m-d'),
                $r->def_code ?? '',
                $r->def_name ?? '',
                $r->def_group ?? '',
                $r->type ?? '',
                $r->name ?? '',
                $r->value,
                $r->unit ?? '',
                $r->notes ?? '',
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        $filename = 'kpi_indicators_'.now()->format('Ymd_His').'.csv';
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required','file','mimes:csv,txt','max:8192'],
        ]);

        $siteId = $this->currentSiteId();
        if (!$siteId) return back()->with('error','Pilih site dulu.');

        $path = $request->file('file')->getRealPath();
        $fh   = @fopen($path, 'r');
        if ($fh === false) return back()->with('error','Gagal membaca file.');

        $header = fgetcsv($fh) ?: [];
        $header = array_map(fn($h) => strtolower(trim((string)$h)), $header);
        $i      = array_flip($header);
        $isNew  = isset($i['definition_code']);

        // cache map code->id untuk efisiensi
        $codeMap = KpiDefinition::query()->pluck('id', DB::raw('UPPER(code)'))->all();

        $batchLegacy = [];
        $batchNew    = [];
        $count       = 0;

        while (($row = fgetcsv($fh)) !== false) {
            $get = fn($key, $default=null) => isset($i[$key]) ? (trim((string)($row[$i[$key]] ?? '')) ?: $default) : $default;

            $date = $this->monthStart($get('date'));
            if (!$date) continue;

            if ($isNew) {
                $code  = strtoupper((string) $get('definition_code'));
                $defId = $codeMap[$code] ?? null;
                if (!$defId) continue;

                $value = $get('value', '0');
                $unit  = $get('unit');
                $notes = $get('notes');

                $batchNew[] = [
                    'site_id'       => $siteId,
                    'date'          => $date,
                    'definition_id' => $defId,
                    'value'         => is_numeric($value) ? (float)$value : 0,
                    'unit'          => $unit,
                    'notes'         => $notes,
                    'updated_at'    => now(),
                    'created_at'    => now(),
                ];
            } else {
                $type = $get('type'); $name = $get('name');
                if (!in_array($type, ['leading','lagging','operational'], true) || !$name) continue;

                $value = $get('value', '0');
                $unit  = $get('unit');
                $notes = $get('notes');

                $batchLegacy[] = [
                    'site_id'    => $siteId,
                    'date'       => $date,
                    'type'       => $type,
                    'name'       => $name,
                    'value'      => is_numeric($value) ? (float)$value : 0,
                    'unit'       => $unit,
                    'notes'      => $notes,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            // upsert per 500 baris
            if (count($batchNew) >= 500) {
                DB::table('kpi_indicators')->upsert($batchNew, ['site_id','date','definition_id'], ['value','unit','notes','updated_at']);
                $count += count($batchNew);
                $batchNew = [];
            }
            if (count($batchLegacy) >= 500) {
                DB::table('kpi_indicators')->upsert($batchLegacy, ['site_id','date','type','name'], ['value','unit','notes','updated_at']);
                $count += count($batchLegacy);
                $batchLegacy = [];
            }
        }
        fclose($fh);

        DB::transaction(function () use (&$batchNew, &$batchLegacy, &$count) {
            if ($batchNew) {
                DB::table('kpi_indicators')->upsert($batchNew, ['site_id','date','definition_id'], ['value','unit','notes','updated_at']);
                $count += count($batchNew);
            }
            if ($batchLegacy) {
                DB::table('kpi_indicators')->upsert($batchLegacy, ['site_id','date','type','name'], ['value','unit','notes','updated_at']);
                $count += count($batchLegacy);
            }
        });

        return back()->with('success', "Imported {$count} rows.");
    }

    /* ===== Helper ===== */

    protected function validatedPayload(Request $request, ?string $ignoreId = null): array
    {
        $sessionSiteId = $this->currentSiteId();

        $validator = Validator::make($request->all(), [
            'site_id'       => ['nullable','uuid'],
            'definition_id' => ['nullable','uuid','exists:kpi_definitions,id'],
            'date'          => ['required','date'],
            'type'          => ['nullable','in:leading,lagging,operational'],
            'name'          => ['nullable','string','max:120'],
            'value'         => ['required','numeric','min:0'],
            'unit'          => ['nullable','string','max:20'],
            'notes'         => ['nullable','string'],
            'meta'          => ['nullable','array'],
        ]);

        // Uniqueness—gunakan def_id jika ada, kalau tidak legacy combo
        $validator->after(function ($v) use ($request, $ignoreId, $sessionSiteId) {
            $siteId = $request->input('site_id') ?: $sessionSiteId;
            $date   = $this->monthStart($request->input('date'));
            if (!$siteId || !$date) return;

            $q = KpiIndicator::query()
                ->where('site_id', $siteId)
                ->whereDate('date', $date);

            if ($request->filled('definition_id')) {
                $q->where('definition_id', $request->input('definition_id'));
            } else {
                $type = $request->input('type'); $name = $request->input('name');
                if (!$type || !$name) {
                    $v->errors()->add('type','Type & Name diperlukan jika tidak memilih Definition.');
                    return;
                }
                $q->where('type', $type)->where('name', $name);
            }

            if ($ignoreId) $q->where('id','!=',$ignoreId);
            if ($q->exists()) $v->errors()->add('date','Duplicate KPI untuk site/bulan yang sama.');
        });

        // whitelist kolom yang boleh di-fill (hindari overposting)
        $data = collect($validator->validate())->only([
            'site_id','definition_id','date','type','name','value','unit','notes','meta'
        ])->toArray();

        $data['site_id'] = $data['site_id'] ?? $sessionSiteId;
        $data['date']    = $this->monthStart($data['date']);

        return $data;
    }

    protected function monthStart($date): ?Carbon
    {
        try { return Carbon::parse($date)->startOfMonth(); } catch (\Throwable) { return null; }
    }

    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }
}
