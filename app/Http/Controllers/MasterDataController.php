<?php

namespace App\Http\Controllers;

use App\Models\MasterRecord;
use App\Models\MasterRecordPermission;
use App\Models\User;
use App\Support\SiteContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule as UniqueRule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterDataController extends Controller
{
    /* =========================
     | Helpers (DRY)
     |=========================*/

    protected function normalizeEntityKey(string $key): string
    {
        return Str::slug($key, '_');
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

    protected function getEntityRow(string $entity): ?object
    {
        return DB::table('master_entities')
            ->where('key', $entity)
            ->where('enabled', 1)
            ->first();
    }

    protected function currentSiteId(?\App\Models\User $user): ?string
    {
        return SiteContext::currentSiteId($user);
    }

    protected function resolveValidSiteId(?string $sid): ?string
    {
        if (!$sid) return null;
        return DB::table('sites')->where('id', $sid)->value('id') ?: null;
    }

    /**
     * Unique rule "code" per (entity, site_id).
     */
    protected function uniqueCodeRule(string $entity, ?string $sid, ?string $ignoreId = null)
    {
        $rule = UniqueRule::unique('master_records', 'code')
            ->where(function ($q) use ($entity, $sid) {
                $q->where('entity', $entity);
                if (\Schema::hasColumn('master_records', 'site_id')) {
                    $validSid = $this->resolveValidSiteId($sid);
                    if ($validSid !== null) $q->where('site_id', $validSid);
                    else $q->whereNull('site_id');
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
                // simpan sebagai string JSON kalau bukan JSON valid
                return json_encode($trim, JSON_UNESCAPED_UNICODE);
            }
        }
        return null;
    }

    /* =========================
     | RBAC helpers (per-record)
     |=========================*/

    protected function isGm(?\App\Models\User $user): bool
    {
        if (!$user) return false;

        if (isset($user->role) && is_string($user->role) && mb_strtolower($user->role) === 'gm') return true;

        if (method_exists($user, 'role')) {
            try {
                $user->loadMissing('role');
            } catch (\Throwable $e) {
            }
            $vals = [
                mb_strtolower($user->role->key   ?? ''),
                mb_strtolower($user->role->slug  ?? ''),
                mb_strtolower($user->role->name  ?? ''),
                mb_strtolower($user->role->title ?? ''),
            ];
            if (in_array('gm', $vals, true)) return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('gm')) return true;

        return false;
    }

    /**
     * Cek izin per-record:
     * - GM always true
     * - Jika record TIDAK punya baris permission -> true (default open)
     * - Jika ada baris → harus punya flag sesuai ability
     */
    protected function assertRecordAbility(?\App\Models\User $user, MasterRecord $record, string $ability): void
    {
        if ($this->isGm($user)) return;

        $hasAnyRow = $record->permissions()->exists();
        if (!$hasAnyRow) return;

        $row = $record->permissions()
            ->where('user_id', optional($user)->id)
            ->first(['can_view', 'can_download', 'can_update', 'can_delete']);

        $ok = false;
        if ($row) {
            $map = [
                'view'     => (bool)$row->can_view,
                'download' => (bool)$row->can_download,
                'update'   => (bool)$row->can_update,
                'delete'   => (bool)$row->can_delete,
            ];
            $ok = $map[$ability] ?? false;
        }

        abort_unless($ok, 403, 'Anda tidak berwenang untuk aksi ini pada record tersebut.');
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

        $q = MasterRecord::query()
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

        return view('admin.master.overview', compact(
            'allowedEntities',
            'labels',
            'masterTotals',
            'currentSiteId',
            'meta'
        ));
    }

    /* =========================
     | CRUD
     |=========================*/

    public function index(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);
        $sid    = $this->currentSiteId($r->user());

        $records = MasterRecord::query()
            ->entity($entity)
            ->forSite($sid)
            ->search($r->string('q'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.master.index', [
            'entity'  => $entity,
            'records' => $records,
            'search'  => (string) $r->get('q', ''),
        ]);
    }

    public function create(string $entity)
    {
        $entity = $this->ensureEntity($entity);
        return view('admin.master.create', compact('entity'));
    }

    public function store(Request $r, string $entity)
    {
        $entity    = $this->ensureEntity($entity);
        $entityRow = $this->getEntityRow($entity);
        if (!$entityRow) abort(404, 'Unknown entity.');

        $rawSid = $this->currentSiteId($r->user());
        $sid    = $this->resolveValidSiteId($rawSid);

        $data = $r->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:255', $this->uniqueCodeRule($entity, $sid)],
            'description' => ['nullable', 'string'],
            'extra'       => ['nullable'],
        ]);

        $record = new MasterRecord();
        $record->fill([
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
        $record->save();

        return redirect()->route('admin.master.index', $entity)->with('status', 'Record created.');
    }

    public function show(Request $r, string $entity, string $recordId)
    {
        $entity = $this->ensureEntity($entity);

        $record = MasterRecord::query()->findOrFail($recordId);
        abort_unless((string) $record->entity === (string) $entity, 404);

        $this->assertRecordAbility($r->user(), $record, 'view');

        return view('admin.master.show', [
            'entity'     => $entity,
            'record'     => $record,
            'extraArray' => $record->extra_json,
        ]);
    }

    public function edit(Request $r, string $entity, string $recordId)
    {
        $entity = $this->ensureEntity($entity);

        $record = MasterRecord::query()->findOrFail($recordId);
        abort_unless((string) $record->entity === (string) $entity, 404);

        $this->assertRecordAbility($r->user(), $record, 'update');

        return view('admin.master.edit', [
            'entity'     => $entity,
            'record'     => $record,
            'extraArray' => $record->extra_json,
        ]);
    }

    public function update(Request $r, string $entity, string $recordId)
    {
        $entity = $this->ensureEntity($entity);

        $record = MasterRecord::query()->findOrFail($recordId);
        abort_unless((string) $record->entity === (string) $entity, 404);

        $this->assertRecordAbility($r->user(), $record, 'update');

        $sid = (\Schema::hasColumn('master_records', 'site_id') ? ($record->site_id ?? null) : null);

        $data = $r->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:255', $this->uniqueCodeRule($entity, $sid, $record->id)],
            'description' => ['nullable', 'string'],
            'extra'       => ['nullable'],
        ]);

        $record->fill([
            'name'        => $data['name'],
            'code'        => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'extra'       => $this->normalizeExtra($data['extra'] ?? null),
            'updated_at'  => now(),
        ])->save();

        return redirect()->route('admin.master.index', $entity)->with('status', 'Record updated.');
    }

    public function destroy(Request $r, string $entity, string $recordId)
    {
        $entity = $this->ensureEntity($entity);

        $record = MasterRecord::query()->findOrFail($recordId);
        abort_unless((string) $record->entity === (string) $entity, 404);

        $this->assertRecordAbility($r->user(), $record, 'delete');

        DB::transaction(function () use ($record) {
            $record->permissions()->delete();
            $record->delete();
        });

        return redirect()->route('admin.master.index', ['entity' => $entity])
            ->with('status', 'Record deleted.');
    }

    /* =========================
     | Permissions (per-record)
     |=========================*/

    public function permissions(string $entity, string $recordId)
    {
        $entity = $this->ensureEntity($entity);

        $record = MasterRecord::query()
            ->entity($entity)
            ->findOrFail($recordId);

        // Hanya GM / yang punya akses manage master data
        if (! Gate::allows('manage-master-data') && ! $this->isGm(auth()->user())) {
            abort(403, 'Tidak berwenang mengelola permission.');
        }

        $users = User::query()
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->select('users.id', 'users.name', 'users.email', 'roles.key as role_key', 'roles.name as role_name')
            ->orderBy('users.name')
            ->get();

        $perms = $record->permissions()->get()->keyBy('user_id');

        return view('admin.master.permissions', [
            'entity' => $entity,
            'record' => $record,
            'users'  => $users,
            'perms'  => $perms,
        ]);
    }

    public function permissionsUpdate(Request $r, string $entity, string $recordId)
    {
        $entity = $this->ensureEntity($entity);

        $record = MasterRecord::query()
            ->entity($entity)
            ->findOrFail($recordId);

        if (! Gate::allows('manage-master-data') && ! $this->isGm(auth()->user())) {
            abort(403, 'Tidak berwenang mengelola permission.');
        }

        $data = $r->validate([
            'permissions'                => ['nullable', 'array'],
            'permissions.*.user_id'      => ['required', 'string', 'exists:users,id'],
            'permissions.*.can_view'     => ['nullable', 'boolean'],
            'permissions.*.can_download' => ['nullable', 'boolean'],
            'permissions.*.can_update'   => ['nullable', 'boolean'],
            'permissions.*.can_delete'   => ['nullable', 'boolean'],
        ]);

        $rows = $data['permissions'] ?? [];

        DB::transaction(function () use ($record, $rows) {
            $record->permissions()->delete();

            if (empty($rows)) return;

            $now = now();
            $payload = [];
            foreach ($rows as $row) {
                $payload[] = [
                    'id'               => (string) Str::uuid(),
                    'master_record_id' => $record->id,
                    'user_id'          => $row['user_id'],
                    'can_view'         => (bool) ($row['can_view']     ?? false),
                    'can_download'     => (bool) ($row['can_download'] ?? false),
                    'can_update'       => (bool) ($row['can_update']   ?? false),
                    'can_delete'       => (bool) ($row['can_delete']   ?? false),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
            MasterRecordPermission::query()->insert($payload);
        });

        return redirect()->route('admin.master.permissions', ['entity' => $entity, 'record' => $recordId])
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

        $base = MasterRecord::query()->entity($entity)->forSite($sid)->search($q);

        $total = (clone $base)->count();
        $items = $base->orderBy('name')
            ->forPage($page, $limit)
            ->get(['id', 'name', 'code']);

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

        $search   = trim((string) $r->get('q', ''));
        $filename = $entity . '_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($entity, $search, $sid) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'entity', 'name', 'code', 'description', 'extra', 'created_by', 'site_id', 'created_at', 'updated_at']);

            $q = MasterRecord::query()->entity($entity)->forSite($sid)->search($search)->orderBy('name');

            $q->chunk(1000, function ($rows) use ($out) {
                /** @var \App\Models\MasterRecord $row */
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
            fputcsv($out, ['name', 'code', 'description', 'extra']);
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
                    $skipped++;
                    continue;
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
                    $exists = MasterRecord::query()
                        ->entity($entity)
                        ->when(\Schema::hasColumn('master_records', 'site_id'), function ($qq) use ($sid) {
                            if ($sid !== null) $qq->where('site_id', $sid);
                            else $qq->whereNull('site_id');
                        })
                        ->where('code', $code)
                        ->first();

                    if ($exists) {
                        $exists->fill($payload)->save();
                        $updated++;
                    } else {
                        $payload['id']         = (string) Str::uuid();
                        $payload['created_by'] = optional(auth()->user())->id;
                        $payload['created_at'] = now();
                        MasterRecord::query()->create($payload);
                        $inserted++;
                    }
                } else {
                    $payload['id']         = (string) Str::uuid();
                    $payload['created_by'] = optional(auth()->user())->id;
                    $payload['created_at'] = now();
                    MasterRecord::query()->create($payload);
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
            MasterRecordPermission::query()->whereIn('master_record_id', $ids)->delete();
            MasterRecord::query()->entity($entity)->whereIn('id', $ids)->delete();
        });

        return back()->with('status', 'Selected records deleted.');
    }

    protected function makeUniqueCode(string $entity, ?string $baseCode): ?string
    {
        if (!$baseCode) return null;

        $suffix = '-COPY';
        $candidate = $baseCode . $suffix;

        $exists = fn($code) => MasterRecord::query()
            ->entity($entity)
            ->where('code', $code)
            ->exists();

        if (!$exists($candidate)) return $candidate;

        for ($i = 2; $i <= 50; $i++) {
            $candidate = $baseCode . $suffix . $i;
            if (!$exists($candidate)) return $candidate;
        }
        return $baseCode . $suffix . '-' . substr((string) Str::uuid(), 0, 8);
    }

    public function duplicate(Request $r, string $entity, string $recordId)
    {
        $entity = $this->ensureEntity($entity);

        $row = MasterRecord::query()->entity($entity)->findOrFail($recordId);

        // minimal perlu bisa view (atau ganti ke 'update' jika mau)
        $this->assertRecordAbility($r->user(), $row, 'view');

        $newId   = (string) Str::uuid();
        $newCode = $this->makeUniqueCode($entity, $row->code);

        $copy = $row->replicate(['id', 'code', 'created_at', 'updated_at']);
        $copy->id         = $newId;
        $copy->name       = $row->name . ' (Copy)';
        $copy->code       = $newCode;
        $copy->created_at = now();
        $copy->updated_at = now();
        $copy->save();

        return redirect()->route('admin.master.edit', ['entity' => $entity, 'record' => $newId])
            ->with('status', 'Record duplicated.');
    }

    public function publicShow(Request $r, string $recordId)
    {
        // Ambil entity dari parameter route (bisa dari defaults), fallback ke 'accounts' bila kosong
        $entityParam = (string) ($r->route('entity') ?? 'accounts');
        $entity = $this->ensureEntity($entityParam);

        $sid = $this->currentSiteId($r->user());

        $row = MasterRecord::query()
            ->entity($entity)
            ->forSite($sid) // atau ->forSiteOrGlobal($sid) sesuai kebutuhan
            ->findOrFail($recordId);

        return view('admin.master.show', [
            'entity'     => $entity,
            'record'     => $row,
            'extraArray' => $row->extra_json,
        ]);
    }


    public function permissionsQuery(Request $r)
    {
        $entity = (string) $r->query('entity', '');
        $record = (string) $r->query('record', '');

        if ($entity === '' || $record === '' || !preg_match('/^[0-9a-fA-F-]{36}$/', $record)) {
            abort(404, 'Missing or invalid entity/record.');
        }

        return $this->permissions($entity, $record);
    }
}
