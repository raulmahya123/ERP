<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterDataController extends Controller
{
    /**
     * Daftar entity yang diizinkan.
     * Sinkronkan dengan Route::pattern('entity', ...) di web.php kalau kamu pakai pattern ketat.
     */
    protected array $entities = [
        'units',
        'pits',
        'stockpiles',
        'cost_centers',
        'accounts',
        'employees',
        'asset_categories',
    ];

    /** Pastikan entity valid */
    protected function ensureEntity(string $entity): string
    {
        if (! in_array($entity, $this->entities, true)) {
            abort(404, 'Unknown entity.');
        }
        return $entity;
    }

    /** Normalisasi kolom extra -> simpan sebagai JSON string atau null */
    protected function normalizeExtra($extra): ?string
    {
        if (is_array($extra)) {
            return json_encode($extra, JSON_UNESCAPED_UNICODE);
        }
        if (is_string($extra) && trim($extra) !== '') {
            $trim = trim($extra);
            if ((Str::startsWith($trim, '{') && Str::endsWith($trim, '}')) ||
                (Str::startsWith($trim, '[') && Str::endsWith($trim, ']'))) {
                try {
                    $decoded = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                    return json_encode($decoded, JSON_UNESCAPED_UNICODE);
                } catch (\Throwable $e) {
                    // biar validator yang nangkap jika diminta json
                }
            }
        }
        return null;
    }

    /** Helper: buat code duplicate yang unik dalam satu entity */
    protected function makeUniqueCode(string $entity, ?string $baseCode): ?string
    {
        if (!$baseCode) {
            return null;
        }

        $suffix = '-COPY';
        $candidate = $baseCode.$suffix;
        $exists = fn($code) => DB::table('master_records')
                                ->where('entity', $entity)
                                ->where('code', $code)
                                ->exists();

        if (!$exists($candidate)) {
            return $candidate;
        }
        // tambahkan angka/random pendek
        for ($i = 2; $i <= 50; $i++) {
            $candidate = $baseCode.$suffix.$i;
            if (!$exists($candidate)) return $candidate;
        }
        // fallback terakhir – gunakan potongan uuid
        return $baseCode.$suffix.'-'.substr((string) Str::uuid(), 0, 8);
    }

    /** GET /admin/master/{entity} */
    public function index(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);

        $q = DB::table('master_records')->where('entity', $entity);

        if ($search = trim((string) $r->get('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $records = $q->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.master.index', [
            'entity'  => $entity,
            'records' => $records,
            'search'  => $r->get('q', ''),
        ]);
    }

    /** GET /admin/master/{entity}/create */
    public function create(string $entity)
    {
        $entity = $this->ensureEntity($entity);

        return view('admin.master.create', [
            'entity' => $entity,
        ]);
    }

    /** POST /admin/master/{entity} */
    public function store(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);

        $rules = [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => [
                'nullable', 'string', 'max:255',
                Rule::unique('master_records', 'code')->where(fn($q) => $q->where('entity', $entity)),
            ],
            'description' => ['nullable', 'string'],
            'extra'       => ['nullable'],
        ];
        if (is_string($r->input('extra'))) {
            $rules['extra'][] = 'json';
        }

        $data = $r->validate($rules);

        DB::table('master_records')->insert([
            'id'          => (string) Str::uuid(),
            'entity'      => $entity,
            'name'        => $data['name'],
            'code'        => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'extra'       => $this->normalizeExtra($data['extra'] ?? null),
            'created_by'  => optional($r->user())->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.index', ['entity' => $entity])
            ->with('status', 'Record created.');
    }

    /** GET /admin/master/{entity}/{record} (opsional) */
    public function show(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $row = DB::table('master_records')
            ->where('entity', $entity)
            ->where('id', $record)
            ->first();

        if (!$row) abort(404);

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

    /** GET /admin/master/{entity}/{record}/edit */
    public function edit(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $row = DB::table('master_records')
            ->where('entity', $entity)
            ->where('id', $record)
            ->first();

        if (!$row) abort(404);

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

    /** PUT /admin/master/{entity}/{record} */
    public function update(Request $r, string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $exists = DB::table('master_records')
            ->where('entity', $entity)
            ->where('id', $record)
            ->exists();
        if (!$exists) abort(404);

        $rules = [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => [
                'nullable', 'string', 'max:255',
                Rule::unique('master_records', 'code')->where(
                    fn($q) => $q->where('entity', $entity)->where('id', '!=', $record)
                ),
            ],
            'description' => ['nullable', 'string'],
            'extra'       => ['nullable'],
        ];
        if (is_string($r->input('extra'))) {
            $rules['extra'][] = 'json';
        }

        $data = $r->validate($rules);

        DB::table('master_records')
            ->where('entity', $entity)
            ->where('id', $record)
            ->update([
                'name'        => $data['name'],
                'code'        => $data['code'] ?? null,
                'description' => $data['description'] ?? null,
                'extra'       => $this->normalizeExtra($data['extra'] ?? null),
                'updated_at'  => now(),
            ]);

        return redirect()->route('admin.master.index', ['entity' => $entity])
            ->with('status', 'Record updated.');
    }

    /** DELETE /admin/master/{entity}/{record} */
    public function destroy(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        DB::transaction(function () use ($entity, $record) {
            DB::table('master_record_permissions')
                ->where('master_record_id', $record)
                ->delete();

            DB::table('master_records')
                ->where('entity', $entity)
                ->where('id', $record)
                ->delete();
        });

        return redirect()->route('admin.master.index', ['entity' => $entity])
            ->with('status', 'Record deleted.');
    }

    /** GET /admin/master/{entity}/{record}/permissions */
    public function permissions(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $rec = DB::table('master_records')
            ->where('entity', $entity)
            ->where('id', $record)
            ->first();
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

    /** POST /admin/master/{entity}/{record}/permissions */
    public function permissionsUpdate(Request $r, string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $exists = DB::table('master_records')
            ->where('entity', $entity)
            ->where('id', $record)
            ->exists();
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
            DB::table('master_record_permissions')
              ->where('master_record_id', $record)
              ->delete();

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

    /* =========================================================
     |  Tambahan: lookup, export/import, template, bulk delete, duplicate
     |=========================================================*/

    /** GET /admin/master/{entity}/lookup?q=&page=&limit= */
    public function lookup(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);

        $q      = trim((string) $r->get('q', ''));
        $limit  = max(1, min(100, (int) $r->get('limit', 10)));
        $page   = max(1, (int) $r->get('page', 1));

        $base = DB::table('master_records')->where('entity', $entity);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $total = (clone $base)->count();
        $items = $base->orderBy('name')
                      ->forPage($page, $limit)
                      ->get(['id','name','code']);

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

    /** GET /admin/master/{entity}/export (CSV) */
    public function export(Request $r, string $entity): StreamedResponse
    {
        $entity = $this->ensureEntity($entity);

        $search = trim((string) $r->get('q', ''));
        $filename = $entity.'_'.now()->format('Ymd_His').'.csv';

        $callback = function () use ($entity, $search) {
            $out = fopen('php://output', 'w');
            // header
            fputcsv($out, ['id','entity','name','code','description','extra','created_by','created_at','updated_at']);

            $q = DB::table('master_records')->where('entity', $entity);
            if ($search !== '') {
                $q->where(function ($w) use ($search) {
                    $w->where('name','like',"%{$search}%")
                      ->orWhere('code','like',"%{$search}%")
                      ->orWhere('description','like',"%{$search}%");
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

    /** GET /admin/master/{entity}/import-template (CSV header + contoh) */
    public function importTemplate(string $entity): StreamedResponse
    {
        $entity = $this->ensureEntity($entity);
        $filename = $entity.'_template.csv';

        $callback = function () use ($entity) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name','code','description','extra']); // header
            // contoh baris (opsional)
            fputcsv($out, ['Contoh Nama','KODE001','Deskripsi opsional','{"key":"value"}']);
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** POST /admin/master/{entity}/import (CSV) */
    public function import(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);

        $r->validate([
            'file' => ['required','file','mimes:csv,txt','max:20480'], // 20MB
        ]);

        $file = $r->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'Tidak bisa membaca file.']);
        }

        // baca header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['file' => 'File kosong / header tidak valid.']);
        }

        // normalisasi header
        $map = [];
        foreach ($header as $idx => $col) {
            $key = Str::of($col)->lower()->replace(' ', '_')->toString();
            $map[$key] = $idx;
        }

        $requiredCols = ['name'];
        foreach ($requiredCols as $rc) {
            if (!array_key_exists($rc, $map)) {
                fclose($handle);
                return back()->withErrors(['file' => "Kolom '{$rc}' wajib ada."]);
            }
        }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $name        = $row[$map['name']] ?? null;
                $code        = $map['code']        ?? null;   $code        = $code !== null ? trim((string) $row[$code]) : null;
                $description = $map['description'] ?? null;   $description = $description !== null ? trim((string) $row[$description]) : null;
                $extra       = $map['extra']       ?? null;   $extra       = $extra !== null ? trim((string) $row[$extra]) : null;

                if (!$name || trim($name) === '') {
                    $skipped++; continue;
                }

                $payload = [
                    'entity'      => $entity,
                    'name'        => trim($name),
                    'code'        => $code ?: null,
                    'description' => $description ?: null,
                    'extra'       => $this->normalizeExtra($extra),
                    'updated_at'  => now(),
                ];

                if ($code) {
                    // upsert by (entity, code)
                    $exists = DB::table('master_records')
                        ->where('entity', $entity)
                        ->where('code', $code)
                        ->first();

                    if ($exists) {
                        DB::table('master_records')
                            ->where('id', $exists->id)
                            ->update($payload);
                        $updated++;
                    } else {
                        $payload['id'] = (string) Str::uuid();
                        $payload['created_by'] = optional($r->user())->id;
                        $payload['created_at'] = now();
                        DB::table('master_records')->insert($payload);
                        $inserted++;
                    }
                } else {
                    // tanpa code -> insert saja
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
            return back()->withErrors(['file' => 'Gagal import: '.$e->getMessage()]);
        }

        fclose($handle);

        return back()->with('status', "Import selesai. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}.");
    }

    /** DELETE /admin/master/{entity}/bulk-delete  (body: ids[] UUID) */
    public function bulkDelete(Request $r, string $entity)
    {
        $entity = $this->ensureEntity($entity);

        $data = $r->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['string','regex:/^[0-9a-fA-F-]{36}$/'],
        ]);

        $ids = $data['ids'];

        DB::transaction(function () use ($entity, $ids) {
            DB::table('master_record_permissions')->whereIn('master_record_id', $ids)->delete();
            DB::table('master_records')->where('entity', $entity)->whereIn('id', $ids)->delete();
        });

        return back()->with('status', 'Selected records deleted.');
    }

    /** POST /admin/master/{entity}/{record}/duplicate */
    public function duplicate(string $entity, string $record)
    {
        $entity = $this->ensureEntity($entity);

        $row = DB::table('master_records')
            ->where('entity', $entity)
            ->where('id', $record)
            ->first();

        if (!$row) abort(404);

        $newId   = (string) Str::uuid();
        $newCode = $this->makeUniqueCode($entity, $row->code);

        DB::table('master_records')->insert([
            'id'          => $newId,
            'entity'      => $entity,
            'name'        => $row->name . ' (Copy)',
            'code'        => $newCode,
            'description' => $row->description,
            'extra'       => $row->extra,
            'created_by'  => $row->created_by,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('admin.master.edit', ['entity' => $entity, 'record' => $newId])
            ->with('status', 'Record duplicated.');
    }
}
