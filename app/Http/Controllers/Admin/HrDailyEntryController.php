<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrDailyEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Shift; // kalau ada
class HrDailyEntryController extends Controller
{
    /** DEFAULT types (protected) – fallback bila belum ada konfigurasi */
    private const DEFAULT_TYPES = [
        'leave'        => 'Leave',
        'permit'       => 'Permit',
        'sick'         => 'Sick',
        'shift_change' => 'Shift Change',
        'ga_request'   => 'GA Request',
        'mcu'          => 'MCU',
    ];

    /** Konfigurasi key di site_configs */
    private const TYPES_CONFIG_KEY             = 'hr.entry_types';
    private const META_SCHEMAS_CONFIG_KEY      = 'hr.entry_meta_schemas';
    private const META_FORM_CONFIG_KEY         = 'hr.entry_meta_form_config';
    private const APPROVAL_SCHEMAS_CONFIG_KEY  = 'hr.entry_approval_schemas';
    private const PRINT_TEMPLATES_CONFIG_KEY   = 'hr.entry_print_templates';

    /* =========================================================
     |  UTIL: lokasi/site aktif & schema_has_column safe
     |=========================================================*/
    private function activeSiteId(Request $r): ?string
    {
        return $r->input('site_id')
            ?? session('site_id')
            ?? optional($r->user())->default_site_id
            ?? null;
    }

    private function schemaHasColumn(string $table, string $col): bool
    {
        try {
            return Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* =========================================================
     |  UTIL: load/save config JSON dari site_configs
     |=========================================================*/
    private function loadConfigJson(string $key): mixed
    {
        try {
            $row = DB::table('site_configs')->where('key', $key)->first(['value']);
            if (!$row) return null;
            $val = $row->value;
            $decoded = is_string($val) ? json_decode($val, true) : $val;
            return $decoded ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function saveConfigJson(string $key, mixed $value): void
    {
        $now  = now();
        $json = json_encode($value, JSON_UNESCAPED_UNICODE);
        try {
            $exists = DB::table('site_configs')->where('key', $key)->exists();
            if ($exists) {
                DB::table('site_configs')->where('key', $key)
                    ->update(['value' => $json, 'updated_at' => $now]);
            } else {
                DB::table('site_configs')->insert([
                    'id'         => (string) Str::uuid(),
                    'key'        => $key,
                    'value'      => $json,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } catch (\Throwable $e) {
            // optional: log
        }
    }

    /* =========================================================
     |  TYPES (dinamis): get/save + CRUD + reorder
     |=========================================================*/
    private function validateTypeKey(string $key): string
    {
        $k = Str::of($key)->lower()->snake()->toString();
        if (!preg_match('/^[a-z0-9_]{2,30}$/', $k)) {
            abort(422, 'Key harus huruf/angka/underscore (2–30 chars).');
        }
        return $k;
    }

    private function getTypes(): array
    {
        $db = $this->loadConfigJson(self::TYPES_CONFIG_KEY);
        if (is_array($db) && !empty($db)) {
            $norm = [];
            foreach ($db as $k => $v) {
                $key = Str::of($k)->lower()->snake()->toString();
                $label = is_string($v)
                    ? $v
                    : (is_array($v) && isset($v['label']) ? (string)$v['label'] : Str::headline($key));
                $norm[$key] = $label;
            }
            return $norm;
        }
        return self::DEFAULT_TYPES;
    }

    private function saveTypes(array $types): void
    {
        $payload = [];
        foreach ($types as $k => $label) {
            $payload[$this->validateTypeKey($k)] = (string)$label;
        }
        $this->saveConfigJson(self::TYPES_CONFIG_KEY, $payload);
    }

    private function protectedTypeKeys(): array
    {
        return array_keys(self::DEFAULT_TYPES);
    }

    public function typesIndex()
    {
        return response()->json([
            'data'      => $this->getTypes(),
            'protected' => $this->protectedTypeKeys(),
        ]);
    }

    public function typesStore(Request $r)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $data = $r->validate([
            'key'   => ['required', 'string', 'max:30'],
            'label' => ['required', 'string', 'max:60'],
        ]);

        $key   = $this->validateTypeKey($data['key']);
        $label = trim($data['label']);

        $types = $this->getTypes();
        if (isset($types[$key])) {
            return response()->json(['message' => 'Key sudah ada.'], 422);
        }

        $types[$key] = $label;
        $this->saveTypes($types);

        return response()->json(['ok' => true, 'data' => $types]);
    }

    public function typesUpdate(Request $r, string $key)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $key = $this->validateTypeKey($key);
        $data = $r->validate([
            'label'   => ['nullable', 'string', 'max:60'],
            'new_key' => ['nullable', 'string', 'max:30'],
        ]);

        $types = $this->getTypes();
        if (!isset($types[$key])) {
            return response()->json(['message' => 'Type tidak ditemukan.'], 404);
        }

        if (isset($data['label'])) {
            $types[$key] = trim($data['label']);
        }

        if (!empty($data['new_key'])) {
            $newKey = $this->validateTypeKey($data['new_key']);
            if ($newKey !== $key) {
                if (isset($types[$newKey])) {
                    return response()->json(['message' => 'new_key sudah dipakai.'], 422);
                }
                // rename referensi pada data
                DB::table('hr_daily_entries')->where('type', $key)->update(['type' => $newKey]);
                $label = $types[$key];
                unset($types[$key]);
                $types[$newKey] = $label;
                $key = $newKey;
            }
        }

        $this->saveTypes($types);
        return response()->json(['ok' => true, 'data' => $types, 'key' => $key]);
    }

    public function typesDestroy(string $key)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $key   = $this->validateTypeKey($key);
        $types = $this->getTypes();

        if (!isset($types[$key])) {
            return response()->json(['message' => 'Type tidak ditemukan.'], 404);
        }
        if (in_array($key, $this->protectedTypeKeys(), true)) {
            return response()->json(['message' => 'Type default tidak boleh dihapus.'], 403);
        }
        $inUse = HrDailyEntry::where('type', $key)->exists();
        if ($inUse) {
            return response()->json(['message' => 'Type sedang dipakai oleh data entry.'], 409);
        }
        unset($types[$key]);
        $this->saveTypes($types);
        return response()->json(['ok' => true, 'data' => $types]);
    }

    public function typesReorder(Request $r)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $data = $r->validate([
            'order'   => ['required', 'array', 'min:1'],
            'order.*' => ['string'],
        ]);

        $current = $this->getTypes();
        $order   = array_map(fn($k) => $this->validateTypeKey($k), $data['order']);

        $new = [];
        foreach ($order as $k) {
            if (isset($current[$k])) $new[$k] = $current[$k];
        }
        foreach ($current as $k => $lbl) {
            if (!isset($new[$k])) $new[$k] = $lbl;
        }

        $this->saveTypes($new);
        return response()->json(['ok' => true, 'data' => $new]);
    }

    /* =========================================================
     |  META SCHEMA (dinamis) – rules & form config
     |=========================================================*/

    /** Meta rules bawaan (fallback) jika tidak ada schema khusus */
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

            case 'ga_request':
                $base += [
                    'meta.category'      => ['required', 'string', 'in:vehicle_booking,travel,consumables,facility_repair,meeting_room,other'],
                    'meta.priority'      => ['required', 'string', 'in:low,normal,high,urgent'],
                    'meta.needed_date'   => ['required', 'date'],
                    'meta.needed_time'   => ['nullable', 'date_format:H:i'],
                    'meta.location'      => ['nullable', 'string', 'max:120'],
                    'meta.item_name'     => ['nullable', 'string', 'max:120'],
                    'meta.quantity'      => ['nullable', 'numeric', 'min:0'],
                    'meta.unit'          => ['nullable', 'string', 'max:30'],
                    'meta.budget_code'   => ['nullable', 'string', 'max:30'],
                    'meta.attachment_id' => ['nullable', 'uuid'],
                    'meta.notes'         => ['nullable', 'string'],
                ];
                break;

            case 'mcu':
                $base += [
                    'meta.package'          => ['required', 'string', 'max:100'],
                    'meta.provider'         => ['required', 'string', 'max:120'],
                    'meta.schedule_date'    => ['required', 'date'],
                    'meta.schedule_time'    => ['nullable', 'date_format:H:i'],
                    'meta.fasting_required' => ['nullable', 'boolean'],
                    'meta.result_status'    => ['nullable', 'string', 'in:pending,normal,attention,fit,unfit'],
                    'meta.result_file_id'   => ['nullable', 'uuid'],
                    'meta.result_file_path' => ['nullable', 'string', 'max:190'],
                    'meta.notes'            => ['nullable', 'string'],
                ];
                break;

            default:
                $base += ['meta.notes' => ['nullable', 'string']];
        }
        return $base;
    }

    /** Ambil schema rules kustom per type (bila diset via API) */
    private function getMetaSchemas(): array
    {
        $cfg = $this->loadConfigJson(self::META_SCHEMAS_CONFIG_KEY);
        return is_array($cfg) ? $cfg : [];
    }

    private function saveMetaSchemas(array $map): void
    {
        $this->saveConfigJson(self::META_SCHEMAS_CONFIG_KEY, $map);
    }

    /** Ambil form config (UI) untuk meta per type */
    private function getMetaFormConfig(): array
    {
        $cfg = $this->loadConfigJson(self::META_FORM_CONFIG_KEY);
        return is_array($cfg) ? $cfg : [];
    }

    private function saveMetaFormConfig(array $map): void
    {
        $this->saveConfigJson(self::META_FORM_CONFIG_KEY, $map);
    }

    /** Merge rules: builtin + custom schema (custom menimpa builtin) */
    private function metaRules(?string $type): array
    {
        $builtin = $this->metaRulesBuiltin($type);

        $schemas = $this->getMetaSchemas();
        $custom  = $schemas[$type] ?? null; // format sama seperti rules Laravel (array)

        if (is_array($custom)) {
            foreach ($custom as $k => $rule) {
                $builtin[$k] = $rule;
            }
        }
        return $builtin;
    }

    /** ====== Meta Schema API ====== */
    public function metaSchemasIndex()
    {
        return response()->json(['data' => $this->getMetaSchemas()]);
    }

    private function guessMetaComponentFromRule($rule): string
    {
        $str = is_array($rule) ? implode('|', $rule) : (string)$rule;
        $str = strtolower($str);
        if (str_contains($str, 'boolean')) return 'checkbox';
        if (str_contains($str, 'date_format:h:i')) return 'time';
        if (str_contains($str, 'date')) return 'date';
        if (str_contains($str, 'numeric') || str_contains($str, 'integer')) return 'number';
        if (preg_match('/\bin:([^|]+)/', $str)) return 'select';
        return 'text';
    }

    public function metaSchemasShow(string $type)
    {
        $type  = $this->validateTypeKey($type);
        $all   = $this->getMetaSchemas();
        $rules = $all[$type] ?? [];

        // Auto-derive struktur meta dari rules "meta.*" untuk fallback render
        $meta = [];
        foreach ($rules as $key => $rule) {
            if (str_starts_with($key, 'meta.')) {
                $field     = substr($key, 5);
                $component = $this->guessMetaComponentFromRule($rule);
                $required  = is_array($rule)
                    ? in_array('required', $rule)
                    : str_contains(strtolower((string)$rule), 'required');
                $meta[$field] = [
                    'label'     => Str::headline($field),
                    'component' => $component,
                    'required'  => $required,
                ];
            }
        }

        return response()->json([
            'data' => $rules, // untuk halaman manage
            'meta' => $meta,  // untuk _form.blade.php fallback
        ]);
    }

    public function metaSchemasUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $data = $r->input('rules', []);
        if (!is_array($data)) {
            return response()->json(['message' => 'rules harus array.'], 422);
        }

        $all = $this->getMetaSchemas();
        $all[$type] = $data;
        $this->saveMetaSchemas($all);

        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function metaSchemasDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $all  = $this->getMetaSchemas();
        if (!isset($all[$type])) {
            return response()->json(['message' => 'Schema tidak ditemukan.'], 404);
        }
        unset($all[$type]);
        $this->saveMetaSchemas($all);
        return response()->json(['ok' => true]);
    }

    /** ====== Meta Form Config API ====== */
    public function metaFormConfigIndex()
    {
        return response()->json(['data' => $this->getMetaFormConfig()]);
    }

    public function metaFormConfigShow(string $type)
    {
        $type   = $this->validateTypeKey($type);
        $all    = $this->getMetaFormConfig();
        $cfg    = $all[$type] ?? [];
        $fields = (is_array($cfg) && array_is_list($cfg)) ? $cfg : ($cfg['fields'] ?? []);

        return response()->json([
            'data'   => $cfg,     // untuk halaman manage
            'fields' => $fields,  // untuk _form.blade.php dinamis
        ]);
    }

    public function metaFormConfigUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $cfg  = $r->input('config', []);
        if (!is_array($cfg)) {
            return response()->json(['message' => 'config harus array.'], 422);
        }
        $all = $this->getMetaFormConfig();
        $all[$type] = $cfg;
        $this->saveMetaFormConfig($all);

        return response()->json(['ok' => true, 'data' => $cfg]);
    }

    public function metaFormConfigDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $all  = $this->getMetaFormConfig();
        if (!isset($all[$type])) {
            return response()->json(['message' => 'Form config tidak ditemukan.'], 404);
        }
        unset($all[$type]);
        $this->saveMetaFormConfig($all);
        return response()->json(['ok' => true]);
    }

    /* =========================================================
     |  APPROVAL FLOW (dinamis & legacy)
     |=========================================================*/

    private function getApprovalSchemas(): array
    {
        $cfg = $this->loadConfigJson(self::APPROVAL_SCHEMAS_CONFIG_KEY);
        return is_array($cfg) ? $cfg : [];
    }

    private function saveApprovalSchemas(array $map): void
    {
        $this->saveConfigJson(self::APPROVAL_SCHEMAS_CONFIG_KEY, $map);
    }

    public function approvalSchemasIndex()
    {
        return response()->json(['data' => $this->getApprovalSchemas()]);
    }

    public function approvalSchemasShow(string $type)
    {
        $type = $this->validateTypeKey($type);
        $all  = $this->getApprovalSchemas();
        return response()->json(['data' => $all[$type] ?? ['stages' => []]]);
    }

    public function approvalSchemasUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $data = $r->validate([
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.key'              => ['required', 'string', 'max:40'],
            'stages.*.label'            => ['required', 'string', 'max:80'],
            'stages.*.roles'            => ['required', 'array', 'min:1'],
            'stages.*.roles.*'          => ['string', 'max:40'],
            'stages.*.all_must_approve' => ['nullable', 'boolean'],
        ]);

        $all = $this->getApprovalSchemas();
        $all[$type] = $data;
        $this->saveApprovalSchemas($all);

        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function approvalSchemasDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $all  = $this->getApprovalSchemas();
        if (!isset($all[$type])) {
            return response()->json(['message' => 'Approval schema tidak ditemukan.'], 404);
        }
        unset($all[$type]);
        $this->saveApprovalSchemas($all);
        return response()->json(['ok' => true]);
    }

    /** Submit → status pending + reset trail */
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

    /** Approve (simple) */
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

    /** Reject (simple) */
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

    /** Legacy single-step approve */
    public function approve(Request $r, HrDailyEntry $entry)
    {
        $entry->status      = 'approved';
        $entry->approved_by = $r->user()?->id;
        $entry->approved_at = now();
        if ($this->schemaHasColumn('hr_daily_entries', 'updated_by')) {
            $entry->updated_by = $r->user()?->id;
        }
        $meta  = (array) ($entry->meta ?? []);
        $trail = (array) ($meta['_approval'] ?? []);
        $trail['stages'] = array_values((array)($trail['stages'] ?? []));
        $trail['stages'][] = [
            'action' => 'approved',
            'at'     => now()->toDateTimeString(),
            'by'     => $r->user()?->id,
            'note'   => (string)$r->input('note', ''),
            'legacy' => true,
        ];
        $meta['_approval'] = $trail;
        $entry->meta = $meta;
        $entry->save();

        return $r->wantsJson()
            ? response()->json($entry->refresh())
            : back()->with('success', 'Entry disetujui.');
    }

    /** Legacy single-step reject */
    public function reject(Request $r, HrDailyEntry $entry)
    {
        $entry->status      = 'rejected';
        $entry->approved_by = $r->user()?->id;
        $entry->approved_at = now();
        if ($this->schemaHasColumn('hr_daily_entries', 'updated_by')) {
            $entry->updated_by = $r->user()?->id;
        }
        $meta  = (array) ($entry->meta ?? []);
        $trail = (array) ($meta['_approval'] ?? []);
        $trail['stages'] = array_values((array)($trail['stages'] ?? []));
        $trail['stages'][] = [
            'action' => 'rejected',
            'at'     => now()->toDateTimeString(),
            'by'     => $r->user()?->id,
            'note'   => (string)$r->input('note', ''),
            'legacy' => true,
        ];
        $meta['_approval'] = $trail;
        $entry->meta = $meta;
        $entry->save();

        return $r->wantsJson()
            ? response()->json($entry->refresh())
            : back()->with('success', 'Entry ditolak.');
    }

    /** Reset approval (kembali draft) */
    public function approvalReset(Request $r, HrDailyEntry $entry)
    {
        Gate::authorize('resetApproval', $entry);

        $meta = (array)($entry->meta ?? []);
        unset($meta['_approval']);

        $entry->update([
            'status'      => 'draft',
            'approved_by' => null,
            'approved_at' => null,
            'meta'        => $meta,
            'updated_by'  => $this->schemaHasColumn('hr_daily_entries', 'updated_by') ? $r->user()?->id : null,
        ]);

        return back()->with('success', 'Approval direset ke draft.');
    }

    /* =========================================================
     |  PRINT TEMPLATES (dinamis per type)
     |=========================================================*/
    private function getPrintTemplates(): array
    {
        $cfg = $this->loadConfigJson(self::PRINT_TEMPLATES_CONFIG_KEY);
        return is_array($cfg) ? $cfg : [];
    }

    private function savePrintTemplates(array $map): void
    {
        $norm = [];
        foreach ($map as $type => $def) {
            $tk = $this->validateTypeKey($type);
            $norm[$tk] = [
                'view'        => (string)($def['view']        ?? ''),
                'paper'       => (string)($def['paper']       ?? ''),
                'orientation' => (string)($def['orientation'] ?? ''),
                'columns'     => is_array($def['columns'] ?? null) ? array_values($def['columns']) : [],
                'header'      => (string)($def['header']      ?? ''),
                'footer'      => (string)($def['footer']      ?? ''),
            ];
        }
        $this->saveConfigJson(self::PRINT_TEMPLATES_CONFIG_KEY, $norm);
    }

    public function printTemplatesIndex()
    {
        return response()->json(['data' => $this->getPrintTemplates()]);
    }

    public function printTemplatesShow(string $type)
    {
        $type = $this->validateTypeKey($type);
        $all  = $this->getPrintTemplates();
        return response()->json(['data' => $all[$type] ?? ['view' => '', 'paper' => '', 'orientation' => '', 'columns' => [], 'header' => '', 'footer' => '']]);
    }

    public function printTemplatesUpsert(Request $r, string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $data = $r->validate([
            'view'        => ['nullable', 'string', 'max:190'],
            'paper'       => ['nullable', 'string', 'max:30'],
            'orientation' => ['nullable', 'string', 'in:portrait,landscape'],
            'header'      => ['nullable', 'string', 'max:190'],
            'footer'      => ['nullable', 'string', 'max:190'],
            'columns'     => ['nullable', 'array'],
            'columns.*.key'   => ['required_with:columns', 'string', 'max:190'],
            'columns.*.label' => ['required_with:columns', 'string', 'max:190'],
        ]);

        $all = $this->getPrintTemplates();
        $def = $all[$type] ?? [];

        $def['view']        = $data['view']        ?? ($def['view']        ?? '');
        $def['paper']       = $data['paper']       ?? ($def['paper']       ?? '');
        $def['orientation'] = $data['orientation'] ?? ($def['orientation'] ?? '');
        $def['header']      = $data['header']      ?? ($def['header']      ?? '');
        $def['footer']      = $data['footer']      ?? ($def['footer']      ?? '');
        if (isset($data['columns'])) {
            $def['columns'] = array_values($data['columns']);
        }

        $all[$type] = $def;
        $this->savePrintTemplates($all);

        return response()->json(['ok' => true, 'data' => $def]);
    }

    public function printTemplatesDestroy(string $type)
    {
        Gate::authorize('manage', HrDailyEntry::class);

        $type = $this->validateTypeKey($type);
        $all  = $this->getPrintTemplates();
        if (!isset($all[$type])) {
            return response()->json(['message' => 'Print template tidak ditemukan.'], 404);
        }
        unset($all[$type]);
        $this->savePrintTemplates($all);
        return response()->json(['ok' => true]);
    }

    public function print(Request $r)
    {
        $type      = $r->string('type')->toString();
        $templates = $this->getPrintTemplates();

        $q = HrDailyEntry::query()
            ->with(['user', 'site', 'approver'])
            ->when($type, fn($qq, $t) => $qq->where('type', $t))
            ->when($r->filled('status'), fn($qq, $st) => $qq->where('status', $st))
            ->when(
                $r->filled(['date_from', 'date_to']),
                fn($qq) => $qq->whereBetween('date', [request('date_from'), request('date_to')])
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
            ->when($r->site_id ?? session('site_id'), fn($qq, $sid) => $qq->where('site_id', $sid))
            ->when($r->user_id, fn($qq, $uid) => $qq->where('user_id', $uid))
            ->when($r->type, fn($qq, $t) => $qq->where('type', $t))
            ->when($r->status, fn($qq, $s) => $qq->where('status', $s))
            ->when($r->date, fn($qq, $d) => $qq->whereDate('date', $d))
            ->when(
                $r->filled(['date_from', 'date_to']) && !$r->date,
                fn($qq) => $qq->whereBetween('date', [request('date_from'), request('date_to')])
            )
            ->when($r->q, function ($qq) use ($r) {
                $s = trim($r->q);
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

        // Jenis entry (statis)
        $types = [
            'leave'        => 'Cuti',
            'permit'       => 'Izin',
            'sick'         => 'Sakit',
            'shift_change' => 'Pergantian Shift',
        ];

        // ✅ Tanpa pivot: pakai scopeInSite (filter default_site_id)
        //    Scope ini juga otomatis mendukung pivot site_user jika nanti ada.
        $users = User::select('id', 'name', 'email', 'employee_code')
            ->orderBy('name')
            ->limit(500)
            ->get();

        $shifts = Shift::query()
            ->when($activeSiteId, fn($q) => $q->where('site_id', $activeSiteId))
            ->orderBy('code')
            ->get();

        return view('admin.hr_entries.create', compact('types', 'activeSiteId', 'users', 'shifts'));
    }

    /** (Opsional) Endpoint AJAX pencarian user – aman tanpa pivot */
    public function searchUsers(Request $r)
    {
        $q   = trim((string) $r->input('q', ''));
        $sid = $r->input('site_id', session('site_id'));
        $like = '%'.str_replace(['%','_'], ['\%','\_'], $q).'%';

        $users = User::select('id','name','email','employee_code')
            ->when($q !== '', function ($qb) use ($like) {
                $qb->where(function ($qq) use ($like) {
                    $qq->where('name', 'like', $like)
                       ->orWhere('email', 'like', $like)
                       ->orWhere('employee_code', 'like', $like);
                });
            })
            ->when($sid, fn($qb) => $qb->inSite($sid)) // tidak menyentuh pivot kalau belum ada
            ->orderBy('name')
            ->limit(20)
            ->get();

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

        $payload = $data;
        $payload['id']      = (string) Str::uuid();
        $payload['site_id'] = $siteId;

        if ($this->schemaHasColumn('hr_daily_entries', 'created_by')) {
            $payload['created_by'] = $r->user()?->id;
        }

        DB::transaction(function () use ($payload) {
            HrDailyEntry::updateOrCreate(
                [
                    'site_id' => $payload['site_id'],
                    'user_id' => $payload['user_id'],
                    'date'    => $payload['date'],
                    'type'    => $payload['type'],
                    'code'    => $payload['code'] ?? null,
                ],
                collect($payload)->except(['id', 'site_id', 'user_id', 'date', 'type', 'code'])->toArray()
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

        if ($this->schemaHasColumn('hr_daily_entries', 'updated_by')) {
            $data['updated_by'] = $r->user()?->id;
        }

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
        $siteId   = $r->site_id ?? session('site_id');

        $q = HrDailyEntry::query()
            ->with(['user', 'site', 'fromShift', 'toShift', 'approver'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($r->user_id, fn($qq, $uid) => $qq->where('user_id', $uid))
            ->when($r->type, fn($qq, $t) => $qq->where('type', $t))
            ->when($r->status, fn($qq, $s) => $qq->where('status', $s))
            ->when($r->date, fn($qq, $d) => $qq->whereDate('date', $d))
            ->when(
                $r->filled(['date_from', 'date_to']) && !$r->date,
                fn($qq) => $qq->whereBetween('date', [request('date_from'), request('date_to')])
            )
            ->when($r->q, fn($qq) => $qq->where(fn($w) => $w->where('reason', 'like', '%' . request('q') . '%')->orWhere('code', 'like', '%' . request('q') . '%')))
            ->orderByDesc('date')->orderBy('user_id');

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $typesMap = $this->getTypes();

        return response()->stream(function () use ($q, $typesMap) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Site', 'User', 'Date', 'Type', 'Code', 'Reason', 'Status', 'Approved By', 'Approved At', 'Meta']);

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
     |  OPTIONS & LOOKUPS
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
            'meeting_room'    => 'Rapat/Meeting Room',
            'other'           => 'Lainnya',
        ]);
    }

}

/* =========================================================
 |  Helpers (global) untuk PHP < 8.1
 |=========================================================*/
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        $i = 0;
        foreach ($array as $k => $_) {
            if ($k !== $i++) return false;
        }
        return true;
    }
}
