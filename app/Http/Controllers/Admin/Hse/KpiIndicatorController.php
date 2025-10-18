<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KpiIndicatorController extends Controller
{
    public function index(Request $request)
    {
        $siteId = session('site_id');
        $type   = $request->route('type') ?: $request->query('type'); // leading|lagging|operational
        $q      = trim((string) $request->query('q', ''));
        $from   = $request->query('from'); // YYYY-MM-DD
        $to     = $request->query('to');

        $rows = KpiIndicator::query()
            ->when($siteId, fn ($qq) => $qq->where('site_id', $siteId))
            ->when($type,   fn ($qq) => $qq->where('type', $type))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('unit', 'like', "%{$q}%")
                      ->orWhere('notes', 'like', "%{$q}%");
                });
            })
            ->when($from, fn ($qq) => $qq->where('date', '>=', $from))
            ->when($to,   fn ($qq) => $qq->where('date', '<=', $to))
            ->orderByDesc('date')
            ->orderBy('name')
            ->paginate(25);

        // mapping kode site untuk tabel index
        $siteMap = Site::query()->pluck('code', 'id');
        $rows->getCollection()->transform(function ($r) use ($siteMap) {
            $r->site_code = $r->site_id ? ($siteMap[$r->site_id] ?? null) : null;
            return $r;
        });

        return view('admin.hse.kpis.index', compact('rows', 'type', 'q', 'from', 'to'));
    }

    public function create()
    {
        $record = new KpiIndicator();
        // opsional: kirim daftar site agar bisa dipilih manual (kalau tidak, pakai session('site_id'))
        $sites  = Site::orderBy('name')->get(['id', 'code', 'name']);

        return view('admin.hse.kpis.create', compact('record', 'sites'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['site_id'] = $data['site_id'] ?? session('site_id');

        $record = KpiIndicator::create($data);

        return redirect()
            ->route('admin.hse.kpi-indicators.edit', ['kpi' => $record]) // <— penting: param kpi
            ->with('success', 'KPI created.');
    }

    public function edit(KpiIndicator $kpi)
    {
        $record = $kpi;
        $sites  = Site::orderBy('name')->get(['id', 'code', 'name']); // opsional dropdown

        return view('admin.hse.kpis.edit', compact('record', 'sites'));
    }

    public function update(Request $request, KpiIndicator $kpi)
    {
        $data = $this->validateData($request, $kpi->id);
        $data['site_id'] = $data['site_id'] ?? session('site_id');

        $kpi->update($data);

        return back()->with('success', 'KPI updated.');
    }

    public function destroy(KpiIndicator $kpi)
    {
        $kpi->delete();

        return redirect()
            ->route('admin.hse.kpi-indicators.index')
            ->with('success', 'KPI deleted.');
    }

    /* === Opsional: Export / Import sederhana === */
    public function exportCsv(Request $request)
    {
        $siteId = session('site_id');
        $type   = $request->query('type');

        $rows = KpiIndicator::query()
            ->when($siteId, fn ($qq) => $qq->where('site_id', $siteId))
            ->when($type,   fn ($qq) => $qq->where('type', $type))
            ->orderBy('date')->orderBy('type')->orderBy('name')
            ->get(['date', 'type', 'name', 'value', 'unit', 'notes']);

        $csv  = "date,type,name,value,unit,notes\n";
        foreach ($rows as $r) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                optional($r->date)->format('Y-m-d'),
                $r->type,
                str_replace(',', ' ', $r->name),
                $r->value,
                $r->unit,
                str_replace(["\r", "\n", ","], [' ', ' ', ';'], (string) $r->notes)
            );
        }

        $filename = 'kpi_indicators_' . now()->format('Ymd_His') . '.csv';
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $siteId = session('site_id');
        $fh     = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($fh);
        $count  = 0;

        while (($row = fgetcsv($fh)) !== false) {
            [$date, $type, $name, $value, $unit, $notes] = array_pad($row, 6, null);
            if (!in_array($type, ['leading', 'lagging', 'operational'], true)) {
                continue;
            }
            KpiIndicator::updateOrCreate(
                [
                    'site_id' => $siteId,
                    'date'    => $date,
                    'type'    => $type,
                    'name'    => $name,
                ],
                [
                    'value' => $value ?? 0,
                    'unit'  => $unit,
                    'notes' => $notes,
                ]
            );
            $count++;
        }
        fclose($fh);

        return back()->with('success', "Imported {$count} rows.");
    }

    /** Validate helper */
    protected function validateData(Request $request, ?string $ignoreId = null): array
    {
        $sessionSiteId = session('site_id');

        $validator = Validator::make($request->all(), [
            'site_id' => ['nullable', 'uuid'],
            'date'    => ['required', 'date'],
            'type'    => ['required', 'in:leading,lagging,operational'],
            'name'    => ['required', 'string', 'max:120'],
            'value'   => ['required', 'numeric'],
            'unit'    => ['nullable', 'string', 'max:20'],
            'notes'   => ['nullable', 'string'],
            'meta'    => ['nullable', 'array'],
        ]);

        $validator->after(function ($v) use ($request, $ignoreId, $sessionSiteId) {
            $siteId = $request->input('site_id') ?: $sessionSiteId;

            if (!$siteId) {
                // kalau site tidak ada, biarkan validasi umum yang menolak bila perlu
                return;
            }

            $exists = KpiIndicator::query()
                ->where('site_id', $siteId)
                ->where('date', $request->input('date'))
                ->where('type', $request->input('type'))
                ->where('name', $request->input('name'))
                ->when($ignoreId, fn ($qq) => $qq->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                $v->errors()->add('name', 'Duplicate KPI for the same site/date/type/name.');
            }
        });

        $data = $validator->validate();
        $data['site_id'] = $data['site_id'] ?? $sessionSiteId;

        return $data;
    }
}
