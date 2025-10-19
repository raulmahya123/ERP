<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class KpiIndicatorController extends Controller
{
    public function __construct()
    {
        // Pastikan ada KpiIndicatorPolicy terdaftar di AuthServiceProvider
        $this->authorizeResource(KpiIndicator::class, 'kpi');
    }

    // KpiIndicatorController@index
    public function index(Request $request)
    {
        $siteId = $this->currentSiteId();
        $type   = $request->route('type') ?: $request->query('type');
        $q      = trim((string) $request->query('q', ''));
        $from   = $request->query('from');
        $to     = $request->query('to');

        $items = \App\Models\KpiIndicator::query()
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($type,   fn($qq) => $qq->where('type', $type))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('unit', 'like', "%{$q}%")
                        ->orWhere('notes', 'like', "%{$q}%");
                });
            })
            ->when($from, fn($qq) => $qq->where('date', '>=', $from))
            ->when($to,   fn($qq) => $qq->where('date', '<=', $to))
            ->orderByDesc('date')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        // kalau masih butuh site_code, bisa transform di sini
        return view('admin.hse.kpi_indicators.index', compact('items', 'type', 'q', 'from', 'to'));
    }


    public function create(): View
    {
        $record = new KpiIndicator();
        $sites  = Site::orderBy('name')->get(['id', 'code', 'name']); // kalau mau pilih site manual
        return view('admin.hse.kpi_indicators.create', compact('record', 'sites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['site_id'] = $data['site_id'] ?? $this->currentSiteId();

        $record = KpiIndicator::create($data);

        return redirect()
            ->route('admin.hse.kpi-indicators.edit', ['kpi' => $record])
            ->with('success', 'KPI created.');
    }

    public function edit(KpiIndicator $kpi): View
    {
        $record = $kpi;
        $sites  = Site::orderBy('name')->get(['id', 'code', 'name']);
        return view('admin.hse.kpi_indicators.edit', compact('record', 'sites'));
    }

    public function update(Request $request, KpiIndicator $kpi): RedirectResponse
    {
        $data = $this->validateData($request, $kpi->id);
        $data['site_id'] = $data['site_id'] ?? $this->currentSiteId();

        $kpi->update($data);

        return back()->with('success', 'KPI updated.');
    }

    public function destroy(KpiIndicator $kpi): RedirectResponse
    {
        $kpi->delete();

        return redirect()
            ->route('admin.hse.kpi-indicators.index')
            ->with('success', 'KPI deleted.');
    }

    /* ===== Opsional: Export / Import sederhana (ORM saja) ===== */
    public function exportCsv(Request $request)
    {
        $siteId = $this->currentSiteId();
        $type   = $request->query('type');

        $rows = KpiIndicator::query()
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($type,   fn($qq) => $qq->where('type', $type))
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

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $siteId = $this->currentSiteId();
        $count  = 0;

        if (($fh = fopen($request->file('file')->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($fh);
            while (($row = fgetcsv($fh)) !== false) {
                [$date, $type, $name, $value, $unit, $notes] = array_pad($row, 6, null);
                if (!in_array($type, ['leading', 'lagging', 'operational'], true)) {
                    continue;
                }
                KpiIndicator::updateOrCreate(
                    ['site_id' => $siteId, 'date' => $date, 'type' => $type, 'name' => $name],
                    ['value' => $value ?? 0, 'unit' => $unit, 'notes' => $notes]
                );
                $count++;
            }
            fclose($fh);
        }

        return back()->with('success', "Imported {$count} rows.");
    }

    /** ===== Helper ===== */
    protected function validateData(Request $request, ?string $ignoreId = null): array
    {
        $sessionSiteId = $this->currentSiteId();

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
            if (!$siteId) return;

            $exists = KpiIndicator::query()
                ->where('site_id', $siteId)
                ->where('date', $request->input('date'))
                ->where('type', $request->input('type'))
                ->where('name', $request->input('name'))
                ->when($ignoreId, fn($qq) => $qq->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                $v->errors()->add('name', 'Duplicate KPI for the same site/date/type/name.');
            }
        });

        $data = $validator->validate();
        $data['site_id'] = $data['site_id'] ?? $sessionSiteId;

        return $data;
    }

    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }
}
