<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrDailyEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Shift;

class HrDailyEntryController extends Controller
{
    /** Label default untuk UI (boleh tidak 1:1 dengan enum DB) */
    private const DEFAULT_TYPES = [
        'leave'        => 'Cuti',
        'permit'       => 'Izin',
        'sick'         => 'Sakit',
        'shift_change' => 'Pergantian Shift',
    ];

    /* =========================
     |  UTIL: site aktif, schema helper
     |=========================*/
    private function activeSiteId(Request $r): ?string
    {
        return $r->input('site_id')
            ?? session('site_id')
            ?? optional($r->user())->default_site_id
            ?? null;
    }

    private function schemaHasColumn(string $table, string $col): bool
    {
        try { return Schema::hasColumn($table, $col); }
        catch (\Throwable $e) { return false; }
    }

    /* =========================================================
     |  CONFIG dinamis: params->hr
     |   - hr.config_keys.{suffix} (opsional per-site)
     |   - fallback: entry_{suffix}
     |   suffix: types|meta_schemas|meta_form|approval_schemas|print_templates
     |=========================================================*/
    private function getSiteConfigRowForHr(?string $siteId = null): ?object
    {
        if (!Schema::hasTable('site_configs')) return null;
        $siteId = $siteId ?: $this->activeSiteId(request());
        return DB::table('site_configs')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('created_at')
            ->first(['id','params']);
    }

    private function hrParams(): array
    {
        $row = $this->getSiteConfigRowForHr();
        if (!$row) return [];
        $params = is_string($row->params) ? (json_decode($row->params, true) ?: []) :
                   (is_array($row->params) ? $row->params : (json_decode(json_encode($row->params ?? []), true) ?: []));
        return (array) ($params['hr'] ?? []);
    }

    private function writeHrParams(array $hr): void
    {
        $row = $this->getSiteConfigRowForHr();
        if (!$row) throw new \RuntimeException('Row site_configs untuk site aktif belum ada.');
        $params = is_string($row->params) ? (json_decode($row->params, true) ?: []) :
                   (is_array($row->params) ? $row->params : (json_decode(json_encode($row->params ?? []), true) ?: []));
        $params['hr'] = $hr;
        DB::table('site_configs')->where('id', $row->id)->update([
            'params'     => json_encode($params, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    private function configKey(string $suffix): string
    {
        $hr = $this->hrParams();
        $map = (array) ($hr['config_keys'] ?? []);
        $fallback = [
            'types'            => 'entry_types',
            'meta_schemas'     => 'entry_meta_schemas',
            'meta_form'        => 'entry_meta_form_config',
            'approval_schemas' => 'entry_approval_schemas',
            'print_templates'  => 'entry_print_templates',
        ];
        return (string) ($map[$suffix] ?? $fallback[$suffix] ?? ('entry_'.$suffix));
    }

    private function loadCfg(string $suffix): mixed
    {
        $key = $this->configKey($suffix);
        $hr  = $this->hrParams();
        return $hr[$key] ?? null;
    }

    private function saveCfg(string $suffix, mixed $value): void
    {
        $key = $this->configKey($suffix);
        $hr  = $this->hrParams();
        $hr[$key] = $value;
        $this->writeHrParams($hr);
    }

    /* =========================================================
     |  ENUM 'type' dinamis dari DB (MySQL). Fallback default.
     |=========================================================*/
    private function dbEnumOptions(string $table, string $column): array
    {
        try {
            $col = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);
            if (!$col || !isset($col->Type)) return [];
            if (preg_match("/^enum\\('(.*)'\\)$/i", $col->Type, $m)) {
                return array_map(fn($v) => str_replace("\\'", "'", trim($v)), explode("','", $m[1]));
            }
        } catch (\Throwable $e) {}
        return [];
    }

    private function allowedTypesFromDb(): array
    {
        $opts = $this->dbEnumOptions('hr_daily_entries', 'type');
        return !empty($opts) ? $opts : array_keys(self::DEFAULT_TYPES);
    }

    /* =========================================================
     |  TYPES (tanpa hardcode key config)
     |=========================================================*/
    private function getTypes(): array
    {
        $cfg = $this->loadCfg('types');
        $labels = is_array($cfg) && !empty($cfg) ? $cfg : self::DEFAULT_TYPES;
        $allowed = $this->allowedTypesFromDb();

        $out = [];
        foreach ($allowed as $k) {
            $kk = Str::of($k)->lower()->snake()->toString();
            $out[$kk] = (string)($labels[$kk] ?? (self::DEFAULT_TYPES[$kk] ?? Str::headline($kk)));
        }
        return $out;
    }

    private function saveTypes(array $types): void
    {
        $allowed = $this->allowedTypesFromDb();
        $payload = [];
        foreach ($types as $k => $label) {
            $kk = Str::of($k)->lower()->snake()->toString();
            if (!in_array($kk, $allowed, true)) continue;
            $payload[$kk] = (string)$label;
        }
        $this->saveCfg('types', $payload);
    }

    private function protectedTypeKeys(): array
    {
        return $this->allowedTypesFromDb();
    }

    /** UI: Blade manage */
    public function typesIndex()
    {
        $types     = $this->getTypes();
        $protected = $this->protectedTypeKeys();
        return view('admin.hr_entries.types_manage', compact('types','protected'));
    }

    public function typesStore(Request $r)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $data = $r->validate([
            'key'   => ['required', 'string', 'max:30'],
            'label' => ['required', 'string', 'max:60'],
        ]);
        $key = Str::of($data['key'])->lower()->snake()->toString();

        $allowed = $this->allowedTypesFromDb();
        if (!in_array($key, $allowed, true)) {
            return back()->withErrors(['key' => 'Type tidak didukung enum DB.'])->withInput();
        }

        $types = $this->getTypes();
        if (isset($types[$key])) {
            return back()->withErrors(['key' => 'Key sudah ada.'])->withInput();
        }
        $types[$key] = trim($data['label']);
        $this->saveTypes($types);

        return back()->with('success', 'Jenis berhasil ditambahkan.');
    }

    public function typesUpdate(Request $r, string $key)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $key  = Str::of($key)->lower()->snake()->toString();
        $data = $r->validate([
            'label'   => ['nullable', 'string', 'max:60'],
            'new_key' => ['nullable', 'string', 'max:30'],
        ]);

        $types = $this->getTypes();
        if (!isset($types[$key])) return back()->withErrors(['key' => 'Type tidak ditemukan.']);

        if (isset($data['label'])) $types[$key] = trim($data['label']);

        if (!empty($data['new_key'])) {
            $newKey = Str::of($data['new_key'])->lower()->snake()->toString();
            $allowed = $this->allowedTypesFromDb();
            if (!in_array($newKey, $allowed, true)) {
                return back()->withErrors(['new_key' => 'new_key tidak didukung enum DB.']);
            }
            if ($newKey !== $key) {
                if (isset($types[$newKey])) {
                    return back()->withErrors(['new_key' => 'new_key sudah dipakai.']);
                }
                DB::table('hr_daily_entries')->where('type', $key)->update(['type' => $newKey]);
                $label = $types[$key];
                unset($types[$key]);
                $types[$newKey] = $label;
                $key = $newKey;
            }
        }

        $this->saveTypes($types);
        return back()->with('success', 'Jenis berhasil diperbarui.');
    }

    public function typesDestroy(Request $r, string $key)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $key   = Str::of($key)->lower()->snake()->toString();
        $types = $this->getTypes();

        if (!isset($types[$key])) {
            return back()->withErrors(['key' => 'Type tidak ditemukan.']);
        }
        if (in_array($key, $this->protectedTypeKeys(), true)) {
            return back()->withErrors(['key' => 'Type default/enum tidak boleh dihapus.']);
        }
        if (HrDailyEntry::where('type', $key)->exists()) {
            return back()->withErrors(['key' => 'Tidak bisa dihapus karena sedang dipakai oleh data entry.']);
        }
        unset($types[$key]);
        $this->saveTypes($types);
        return back()->with('success', 'Jenis berhasil dihapus.');
    }

    public function typesReorder(Request $r)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $data = $r->validate([
            'order'   => ['required', 'array', 'min:1'],
            'order.*' => ['string'],
        ]);

        $current = $this->getTypes();
        $order   = array_map(fn($k) => Str::of($k)->lower()->snake()->toString(), $data['order']);

        $new = [];
        foreach ($order as $k) if (isset($current[$k])) $new[$k] = $current[$k];
        foreach ($current as $k => $lbl) if (!isset($new[$k])) $new[$k] = $lbl;

        $this->saveTypes($new);
        return back()->with('success', 'Urutan jenis berhasil disimpan.');
    }

    /* =========================================================
     |  META SCHEMA (dinamis) – rules & form config
     |=========================================================*/
    private function metaRulesBuiltin(?string $type): array
    {
        $base = ['meta' => ['nullable', 'array']];

        switch ($type) {
            case 'leave':
                $base += [
                    'meta.leave_type'    => ['required', 'string', 'in:annual,unpaid,marriage,maternity,paternity,other'],
                    'meta.duration_days' => ['nullable', 'numeric', 'min:0', 'max:30'],
                    'meta.half_day'      => ['nullable', 'boolean'],
                    'meta.attachment_id' => ['nullable', 'uuid'],
                    'meta.notes'         => ['nullable', 'string'],
                ];
                break;

            case 'permit':
                $base += [
                    'meta.permit_category' => ['required', 'string', 'in:personal,official,urgent,other'],
                    'meta.hours'           => ['nullable', 'numeric', 'min:0', 'max:24'],
                    'meta.start_time'      => ['nullable', 'date_format:H:i'],
                    'meta.end_time'        => ['nullable', 'date_format:H:i'],
                    'meta.attachment_id'   => ['nullable', 'uuid'],
                    'meta.notes'           => ['nullable', 'string'],
                ];
                break;

            case 'sick':
                $base += [
                    'meta.doctor_note'   => ['nullable', 'boolean'],
                    'meta.diagnosis'     => ['nullable', 'string', 'max:200'],
                    'meta.inpatient'     => ['nullable', 'boolean'],
                    'meta.bpjs_claim'    => ['nullable', 'boolean'],
                    'meta.attachment_id' => ['nullable', 'uuid'],
                    'meta.notes'         => ['nullable', 'string'],
                ];
                break;

            case 'shift_change':
                $base += [
                    'meta.effective_from' => ['nullable', 'date'],
                    'meta.requested_by'   => ['nullable', 'uuid'],
                    'meta.approved_by'    => ['nullable', 'uuid'],
                    'meta.notes'          => ['nullable', 'string'],
                ];
                break;

            default:
                $base += ['meta.notes' => ['nullable', 'string']];
        }
        return $base;
    }

    private function getMetaSchemas(): array
    {
        $cfg = $this->loadCfg('meta_schemas');
        return is_array($cfg) ? $cfg : [];
    }

    private function saveMetaSchemas(array $map): void
    {
        $this->saveCfg('meta_schemas', $map);
    }

    private function getMetaFormConfig(): array
    {
        $cfg = $this->loadCfg('meta_form');
        return is_array($cfg) ? $cfg : [];
    }

    private function saveMetaFormConfig(array $map): void
    {
        $this->saveCfg('meta_form', $map);
    }

    private function metaRules(?string $type): array
    {
        $builtin = $this->metaRulesBuiltin($type);
        $custom  = ($this->getMetaSchemas())[$type] ?? null;
        if (is_array($custom)) {
            foreach ($custom as $k => $rule) $builtin[$k] = $rule;
        }
        return $builtin;
    }

    /* =========================================================
     |  META: helper dinamis dari rules (tanpa VIRTUAL_META_KEYS)
     |=========================================================*/
    private function metaKeysForType(?string $type): array
    {
        $keys = [];
        foreach ($this->metaRules($type) as $k => $rule) {
            if (str_starts_with($k, 'meta.')) $keys[] = substr($k, 5);
        }
        return array_values(array_unique($keys));
    }

    private function isBooleanRule($rule): bool
    {
        $s = is_array($rule) ? implode('|', $rule) : (string)$rule;
        return str_contains(strtolower($s), 'boolean');
    }

    private function booleanMetaKeysForType(?string $type): array
    {
        $bools = [];
        foreach ($this->metaRules($type) as $k => $rule) {
            if (str_starts_with($k, 'meta.') && $this->isBooleanRule($rule)) {
                $bools[] = substr($k, 5);
            }
        }
        return $bools;
    }

    private function normalizeMetaInput($val): array
    {
        if ($val instanceof \Stringable) $val = (string)$val;
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }
        if (is_object($val)) return json_decode(json_encode($val), true) ?: [];
        return is_array($val) ? $val : [];
    }

    private function mergeRequestIntoMeta(Request $r, ?string $type, array $baseMeta = []): array
    {
        $meta = $baseMeta;
        $allowedMeta = $this->metaKeysForType($type);

        foreach ($allowedMeta as $field) {
            if ($r->has($field) && !Arr::has($meta, $field)) {
                $val = $r->input($field);
                if ($val !== null && $val !== '') Arr::set($meta, $field, $val);
            }
        }

        $boolKeys = $this->booleanMetaKeysForType($type);
        foreach ($boolKeys as $b) {
            if (array_key_exists($b, $meta)) {
                $v = $meta[$b];
                $parsed = filter_var($v, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                $meta[$b] = $parsed !== null ? $parsed : in_array($v, [1,'1','on','yes',true], true);
            }
        }
        return $meta;
    }

    /* =========================================================
     |  META FORM CONFIG (Blade) — konsisten pakai .manage
     |=========================================================*/
    public function metaFormConfigIndex()
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $types = $this->getTypes();
        $map   = $this->getMetaFormConfig();
        return view('admin.hr_entries.meta_form.index', compact('types','map'));
    }

    public function metaFormConfigManage(?string $type = null)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $type ? Str::of($type)->lower()->snake()->toString() : null;
        if (!$type) {
            $types = $this->getTypes();
            $type  = array_key_first($types);
        }

        $all    = $this->getMetaFormConfig();
        $config = $all[$type] ?? ['fields' => []];
        $json   = json_encode($config['fields'] ?? [], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

        return view('admin.hr_entries.meta_form.manage', compact('type','config','json'));
    }

    public function metaFormConfigUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();

        $fields = [];
        if ($r->filled('fields_json')) {
            $fields = json_decode($r->input('fields_json'), true);
            if (!is_array($fields)) {
                return back()->withErrors(['fields_json' => 'Format JSON tidak valid.'])->withInput();
            }
        } else {
            $data = $r->validate([
                'fields'                        => ['required','array','min:1'],
                'fields.*.key'                  => ['required','string','max:60'],
                'fields.*.label'                => ['required','string','max:120'],
                'fields.*.type'                 => ['required','string','in:text,textarea,number,date,time,datetime,select,radio,checkbox,file,toggle'],
                'fields.*.required'             => ['nullable','boolean'],
                'fields.*.placeholder'          => ['nullable','string','max:190'],
                'fields.*.help'                 => ['nullable','string','max:190'],
                'fields.*.default'              => ['nullable'],
                'fields.*.attrs'                => ['nullable','array'],
                'fields.*.options'              => ['nullable','array'],
                'fields.*.options.*.value'      => ['required_with:fields.*.options','string','max:120'],
                'fields.*.options.*.label'      => ['required_with:fields.*.options','string','max:120'],
            ]);
            $fields = $data['fields'];
        }

        $norm = [];
        foreach ($fields as $f) {
            if (!is_array($f)) continue;
            $k = $f['key'] ?? '';
            if (Str::startsWith($k, 'meta.')) $k = substr($k, 5);
            $f['key'] = Str::of($k)->lower()->snake()->toString();
            $norm[] = $f;
        }

        $all = $this->getMetaFormConfig();
        $all[$type] = ['fields' => array_values($norm)];
        $this->saveMetaFormConfig($all);

        return redirect()->route('admin.hr-entries.meta-form.manage', $type)
            ->with('success', 'Meta Form Config disimpan.');
    }

    public function metaFormConfigDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();
        $all  = $this->getMetaFormConfig();
        if (!isset($all[$type])) {
            return redirect()->route('admin.hr-entries.meta-form.index')
                ->with('error', 'Meta form config tidak ditemukan.');
        }
        unset($all[$type]);
        $this->saveMetaFormConfig($all);

        return redirect()->route('admin.hr-entries.meta-form.index')
            ->with('success', 'Meta Form Config dihapus.');
    }

    /* =========================================================
     |  META SCHEMAS (Blade) — konsisten pakai .manage
     |=========================================================*/
    public function metaSchemasIndex()
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $types = $this->getTypes();
        $map   = $this->getMetaSchemas();
        return view('admin.hr_entries.meta_schemas.index', compact('types','map'));
    }

    public function metaSchemasManage(?string $type = null)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $type ? Str::of($type)->lower()->snake()->toString() : null;
        if (!$type) {
            $types = $this->getTypes();
            $type  = array_key_first($types);
        }

        $rules = $this->getMetaSchemas()[$type] ?? [];
        $json  = json_encode($rules, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

        return view('admin.hr_entries.meta_schemas.manage', compact('type','rules','json'));
    }

    public function metaSchemasUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();

        $normalized = [];
        if ($r->filled('rules_json')) {
            $raw = json_decode($r->input('rules_json'), true);
            if (!is_array($raw)) {
                return back()->withErrors(['rules_json' => 'Format JSON tidak valid.'])->withInput();
            }
            foreach ($raw as $k => $rule) {
                $kk = Str::startsWith($k, 'meta.') ? $k : 'meta.'.Str::of($k)->lower()->snake()->toString();
                $normalized[$kk] = is_string($rule) ? array_values(array_filter(explode('|', $rule))) : (array) $rule;
            }
        } else {
            $raw = $r->input('rules', []);
            if (!is_array($raw) || empty($raw)) {
                return back()->withErrors(['rules_json' => 'Isi aturan pada JSON textarea.'])->withInput();
            }
            foreach ($raw as $k => $rule) {
                $kk = Str::startsWith($k, 'meta.') ? $k : 'meta.'.Str::of($k)->lower()->snake()->toString();
                $normalized[$kk] = is_string($rule) ? array_values(array_filter(explode('|', $rule))) : (array) $rule;
            }
        }

        $all = $this->getMetaSchemas();
        $all[$type] = $normalized;
        $this->saveMetaSchemas($all);

        return redirect()->route('admin.hr-entries.meta-schema.manage', $type)
            ->with('success', 'Meta Schema disimpan.');
    }

    public function metaSchemasDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();
        $all  = $this->getMetaSchemas();
        if (!isset($all[$type])) {
            return redirect()->route('admin.hr-entries.meta-schema.index')
                ->with('error', 'Meta schema tidak ditemukan.');
        }
        unset($all[$type]);
        $this->saveMetaSchemas($all);

        return redirect()->route('admin.hr-entries.meta-schema.index')
            ->with('success', 'Meta Schema dihapus.');
    }

    /* =========================================================
     |  APPROVAL FLOW (pakai config dinamis jg) — Blade
     |=========================================================*/
    private function getApprovalSchemas(): array
    {
        $cfg = $this->loadCfg('approval_schemas');
        return is_array($cfg) ? $cfg : [];
    }

    private function saveApprovalSchemas(array $map): void
    {
        $this->saveCfg('approval_schemas', $map);
    }

    public function approvalSchemasIndex()
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $types = $this->getTypes();
        $map   = $this->getApprovalSchemas();
        return view('admin.hr_entries.approval_schemas.index', compact('types','map'));
    }

    public function approvalSchemasShow(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type   = Str::of($type)->lower()->snake()->toString();
        $config = $this->getApprovalSchemas()[$type] ?? ['stages' => []];
        $json   = json_encode($config, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

        return view('admin.hr_entries.approval_schemas.manage', compact('type','config','json'));
    }

    public function approvalSchemasUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();
        $allowed = $this->allowedTypesFromDb();
        if (!in_array($type, $allowed, true)) {
            return back()->withErrors(['type' => 'Type tidak didukung enum DB.']);
        }

        if ($r->filled('stages_json')) {
            $parsed = json_decode($r->input('stages_json'), true);
            if (!is_array($parsed) || !isset($parsed['stages'])) {
                return back()->withErrors(['stages_json'=>'JSON harus berisi key "stages".'])->withInput();
            }
            $data = ['stages' => array_values($parsed['stages'])];
        } else {
            $data = $r->validate([
                'stages' => ['required', 'array', 'min:1'],
                'stages.*.key'              => ['required', 'string', 'max:40'],
                'stages.*.label'            => ['required', 'string', 'max:80'],
                'stages.*.roles'            => ['required', 'array', 'min:1'],
                'stages.*.roles.*'          => ['string', 'max:40'],
                'stages.*.all_must_approve' => ['nullable', 'boolean'],
            ]);
        }

        $all = $this->getApprovalSchemas();
        $all[$type] = $data;
        $this->saveApprovalSchemas($all);

        return redirect()->route('admin.hr-entries.approval.schemas.show', $type)
            ->with('success', 'Approval schema disimpan.');
    }

    public function approvalSchemasDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();
        $all  = $this->getApprovalSchemas();
        if (!isset($all[$type])) {
            return redirect()->route('admin.hr-entries.approval.schemas.index')
                ->with('error', 'Approval schema tidak ditemukan.');
        }
        unset($all[$type]);
        $this->saveApprovalSchemas($all);

        return redirect()->route('admin.hr-entries.approval.schemas.index')
            ->with('success', 'Approval schema dihapus.');
    }

    public function approvalSubmit(Request $r, HrDailyEntry $entry)
    {
        Gate::authorize('submit', $entry);
        $trail = [
            'submitted_at' => now()->toDateTimeString(),
            'submitted_by' => $r->user()?->id,
            'stages'       => [],
        ];
        $meta = (array)($entry->meta ?? []);
        $meta['_approval'] = $trail;

        $entry->update([
            'status' => 'pending',
            'meta'   => $meta,
            'updated_by' => $this->schemaHasColumn('hr_daily_entries', 'updated_by') ? $r->user()?->id : null,
        ]);

        return back()->with('success', 'Pengajuan telah dikirim.');
    }

    public function approvalApprove(Request $r, HrDailyEntry $entry)
    {
        Gate::authorize('approve', $entry);

        $meta  = (array)($entry->meta ?? []);
        $trail = (array)($meta['_approval'] ?? []);
        $trail['stages']   = array_values((array)($trail['stages'] ?? []));
        $trail['stages'][] = [
            'action' => 'approved',
            'at'     => now()->toDateTimeString(),
            'by'     => $r->user()?->id,
            'note'   => (string)$r->input('note', ''),
        ];
        $meta['_approval'] = $trail;

        $entry->status      = 'approved';
        $entry->approved_by = $r->user()?->id;
        $entry->approved_at = now();
        if ($this->schemaHasColumn('hr_daily_entries', 'updated_by')) {
            $entry->updated_by = $r->user()?->id;
        }
        $entry->meta = $meta;
        $entry->save();

        return back()->with('success', 'Entry disetujui.');
    }

    public function approvalReject(Request $r, HrDailyEntry $entry)
    {
        Gate::authorize('reject', $entry);

        $meta  = (array)($entry->meta ?? []);
        $trail = (array)($meta['_approval'] ?? []);
        $trail['stages']   = array_values((array)($trail['stages'] ?? []));
        $trail['stages'][] = [
            'action' => 'rejected',
            'at'     => now()->toDateTimeString(),
            'by'     => $r->user()?->id,
            'note'   => (string)$r->input('note', ''),
        ];
        $meta['_approval'] = $trail;

        $entry->status      = 'rejected';
        $entry->approved_by = $r->user()?->id;
        $entry->approved_at = now();
        if ($this->schemaHasColumn('hr_daily_entries', 'updated_by')) {
            $entry->updated_by = $r->user()?->id;
        }
        $entry->meta = $meta;
        $entry->save();

        return back()->with('success', 'Entry ditolak.');
    }

    /* =========================================================
     |  PRINT TEMPLATES (dinamis key) — Blade
     |=========================================================*/
    private function getPrintTemplates(): array
    {
        $cfg = $this->loadCfg('print_templates');
        return is_array($cfg) ? $cfg : [];
    }

    private function savePrintTemplates(array $map): void
    {
        $this->saveCfg('print_templates', $map);
    }

    public function printTemplatesIndex()
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $types = $this->getTypes();
        $map   = $this->getPrintTemplates();
        return view('admin.hr_entries.print_templates.index', compact('types','map'));
    }

    public function printTemplatesShow(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();
        $all  = $this->getPrintTemplates();
        $tpl  = $all[$type] ?? ['view' => '', 'paper' => 'A4', 'orientation' => 'portrait', 'columns' => [], 'header' => '', 'footer' => ''];
        $json = json_encode($tpl, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

        return view('admin.hr_entries.print_templates.manage', compact('type','tpl','json'));
    }

    public function printTemplatesUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();

        if ($r->filled('template_json')) {
            $def = json_decode($r->input('template_json'), true);
            if (!is_array($def)) {
                return back()->withErrors(['template_json' => 'Format JSON tidak valid.'])->withInput();
            }
        } else {
            $def = $r->validate([
                'view'        => ['nullable', 'string', 'max:190'],
                'paper'       => ['nullable', 'string', 'max:30'],
                'orientation' => ['nullable', 'string', 'in:portrait,landscape'],
                'header'      => ['nullable', 'string', 'max:190'],
                'footer'      => ['nullable', 'string', 'max:190'],
                'columns'     => ['nullable', 'array'],
                'columns.*.key'   => ['required_with:columns', 'string', 'max:190'],
                'columns.*.label' => ['required_with:columns', 'string', 'max:190'],
            ]);
        }

        $all = $this->getPrintTemplates();
        $cur = $all[$type] ?? [];
        $cur['view']        = $def['view']        ?? ($cur['view']        ?? '');
        $cur['paper']       = $def['paper']       ?? ($cur['paper']       ?? 'A4');
        $cur['orientation'] = $def['orientation'] ?? ($cur['orientation'] ?? 'portrait');
        $cur['header']      = $def['header']      ?? ($cur['header']      ?? '');
        $cur['footer']      = $def['footer']      ?? ($cur['footer']      ?? '');
        if (isset($def['columns'])) $cur['columns'] = array_values($def['columns']);

        $all[$type] = $cur;
        $this->savePrintTemplates($all);

        return redirect()->route('admin.hr-entries.print-templates.show', $type)
            ->with('success', 'Print template disimpan.');
    }

    public function printTemplatesDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = Str::of($type)->lower()->snake()->toString();
        $all  = $this->getPrintTemplates();
        if (!isset($all[$type])) {
            return redirect()->route('admin.hr-entries.print-templates.index')
                ->with('error', 'Print template tidak ditemukan.');
        }
        unset($all[$type]);
        $this->savePrintTemplates($all);

        return redirect()->route('admin.hr-entries.print-templates.index')
            ->with('success', 'Print template dihapus.');
    }

    public function print(Request $r)
    {
        $type      = (string) $r->input('type', '');
        $templates = $this->getPrintTemplates();

        $q = HrDailyEntry::query()
            ->with(['user', 'site', 'approver'])
            ->when($type, fn($qq) => $qq->where('type', $type))
            ->when($r->filled('status'), fn($qq) => $qq->where('status', $r->input('status')))
            ->when($r->filled(['date_from', 'date_to']),
                fn($qq) => $qq->whereBetween('date', [$r->input('date_from'), $r->input('date_to')])
            )
            ->latest('date')
            ->limit(2000);

        $entries = $q->get();

        $tpl      = $type ? ($templates[$type] ?? []) : [];
        $viewName = $tpl['view'] ?? '';

        $vars = [
            'entries'     => $entries,
            'type'        => $type ?: null,
            'typesMap'    => $this->getTypes(),
            'template'    => $tpl,
            'paper'       => $tpl['paper'] ?? 'A4',
            'orientation' => $tpl['orientation'] ?? 'portrait',
            'columns'     => $tpl['columns'] ?? [],
            'header'      => $tpl['header'] ?? '',
            'footer'      => $tpl['footer'] ?? '',
            'get' => function ($model, string $path) {
                $val = data_get($model, $path);
                if ($val instanceof \Carbon\CarbonInterface) return $val->format('Y-m-d H:i');
                return $val;
            },
        ];

        if ($viewName && view()->exists($viewName)) {
            return view($viewName, $vars);
        }
        return view('admin.hr_entries.print_generic', $vars);
    }

    /* =========================================================
     |  CRUD ENTRY
     |=========================================================*/
    public function index(Request $r)
    {
        $q = HrDailyEntry::query()
            ->with(['user', 'fromShift', 'toShift', 'site', 'approver'])
            ->when($r->input('site_id', session('site_id')), fn($qq, $sid) => $qq->where('site_id', $sid))
            ->when($r->user_id, fn($qq, $uid) => $qq->where('user_id', $uid))
            ->when($r->type, fn($qq, $t) => $qq->where('type', $t))
            ->when($r->status, fn($qq, $s) => $qq->where('status', $s))
            ->when($r->date, fn($qq, $d) => $qq->whereDate('date', $d))
            ->when(
                $r->filled(['date_from', 'date_to']) && !$r->date,
                fn($qq) => $qq->whereBetween('date', [$r->input('date_from'), $r->input('date_to')])
            )
            ->when($r->q, function ($qq) use ($r) {
                $s = trim((string)$r->q);
                $qq->where(fn($w) => $w->where('reason', 'like', "%$s%")->orWhere('code', 'like', "%$s%"));
            })
            ->orderByDesc('date')->orderBy('user_id');

        $entries = $q->paginate($r->integer('per_page', 25))->appends($r->query());

        if (!$r->wantsJson()) {
            $types = $this->getTypes();
            $activeSiteId = $this->activeSiteId($r);
            return view('admin.hr_entries.index', compact('entries', 'types', 'activeSiteId'));
        }
        return response()->json($entries);
    }

    public function create(Request $r)
    {
        $activeSiteId = $r->input('site_id', session('site_id'));

        $types = $this->getTypes();

        $users = User::select('id', 'name', 'email', 'employee_code')
            ->when($activeSiteId && method_exists(new User, 'scopeInSite'), fn($q) => $q->inSite($activeSiteId))
            ->orderBy('name')
            ->limit(500)
            ->get();

        $shifts = Shift::query()
            ->when($activeSiteId, fn($q) => $q->where('site_id', $activeSiteId))
            ->orderBy('code')
            ->get();

        return view('admin.hr_entries.create', compact('types', 'activeSiteId', 'users', 'shifts'));
    }

    /** AJAX lookup user */
    public function searchUsers(Request $r)
    {
        $q   = trim((string) $r->input('q', ''));
        $sid = $r->input('site_id', session('site_id'));
        $like = '%'.str_replace(['%','_'], ['\%','\_'], $q).'%';

        $usersQ = User::select('id','name','email','employee_code')
            ->when($q !== '', function ($qb) use ($like) {
                $qb->where(function ($qq) use ($like) {
                    $qq->where('name', 'like', $like)
                       ->orWhere('email', 'like', $like)
                       ->orWhere('employee_code', 'like', $like);
                });
            });

        if ($sid && method_exists(new User, 'scopeInSite')) {
            $usersQ->inSite($sid);
        }

        $users = $usersQ->orderBy('name')->limit(20)->get();

        return response()->json(
            $users->map(fn($u) => [
                'id'   => $u->id,
                'text' => trim(($u->name ?: $u->email).' — '.($u->employee_code ?: $u->email)),
            ])
        );
    }

    public function store(Request $r)
    {
        $siteId = $this->activeSiteId($r);
        if (!$siteId) {
            return back()->withErrors(['site_id' => 'Site aktif tidak ditemukan. Pilih site terlebih dahulu.'])->withInput();
        }

        $types = $this->getTypes();
        $rules = [
            'user_id'       => ['required', 'uuid'],
            'date'          => ['required', 'date'],
            'type'          => ['required', Rule::in(array_keys($types))],
            'code'          => ['nullable', 'string', 'max:20'],
            'reason'        => ['nullable', 'string'],
            'from_shift_id' => [Rule::requiredIf($r->type === 'shift_change'), 'nullable', 'uuid'],
            'to_shift_id'   => [
                Rule::requiredIf($r->type === 'shift_change'),
                'nullable',
                'uuid',
                function ($attr, $val, $fail) use ($r) {
                    if ($r->type === 'shift_change' && $val && $val === $r->from_shift_id) {
                        $fail('Shift tujuan harus berbeda dengan shift asal.');
                    }
                }
            ],
        ];
        $rules = array_merge($rules, $this->metaRules($r->type));
        $data  = $r->validate($rules);

        $metaBase = $this->normalizeMetaInput($data['meta'] ?? $r->input('meta', []));
        $meta     = $this->mergeRequestIntoMeta($r, $r->type, $metaBase);
        $jsonMeta = json_encode($meta, JSON_UNESCAPED_UNICODE);

        $payload = [
            'site_id'       => $siteId,
            'user_id'       => $data['user_id'],
            'date'          => $data['date'],
            'type'          => $data['type'],
            'code'          => $data['code']   ?? null,
            'reason'        => $data['reason'] ?? null,
            'from_shift_id' => $data['from_shift_id'] ?? null,
            'to_shift_id'   => $data['to_shift_id']   ?? null,
            'meta'          => $jsonMeta,
        ];

        if ($this->schemaHasColumn('hr_daily_entries', 'created_by')) {
            $payload['created_by'] = $r->user()?->id;
        }

        DB::transaction(function () use ($payload) {
            HrDailyEntry::updateOrCreate(
                [
                    'site_id' => $payload['site_id'],
                    'user_id' => $payload['user_id'],
                    'date'    => $payload['date'],
                    'type'    => $payload['type'], // FIX: jangan 'type    '
                    'code'    => $payload['code'],
                ],
                Arr::except($payload, ['site_id','user_id','date','type','code'])
            );
        });

        if (!$r->wantsJson()) {
            return redirect()->route('admin.hr-entries.index')->with('success', 'Entry HR harian disimpan.');
        }
        return response()->json(['ok' => true]);
    }

    public function edit(Request $r, HrDailyEntry $entry)
    {
        $types        = $this->getTypes();
        $activeSiteId = $this->activeSiteId($r);
        $metaForm     = $this->getMetaFormConfig();
        $resolvedType = old('type', $entry->type ?? '');

        return view('admin.hr_entries.edit', compact('entry', 'types', 'activeSiteId', 'metaForm', 'resolvedType'));
    }

    public function update(Request $r, HrDailyEntry $entry)
    {
        $rules = [
            'reason'        => ['nullable', 'string'],
            'from_shift_id' => [Rule::requiredIf($entry->type === 'shift_change'), 'nullable', 'uuid'],
            'to_shift_id'   => [
                Rule::requiredIf($entry->type === 'shift_change'),
                'nullable',
                'uuid',
                function ($attr, $val, $fail) use ($r, $entry) {
                    $from = $r->input('from_shift_id', $entry->from_shift_id);
                    if ($entry->type === 'shift_change' && $val && $val === $from) {
                        $fail('Shift tujuan harus berbeda dengan shift asal.');
                    }
                }
            ],
        ];
        $rules = array_merge($rules, $this->metaRules($entry->type));
        $data  = $r->validate($rules);

        $incoming = $this->normalizeMetaInput($data['meta'] ?? $r->input('meta', $entry->meta ?? []));
        $meta     = $this->mergeRequestIntoMeta($r, $entry->type, $incoming);
        $data['meta'] = json_encode($meta, JSON_UNESCAPED_UNICODE);

        if ($this->schemaHasColumn('hr_daily_entries', 'updated_by')) {
            $data['updated_by'] = $r->user()?->id;
        }

        $data = Arr::except($data, $this->metaKeysForType($entry->type));

        $entry->update($data);

        if (!$r->wantsJson()) {
            return back()->with('success', 'Entry HR harian diperbarui.');
        }
        return response()->json($entry->refresh());
    }

    public function destroy(Request $r, HrDailyEntry $entry)
    {
        $entry->delete();

        if (!$r->wantsJson()) {
            return back()->with('success', 'Entry HR harian dihapus.');
        }
        return response()->json(['ok' => true]);
    }

    /* =========================================================
     |  UTILITIES
     |=========================================================*/
    public function bulk(Request $r)
    {
        $ids = collect($r->input('ids', []))->filter()->values();
        $action = $r->string('action')->toString(); // approve|reject|delete

        if ($ids->isEmpty() || !in_array($action, ['approve', 'reject', 'delete'], true)) {
            return back()->with('error', 'Tidak ada data atau aksi tidak valid.');
        }

        $entries = HrDailyEntry::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($entries as $e) {
            if ($action === 'approve' && Gate::allows('approve', $e)) {
                $e->status = 'approved';
                $e->approved_by = $r->user()->id;
                $e->approved_at = now();
                $e->save();
                $count++;
            } elseif ($action === 'reject' && Gate::allows('reject', $e)) {
                $e->status = 'rejected';
                $e->approved_by = $r->user()->id;
                $e->approved_at = now();
                $e->save();
                $count++;
            } elseif ($action === 'delete' && Gate::allows('delete', $e)) {
                $e->delete();
                $count++;
            }
        }
        return back()->with('success', "Bulk {$action}: {$count} data diproses.");
    }

    public function trashed(Request $r)
    {
        $q = HrDailyEntry::onlyTrashed()
            ->with(['user', 'site'])
            ->when($r->filled('q'), fn($qq) => $qq->where('reason', 'like', '%' . $r->q . '%'))
            ->orderByDesc('deleted_at');

        $entries = $q->paginate(25)->appends($r->query());
        return view('admin.hr_entries.trashed', compact('entries'));
    }

    public function restore($entry)
    {
        $model = HrDailyEntry::withTrashed()->where('id', $entry)->firstOrFail();
        $this->authorize('restore', $model);
        $model->restore();
        return back()->with('success', 'Entry dipulihkan.');
    }

    public function forceDelete($entry)
    {
        $model = HrDailyEntry::withTrashed()->where('id', $entry)->firstOrFail();
        $this->authorize('forceDelete', $model);
        $model->forceDelete();
        return back()->with('success', 'Entry dihapus permanen.');
    }

    public function exportCsv(Request $r): StreamedResponse
    {
        $filename = 'hr_daily_entries_' . now()->format('Ymd_His') . '.csv';
        $siteId   = $r->input('site_id', session('site_id'));

        $q = HrDailyEntry::query()
            ->with(['user', 'site', 'fromShift', 'toShift', 'approver'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($r->user_id, fn($qq, $uid) => $qq->where('user_id', $uid))
            ->when($r->type, fn($qq, $t) => $qq->where('type', $t))
            ->when($r->status, fn($qq, $s) => $qq->where('status', $s))
            ->when($r->date, fn($qq, $d) => $qq->whereDate('date', $d))
            ->when(
                $r->filled(['date_from', 'date_to']) && !$r->date,
                fn($qq) => $qq->whereBetween('date', [$r->input('date_from'), $r->input('date_to')])
            )
            ->when($r->q, fn($qq) => $qq->where(fn($w) => $w
                ->where('reason', 'like', '%' . $r->input('q') . '%')
                ->orWhere('code', 'like', '%' . $r->input('q') . '%')))
            ->orderByDesc('date')->orderBy('user_id');

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $typesMap = $this->getTypes();

        return response()->stream(function () use ($q, $typesMap) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Site', 'Karyawan', 'Tanggal', 'Jenis', 'Kode', 'Alasan', 'Status', 'Disetujui Oleh', 'Waktu Persetujuan', 'Meta']);

            $q->chunk(500, function ($rows) use ($out, $typesMap) {
                foreach ($rows as $e) {
                    fputcsv($out, [
                        $e->id,
                        $e->site?->name ?? $e->site_id,
                        $e->user?->name ?? $e->user_id,
                        optional($e->date)->format('Y-m-d'),
                        $typesMap[$e->type] ?? $e->type,
                        $e->code,
                        $e->reason,
                        $e->status,
                        $e->approver?->name ?? $e->approved_by,
                        optional($e->approved_at)->format('Y-m-d H:i'),
                        $e->meta_summary ?? (is_array($e->meta) ? json_encode($e->meta) : (string)$e->meta),
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }

    public function downloadAttachment(Request $r, HrDailyEntry $entry, string $key)
    {
        $meta = (array) ($entry->meta ?? []);
        $val  = $meta[$key] ?? null;

        if (is_string($val) && Storage::disk('public')->exists($val)) {
            return Storage::disk('public')->download($val);
        }
        if ($key === 'attachment_id' && is_string($val)) {
            return back()->with('error', 'Resolver attachment_id belum diimplementasikan.');
        }
        return back()->with('error', 'Lampiran tidak ditemukan.');
    }

    /* =========================================================
     |  OPTIONS
     |=========================================================*/
    public function typesOptions()
    {
        return response()->json($this->getTypes());
    }

    public function gaCategoriesOptions()
    {
        return response()->json([
            'vehicle_booking' => 'Booking Kendaraan',
            'travel'          => 'Perjalanan Dinas',
            'consumables'     => 'ATK/Consumables',
            'facility_repair' => 'Perbaikan Fasilitas',
            'meeting_room'    => 'Ruang Rapat',
            'other'           => 'Lainnya',
        ]);
    }
}

/* Helper untuk PHP < 8.1 */
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        $i = 0;
        foreach ($array as $k => $_) if ($k !== $i++) return false;
        return true;
    }
}
