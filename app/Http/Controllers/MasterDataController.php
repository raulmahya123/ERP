<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule as UniqueRule; // alias agar jelas
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Support\SiteContext;

class MasterDataController extends Controller
{
    protected array $entities = [
        'units',
        'pits',
        'stockpiles',
        'cost_centers',
        'accounts',
        'employees',
        'asset_categories',
    ];

    /* =========================
     | Helpers (DRY)
     |=========================*/
    protected function normalizeEntityKey(string $key): string
    {
        return Str::slug($key, '_');
    }

    protected function getEntityRow(string $entity): ?object
    {
        return DB::table('master_entities')
            ->where('key', $entity)
            ->where('enabled', 1)
            ->first();
    }

    protected function ensureEntity(string $entity): string
    {
        $key = $this->normalizeEntityKey($entity);
        $exists = DB::table('master_entities')
            ->where('key', $key)
            ->where('enabled', 1)
            ->exists();

        abort_unless($exists, 404, 'Unknown entity.');
        return $key;
    }

    protected function currentSiteId(?\App\Models\User $user): ?string
    {
        return SiteContext::currentSiteId($user);
    }

    /**
     * Pastikan site_id valid; kalau tidak valid -> NULL.
     */
    protected function resolveValidSiteId(?string $sid): ?string
    {
        if (!$sid) return null;
        return DB::table('sites')->where('id', $sid)->value('id') ?: null;
    }

    /**
     * Terapkan scope site (site_id = $sid ATAU NULL) untuk SELECT.
     */
    protected function applySiteScope($query, ?string $sid)
    {
        if (\Schema::hasColumn('master_records', 'site_id')) {
            $validSid = $this->resolveValidSiteId($sid);
            if ($validSid) {
                $query->where(function ($w) use ($validSid) {
                    $w->where('site_id', $validSid)->orWhereNull('site_id');
                });
            }
            // jika $sid tidak valid -> tidak filter (tampilkan semua, termasuk global)
        }
        return $query;
    }

    /**
     * Rule unik code per (entity, site_id). Saat update, set $ignoreId.
     * Mengembalikan instance rule kompatibel (tanpa return type ketat).
     *
     * @return \Illuminate\Contracts\Validation\Rule|\Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\Unique
     */
    protected function uniqueCodeRule(string $entity, ?string $sid, ?string $ignoreId = null)
    {
        $rule = UniqueRule::unique('master_records', 'code')
            ->where(function ($q) use ($entity, $sid) {
                $q->where('entity', $entity);
                if (\Schema::hasColumn('master_records', 'site_id')) {
                    $validSid = $this->resolveValidSiteId($sid);
                    if ($validSid !== null) {
                        $q->where('site_id', $validSid);
                    } else {
                        $q->whereNull('site_id');
                    }
                }
            });

        return $ignoreId ? $rule->ignore($ignoreId, 'id') : $rule;
    }

    protected function normalizeExtra($extra): ?string
    {
        if (is_array($extra)) {
            return json_encode($extra, JSON_UNESCAPED_UNICODE);
        }
        if (is_string($extra)) {
            $trim = trim($extra);
            if ($trim === '') return null;
            try {
                $decoded = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                return json_encode($trim, JSON_UNESCAPED_UNICODE);
            }
        }
        return null;
    }

    /* =========================
     | Overview
     |=========================*/
    public function overview()
    {
        $entities = DB::table('master_entities')
            ->where('enabled', 1)
            ->orderBy('sort')
            ->orderBy('label')
            ->get(['id', 'key', 'label', 'icon', 'color_from', 'color_to']);

        $allowedEntities = $entities->pluck('key')->all();
        $labels          = $entities->mapWithKeys(fn($e) => [$e->key => $e->label])->all();

        $currentSiteId = session('site_id');

        // Hitung total per entity_id, scope: site_id = current OR NULL
        $q = DB::table('master_records')
            ->select('master_entity_id', DB::raw('COUNT(*) as total'))
            ->whereIn('master_entity_id', $entities->pluck('id'));

        if ($currentSiteId && \Schema::hasColumn('master_records', 'site_id')) {
            $validSid = $this->resolveValidSiteId($currentSiteId);
            if ($validSid) {
                $q->where(function ($w) use ($validSid) {
                    $w->where('site_id', $validSid)->orWhereNull('site_id');
                });
            }
        }

        $countsById = $q->groupBy('master_entity_id')->pluck('total', 'master_entity_id');

        $masterTotals = [];
        foreach ($entities as $e) {
            $masterTotals[$e->key] = (int) ($countsById[$e->id] ?? 0);
        }

        $meta = $entities->mapWithKeys(function ($e) {
            return [$e->key => [
                'icon'       => $e->icon,
                'color_from' => $e->color_from,
                'color_to'   => $e->color_to,
            ]];
        })->all();

        return view('admin.master.overview', [
            'allowedEntities' => $allowedEntities,
            'labels'          => $labels,
            'masterTotals'    => $masterTotals,
            'currentSiteId'   => $currentSiteId,
            'meta'            => $meta,
        ]);
    }

    protected function makeUniqueCode(string $entity, ?string $baseCode): ?string
    {
        if (!$baseCode) return null;

        $suffix = '-COPY';
        $candidate = $baseCode . $suffix;

        $exists = fn($code) => DB::table('master_records')
            ->where('entity', $entity)
            ->where('code', $code)
            ->exists();

        if (!$exists($candidate)) return $candidate;

        for ($i = 2; $i <= 50; $i++) {
            $candidate = $baseCode . $suffix . $i;
            if (!$exists($candidate)) return $candidate;
        }
        return $baseCode . $suffix . '-' . substr((string) Str::uuid(), 0, 8);
    }

    /* =========================
     | CRUD
     |=========================*/
    public function index(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);
        $sid    = $this->currentSiteId($r->user());

        $q = DB::table('master_records')->where('entity', $entity);
        $this->applySiteScope($q, $sid);

        if ($search = trim((string) $r->get('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $records = $q->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.master.index', [
            'entity'  => $entity,
            'records' => $records,
            'search'  => $r->get('q', ''),
        ]);
    }

    public function create(string $entity)
    {
        $entity = $this->ensureEntity($entity);

        return view('admin.master.create', [
            'entity' => $entity,
        ]);
    }

    public function store(Request $r, string $entity)
    {
        $entity    = $this->ensureEntity($entity);
        $entityRow = $this->getEntityRow($entity);
        if (!$entityRow) abort(404, 'Unknown entity.');

        $rawSid = $this->currentSiteId($r->user());
        $sid    = $this->resolveValidSiteId($rawSid); // penting: valid/NULL

        $data = $r->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:255', $this->uniqueCodeRule($entity, $sid)],
            'description' => ['nullable', 'string'],
            'extra'       => ['nullable'],
        ]);

        DB::table('master_records')->insert([
            'id'               => (string) Str::uuid(),
            'entity'           => $entity,
            'master_entity_id' => $entityRow->id,
            'site_id'          => (\Schema::hasColumn('master_records', 'site_id') ? $sid : null),
            'name'             => $data['name'],
            'code'             => $data['code'] ?? null,
            'description'      => $data['description'] ?? null,
            'extra'            => $this->normalizeExtra($data['extra'] ?? null),
            'created_by'       => optional($r->user())->id,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('admin.master.index', $entity)->with('status', 'Record created.');
    }

    public function show(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $row = DB::table('master_records')->where('id', $record)->first();
        if (!$row || (string) $row->entity !== (string) $entity) abort(404);

        $extraArray = null;
        if (!empty($row->extra)) {
            try { $extraArray = json_decode($row->extra, true, 512, JSON_THROW_ON_ERROR); }
            catch (\Throwable $e) { $extraArray = null; }
        }

        return view('admin.master.show', [
            'entity'     => $entity,
            'record'     => $row,
            'extraArray' => $extraArray,
        ]);
    }

    public function edit(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $row = DB::table('master_records')->where('id', $record)->first();
        if (!$row || (string) $row->entity !== (string) $entity) abort(404);

        $extraArray = null;
        if (!empty($row->extra)) {
            try { $extraArray = json_decode($row->extra, true, 512, JSON_THROW_ON_ERROR); }
            catch (\Throwable $e) { $extraArray = null; }
        }

        return view('admin.master.edit', [
            'entity'     => $entity,
            'record'     => $row,
            'extraArray' => $extraArray,
        ]);
    }

    public function update(Request $r, string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);
        $row    = DB::table('master_records')->where('id', $record)->first();
        if (!$row || (string) $row->entity !== (string) $entity) abort(404);

        // site_id tetap, tapi rule unik harus per site milik record tsb (validasi NULL-aware)
        $sid = (\Schema::hasColumn('master_records', 'site_id') ? ($row->site_id ?? null) : null);

        $data = $r->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:255', $this->uniqueCodeRule($entity, $sid, $record)],
            'description' => ['nullable', 'string'],
            'extra'       => ['nullable'],
        ]);

        DB::table('master_records')->where('id', $record)->update([
            'name'        => $data['name'],
            'code'        => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'extra'       => $this->normalizeExtra($data['extra'] ?? null),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.index', $entity)->with('status', 'Record updated.');
    }

    public function destroy(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $row = DB::table('master_records')->where('id', $record)->first();
        if (!$row || (string) $row->entity !== (string) $entity) abort(404);

        DB::transaction(function () use ($record) {
            DB::table('master_record_permissions')->where('master_record_id', $record)->delete();
            DB::table('master_records')->where('id', $record)->delete();
        });

        return redirect()->route('admin.master.index', ['entity' => $entity])
            ->with('status', 'Record deleted.');
    }

    /* =========================
     | Permissions
     |=========================*/
    public function permissions(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $rec = DB::table('master_records')->where('entity', $entity)->where('id', $record)->first();
        if (!$rec) abort(404);

        $users = DB::table('users')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->select('users.id', 'users.name', 'users.email', 'roles.key as role_key', 'roles.name as role_name')
            ->orderBy('users.name')
            ->get();

        $perms = DB::table('master_record_permissions')
            ->where('master_record_id', $record)
            ->get()
            ->keyBy('user_id');

        return view('admin.master.permissions', [
            'entity' => $entity,
            'record' => $rec,
            'users'  => $users,
            'perms'  => $perms,
        ]);
    }

    public function permissionsUpdate(Request $r, string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $exists = DB::table('master_records')->where('entity', $entity)->where('id', $record)->exists();
        if (!$exists) abort(404);

        $data = $r->validate([
            'permissions'                => ['nullable', 'array'],
            'permissions.*.user_id'      => ['required', 'string'],
            'permissions.*.can_view'     => ['nullable', 'boolean'],
            'permissions.*.can_download' => ['nullable', 'boolean'],
            'permissions.*.can_update'   => ['nullable', 'boolean'],
            'permissions.*.can_delete'   => ['nullable', 'boolean'],
        ]);

        $rows = $data['permissions'] ?? [];

        DB::transaction(function () use ($record, $rows) {
            DB::table('master_record_permissions')->where('master_record_id', $record)->delete();

            if (empty($rows)) return;

            $now = now();
            $payload = [];
            foreach ($rows as $row) {
                $payload[] = [
                    'id'               => (string) Str::uuid(),
                    'master_record_id' => $record,
                    'user_id'          => $row['user_id'],
                    'can_view'         => (bool) ($row['can_view']     ?? false),
                    'can_download'     => (bool) ($row['can_download'] ?? false),
                    'can_update'       => (bool) ($row['can_update']   ?? false),
                    'can_delete'       => (bool) ($row['can_delete']   ?? false),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
            foreach (array_chunk($payload, 1000) as $chunk) {
                DB::table('master_record_permissions')->insert($chunk);
            }
        });

        return redirect()->route('admin.master.permissions', ['entity' => $entity, 'record' => $record])
            ->with('status', 'Permissions updated.');
    }

    /* =========================
     | Utilities
     |=========================*/
    public function lookup(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);
        $sid    = $this->currentSiteId($r->user());

        $q      = trim((string) $r->get('q', ''));
        $limit  = max(1, min(100, (int) $r->get('limit', 10)));
        $page   = max(1, (int) $r->get('page', 1));

        $base = DB::table('master_records')->where('entity', $entity);
        $this->applySiteScope($base, $sid);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $total = (clone $base)->count();
        $items = $base->orderBy('name')->forPage($page, $limit)->get(['id', 'name', 'code']);

        return response()->json([
            'items' => $items,
            'pagination' => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'more'  => ($page * $limit) < $total,
            ],
        ]);
    }

    public function export(Request $r, string $entity): StreamedResponse
    {
        $entity = $this->ensureEntity($entity);
        $sid    = $this->currentSiteId($r->user());

        $search = trim((string) $r->get('q', ''));
        $filename = $entity . '_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($entity, $search, $sid) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'entity', 'name', 'code', 'description', 'extra', 'created_by', 'site_id', 'created_at', 'updated_at']);

            $q = DB::table('master_records')->where('entity', $entity);
            $this->applySiteScope($q, $sid);

            if ($search !== '') {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $q->orderBy('name')->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->id,
                        $row->entity,
                        $row->name,
                        $row->code,
                        $row->description,
                        $row->extra,
                        $row->created_by,
                        $row->site_id ?? '',
                        $row->created_at,
                        $row->updated_at,
                    ]);
                }
            });

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importTemplate(string $entity): StreamedResponse
    {
        $entity = $this->ensureEntity($entity);
        $filename = $entity . '_template.csv';

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'code', 'description', 'extra']); // header
            fputcsv($out, ['Contoh Nama', 'KODE001', 'Deskripsi opsional', '{"key":"value"}']);
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $r, string $entity)
    {
        $entity    = $this->ensureEntity($entity);
        $entityRow = $this->getEntityRow($entity);
        if (!$entityRow) abort(404, 'Unknown entity.');

        $sid = $this->resolveValidSiteId($this->currentSiteId($r->user()));

        $r->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:20480']]);
        $file = $r->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) return back()->withErrors(['file' => 'Tidak bisa membaca file.']);

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['file' => 'File kosong / header tidak valid.']);
        }

        $map = [];
        foreach ($header as $idx => $col) {
            $map[Str::of($col)->lower()->replace(' ', '_')] = $idx;
        }

        if (!array_key_exists('name', $map)) {
            fclose($handle);
            return back()->withErrors(['file' => "Kolom 'name' wajib ada."]);
        }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $name        = $row[$map['name']] ?? null;
                $code        = ($map['code'] ?? null) !== null ? trim((string) $row[$map['code']]) : null;
                $description = ($map['description'] ?? null) !== null ? trim((string) $row[$map['description']]) : null;
                $extra       = ($map['extra'] ?? null) !== null ? trim((string) $row[$map['extra']]) : null;

                if (!$name || trim($name) === '') {
                    $skipped++; continue;
                }

                $payload = [
                    'entity'           => $entity,
                    'master_entity_id' => $entityRow->id,
                    'site_id'          => (\Schema::hasColumn('master_records', 'site_id') ? $sid : null),
                    'name'             => trim($name),
                    'code'             => $code ?: null,
                    'description'      => $description ?: null,
                    'extra'            => $this->normalizeExtra($extra),
                    'updated_at'       => now(),
                ];

                if ($code) {
                    $exists = DB::table('master_records')
                        ->where('entity', $entity)
                        ->when(\Schema::hasColumn('master_records', 'site_id'), function ($qq) use ($sid) {
                            if ($sid !== null) $qq->where('site_id', $sid);
                            else $qq->whereNull('site_id');
                        })
                        ->where('code', $code)
                        ->first();

                    if ($exists) {
                        DB::table('master_records')->where('id', $exists->id)->update($payload);
                        $updated++;
                    } else {
                        $payload['id']         = (string) Str::uuid();
                        $payload['created_by'] = optional($r->user())->id;
                        $payload['created_at'] = now();
                        DB::table('master_records')->insert($payload);
                        $inserted++;
                    }
                } else {
                    $payload['id']         = (string) Str::uuid();
                    $payload['created_by'] = optional($r->user())->id;
                    $payload['created_at'] = now();
                    DB::table('master_records')->insert($payload);
                    $inserted++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            return back()->withErrors(['file' => 'Gagal import: ' . $e->getMessage()]);
        }

        fclose($handle);

        return back()->with('status', "Import selesai. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}.");
    }

    public function bulkDelete(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);

        $data = $r->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'regex:/^[0-9a-fA-F-]{36}$/'],
        ]);

        $ids = $data['ids'];

        DB::transaction(function () use ($entity, $ids) {
            DB::table('master_record_permissions')->whereIn('master_record_id', $ids)->delete();
            DB::table('master_records')->where('entity', $entity)->whereIn('id', $ids)->delete();
        });

        return back()->with('status', 'Selected records deleted.');
    }

    public function duplicate(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $row = DB::table('master_records')->where('entity', $entity)->where('id', $record)->first();
        if (!$row) abort(404);

        $newId   = (string) Str::uuid();
        $newCode = $this->makeUniqueCode($entity, $row->code);

        DB::table('master_records')->insert([
            'id'               => $newId,
            'entity'           => $row->entity,
            'master_entity_id' => $row->master_entity_id ?? optional($this->getEntityRow($entity))->id,
            'site_id'          => (\Schema::hasColumn('master_records', 'site_id') ? ($row->site_id ?? null) : null),
            'name'             => $row->name . ' (Copy)',
            'code'             => $newCode,
            'description'      => $row->description,
            'extra'            => $row->extra,
            'created_by'       => $row->created_by,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('admin.master.edit', ['entity' => $entity, 'record' => $newId])
            ->with('status', 'Record duplicated.');
    }
}
