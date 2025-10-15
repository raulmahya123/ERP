<?php

namespace App\Http\Controllers;

use App\Models\ManpowerEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManpowerEntryController extends Controller
{
    /** List & filter entries + siapkan map nama user/equipment untuk tampilan. */
    public function index(Request $request)
    {
        $q = ManpowerEntry::query()
            ->when($request->filled('site_id'), fn($qq) => $qq->where('site_id', $request->site_id))
            ->when($request->filled('entry_type'), fn($qq) => $qq->where('entry_type', $request->entry_type))
            ->when($request->filled('shift_slot'), fn($qq) => $qq->where('shift_slot', $request->shift_slot))
            ->when($request->filled('department'), fn($qq) => $qq->where('department', $request->department))
            ->when($request->filled('user_id'), fn($qq) => $qq->where('user_id', $request->user_id))
            ->when($request->filled('equipment_id'), fn($qq) => $qq->where('equipment_id', $request->equipment_id))
            ->when($request->filled('from'), fn($qq) => $qq->whereDate('date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn($qq) => $qq->whereDate('date', '<=', $request->date('to')))
            ->orderByDesc('date')
            ->orderBy('shift_slot')
            ->orderBy('department')
            ->orderBy('user_id');

        $entries = $q->paginate(20)->withQueryString();

        $userIds  = collect($entries->items())->pluck('user_id')->filter()->unique()->values();
        $equipIds = collect($entries->items())->pluck('equipment_id')->filter()->unique()->values();

        $userMap  = $this->userNameMap($userIds);
        $equipMap = $this->equipmentNameMap($equipIds);

        return view('manpower.entries.index', compact('entries', 'userMap', 'equipMap'));
    }

    /** Form Create: defaults + opsi dropdown. */
    public function create(Request $request)
    {
        $defaults = [
            'site_id'    => $request->get('site_id') ?: $this->resolveActiveSiteId($request),
            'date'       => $request->get('date', now()->toDateString()),
            'shift_slot' => $request->get('shift_slot', 'A'),
            'entry_type' => $request->get('entry_type', ManpowerEntry::TYPE_PLAN),
        ];

        $deptOptions  = $this->departmentOptions();
        $userOptions  = $this->userOptions();
        $equipOptions = $this->equipmentOptions();

        return view('manpower.entries.create', compact('defaults', 'deptOptions', 'userOptions', 'equipOptions'));
    }

    /** Simpan entry baru (redirect ke index). */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        if (empty($data['site_id'])) {
            $data['site_id'] = $this->resolveActiveSiteId($request);
        }

        $data = $this->normalizeByType($data);

        $user = Auth::user();
        $data['meta'] = $this->mergedMeta($data['meta'] ?? [], [
            'created_by'         => $user?->id,
            'created_by_name'    => $user?->name,
            'created_by_role'    => $this->resolveRawRole(),
            'created_from_ip'    => $request->ip(),
            'created_user_agent' => substr((string)$request->userAgent(), 0, 255),
        ]);

        DB::transaction(function () use ($data, $user) {
            if (schema_has_column('manpower_entries', 'created_by')) {
                $data['created_by'] = $user?->id;
            }

            ManpowerEntry::updateOrCreate(
                [
                    'site_id'      => $data['site_id'],
                    'date'         => $data['date'],
                    'shift_slot'   => $data['shift_slot'],
                    'entry_type'   => $data['entry_type'],
                    'department'   => $data['department'] ?? null,
                    'user_id'      => $data['user_id'] ?? null,
                    'equipment_id' => $data['equipment_id'] ?? null,
                ],
                $data
            );
        });

        return redirect()
            ->route('manpower.entries.index')
            ->with('success', 'Manpower entry tersimpan.');
    }

    /** Form Edit: read-only site label + opsi dropdown lain. */
    public function edit(ManpowerEntry $entry)
    {
        $deptOptions  = $this->departmentOptions();
        $userOptions  = $this->userOptions();
        $equipOptions = $this->equipmentOptions();

        $siteRow = null;
        try { $siteRow = DB::table('sites')->where('id', $entry->site_id)->first(); } catch (\Throwable $e) {}
        $siteLabel = $this->siteLabelFromRow($siteRow) ?? '—';

        return view('manpower.entries.edit', compact(
            'entry', 'deptOptions', 'userOptions', 'equipOptions', 'siteLabel'
        ));
    }

    /**
     * Update entry (redirect ke index + flash sukses).
     * Pastikan entry_type ada (pakai existing kalau form tidak kirim).
     */
    public function update(Request $request, ManpowerEntry $entry)
    {
        if (!$request->filled('entry_type')) {
            $request->merge(['entry_type' => $entry->entry_type]);
        }

        $data = $this->validated($request, $entry->id);

        if (empty($data['site_id'])) {
            $data['site_id'] = $this->resolveActiveSiteId($request);
        }

        $data = $this->normalizeByType($data);

        $user = Auth::user();
        $data['meta'] = $this->mergedMeta($entry->meta ?? [], $data['meta'] ?? [], [
            'updated_by'         => $user?->id,
            'updated_by_name'    => $user?->name,
            'updated_by_role'    => $this->resolveRawRole(),
            'updated_from_ip'    => $request->ip(),
            'updated_user_agent' => substr((string)$request->userAgent(), 0, 255),
        ]);

        DB::transaction(function () use ($entry, $data) {
            $entry->fill($data)->save();
        });

        return redirect()
            ->route('manpower.entries.index')
            ->with('success', 'Manpower entry berhasil diperbarui.');
    }

    /** Hapus entry. */
    public function destroy(ManpowerEntry $entry)
    {
        $entry->delete();
        return redirect()->route('manpower.entries.index')->with('success', 'Manpower entry dihapus.');
    }

    /* ===================== Helpers ===================== */

    private function validated(Request $request, ?string $id = null): array
    {
        $types = [ManpowerEntry::TYPE_PLAN, ManpowerEntry::TYPE_REAL, ManpowerEntry::TYPE_ASSIGN];

        $rules = [
            'site_id'    => ['nullable', 'uuid'],
            'date'       => ['required', 'date'],
            'shift_slot' => ['required', Rule::in(['A','B','C','D','NON'])],
            'entry_type' => ['required', Rule::in($types)],
            'note'       => ['nullable','string','max:200'],
            'meta'       => ['nullable','array'],
        ];

        $type = $request->get('entry_type');

        if ($type === ManpowerEntry::TYPE_PLAN) {
            $rules += [
                'department'        => ['required','string','max:50'],
                'planned_headcount' => ['nullable','integer','min:0','max:65535'],
                'planned_operators' => ['nullable','integer','min:0','max:65535'],
                'planned_mechanics' => ['nullable','integer','min:0','max:65535'],
                'planned_helpers'   => ['nullable','integer','min:0','max:65535'],
                'planned_others'    => ['nullable','integer','min:0','max:65535'],
            ];
        } elseif ($type === ManpowerEntry::TYPE_REAL) {
            $rules += [
                'department'         => ['required','string','max:50'],
                'actual_headcount'   => ['nullable','integer','min:0','max:65535'],
                'actual_operators'   => ['nullable','integer','min:0','max:65535'],
                'actual_mechanics'   => ['nullable','integer','min:0','max:65535'],
                'actual_helpers'     => ['nullable','integer','min:0','max:65535'],
                'actual_others'      => ['nullable','integer','min:0','max:65535'],
                'production_tonnage' => ['nullable','numeric','min:0'],
                'manhours'           => ['nullable','numeric','min:0'],
            ];
        } else { // ASSIGN
            $rules += [
                'user_id'       => ['required','uuid'],
                'equipment_id'  => ['nullable','uuid'],
                'role'          => ['nullable','string','max:30'],
                'activity_code' => ['nullable','string','max:50'],
                'remarks'       => ['nullable','string'],
            ];
        }

        return $request->validate($rules);
    }

    private function normalizeByType(array $data): array
    {
        $type = $data['entry_type'];

        $planFields   = ['planned_headcount','planned_operators','planned_mechanics','planned_helpers','planned_others'];
        $realFields   = ['actual_headcount','actual_operators','actual_mechanics','actual_helpers','actual_others','production_tonnage','manhours'];
        $assignFields = ['user_id','equipment_id','role','activity_code','remarks'];

        if ($type === ManpowerEntry::TYPE_PLAN) {
            foreach ($assignFields as $f) { $data[$f] = null; }
            foreach ($realFields as $f)   { $data[$f] = $data[$f] ?? null; }
        } elseif ($type === ManpowerEntry::TYPE_REAL) {
            foreach ($assignFields as $f) { $data[$f] = null; }
            foreach ($planFields as $f)   { $data[$f] = $data[$f] ?? null; }
        } else { // ASSIGN
            $data['department'] = null;
            foreach ($planFields as $f) { $data[$f] = null; }
            foreach ($realFields as $f) { $data[$f] = null; }
        }

        return $data;
    }

    private function resolveActiveSiteId(Request $request): ?string
    {
        $user = Auth::user();
        return $request->session()->get('site_id')
            ?: ($user->default_site_id ?? null);
    }

    private function resolveRawRole(): ?string
    {
        $u = Auth::user();
        if (!$u) return null;
        return $u->role->key
            ?? $u->role->slug
            ?? $u->role->name
            ?? (is_string($u->role ?? null) ? $u->role : null);
    }

    /** Bentuk label site dari row: "CODE - Name" (fallback berbagai kolom). */
    private function siteLabelFromRow($row): ?string
    {
        if (!$row) return null;

        $code = null;
        foreach (['code','site_code','slug'] as $k) {
            if (isset($row->$k) && trim((string)$row->$k) !== '') { $code = trim((string)$row->$k); break; }
        }
        $name = null;
        foreach (['name','site_name','title'] as $k) {
            if (isset($row->$k) && trim((string)$row->$k) !== '') { $name = trim((string)$row->$k); break; }
        }

        if ($code && $name) return "{$code} - {$name}";
        if ($code)          return $code;
        if ($name)          return $name;
        return null;
    }

    private function departmentOptions(): array
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('roles') && DB::table('roles')->count() > 0) {
                return DB::table('roles')->orderBy('name')->pluck('name')
                    ->map(fn($n) => (string)$n)->unique()->values()->all();
            }
        } catch (\Throwable $e) {}

        try {
            return DB::table('manpower_entries')->whereNotNull('department')
                ->distinct()->orderBy('department')->pluck('department')
                ->map(fn($n) => (string)$n)->values()->all();
        } catch (\Throwable $e) {}

        return ['PRODUCTION','MECHANIC','HR','HSE','OPERATION'];
    }

    private function userOptions(): array
    {
        try {
            return DB::table('users')->select('id','name')->orderBy('name')->get()
                ->map(fn($u) => ['id' => (string)$u->id, 'name' => (string)$u->name])->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function equipmentOptions(): array
    {
        try {
            $q = DB::table('assets')->select('id','name','code');
            if (DB::getSchemaBuilder()->hasColumn('assets','category')) {
                $q->where('category', 'equipment');
            }
            return $q->orderBy('name')->get()
                ->map(fn($a) => [
                    'id'   => (string)$a->id,
                    'name' => trim(($a->name ?? '').(($a->code ?? null) ? " ({$a->code})" : ''))
                ])->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function userNameMap($ids)
    {
        if (!$ids || count($ids) === 0) return [];
        try {
            return DB::table('users')->whereIn('id', $ids)->pluck('name','id')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function equipmentNameMap($ids)
    {
        if (!$ids || count($ids) === 0) return [];
        try {
            $rows = DB::table('assets')->whereIn('id', $ids)->get(['id','name','code']);
            $map = [];
            foreach ($rows as $r) {
                $map[$r->id] = trim(($r->name ?? '').(($r->code ?? null) ? " ({$r->code})" : ''));
            }
            return $map;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function mergedMeta(...$chunks): array
    {
        $out = [];
        foreach ($chunks as $part) {
            if (is_array($part)) {
                $out = array_replace_recursive($out, $part);
            }
        }
        return $out;
    }
}

/** Helper aman untuk cek kolom. */
if (!function_exists('schema_has_column')) {
    function schema_has_column(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
