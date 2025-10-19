<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\MasterRecord;

class MasterRecordPermission extends Model
{
    use HasFactory;

    /** Tabel & primary key (UUID) */
    protected $table = 'master_record_permissions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** Mass assignable */
    protected $fillable = [
        'id',
        'master_record_id',
        'user_id',
        'can_view',
        'can_download',
        'can_update',
        'can_delete',
    ];

    /** Default attributes */
    protected $attributes = [
        'can_view'     => false,
        'can_download' => false,
        'can_update'   => false,
        'can_delete'   => false,
    ];

    /** Casting */
    protected $casts = [
        'id'               => 'string',
        'master_record_id' => 'string',
        'user_id'          => 'string',
        'can_view'         => 'boolean',
        'can_download'     => 'boolean',
        'can_update'       => 'boolean',
        'can_delete'       => 'boolean',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /** Auto-generate UUID saat create (hanya untuk path Eloquent biasa, bukan upsert) */
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->{$m->getKeyName()})) {
                $m->{$m->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /* =========================================================
     |  Relationships
     |=========================================================*/

    public function record()
    {
        return $this->belongsTo(MasterRecord::class, 'master_record_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =========================================================
     |  Query Scopes
     |=========================================================*/

    /** Scope: filter by specific ability (view/download/update/delete = true) */
    public function scopeWithAbility(Builder $q, string $ability): Builder
    {
        $flag = match ($ability) {
            'view'     => 'can_view',
            'download' => 'can_download',
            'update'   => 'can_update',
            'delete'   => 'can_delete',
            default    => 'can_view',
        };

        return $q->where($flag, true);
    }

    /** Scope: untuk record tertentu */
    public function scopeForRecord(Builder $q, string $recordId): Builder
    {
        return $q->where('master_record_id', $recordId);
    }

    /** Scope: untuk user tertentu */
    public function scopeForUser(Builder $q, string $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    /* =========================================================
     |  Helpers
     |=========================================================*/

    /** Kembalikan array ringkas kemampuan */
    public function abilities(): array
    {
        return [
            'view'     => (bool) $this->can_view,
            'download' => (bool) $this->can_download,
            'update'   => (bool) $this->can_update,
            'delete'   => (bool) $this->can_delete,
        ];
    }

    /** Helper: cek satu ability */
    public function allows(string $ability): bool
    {
        return match ($ability) {
            'view'     => (bool) $this->can_view,
            'download' => (bool) $this->can_download,
            'update'   => (bool) $this->can_update,
            'delete'   => (bool) $this->can_delete,
            default    => false,
        };
    }

    /** Grant/update permission untuk (recordId, userId) – pakai Eloquent, event tetap jalan */
    public static function grant(string $recordId, string $userId, array $abilities = ['view' => true]): self
    {
        $values = [
            'can_view'     => (bool) ($abilities['view']     ?? false),
            'can_download' => (bool) ($abilities['download'] ?? false),
            'can_update'   => (bool) ($abilities['update']   ?? false),
            'can_delete'   => (bool) ($abilities['delete']   ?? false),
        ];

        // updateOrCreate memicu creating/updating -> UUID auto terisi saat insert
        return static::updateOrCreate(
            ['master_record_id' => $recordId, 'user_id' => $userId],
            $values
        );
    }

    /** Revoke permission untuk (recordId, userId) */
    public static function revoke(string $recordId, string $userId): int
    {
        return static::query()
            ->forRecord($recordId)
            ->forUser($userId)
            ->delete();
    }

    /**
     * Sync massal untuk satu record.
     *
     * $rows contoh:
     * [
     *   ['user_id' => '...', 'view'=>1, 'download'=>0, 'update'=>0, 'delete'=>0],
     *   ...
     * ]
     *
     * Catatan penting:
     * - `upsert()` tidak memicu event model; karena PK UUID non-increment,
     *   kita sertakan kolom `id` untuk baris insert agar tidak gagal NOT NULL.
     */
    public static function syncForRecord(string $recordId, array $rows): void
    {
        // Ambil user_id yang sudah ada
        $existingUserIds = static::query()
            ->forRecord($recordId)
            ->pluck('user_id')
            ->all();

        // Normalisasi rows & siapkan upsert
        $now = now();
        $incomingUserIds = [];
        $payload = [];

        foreach ($rows as $r) {
            $uid = (string) ($r['user_id'] ?? '');
            if ($uid === '') {
                continue; // skip baris tanpa user_id
            }

            $incomingUserIds[] = $uid;

            $payload[] = [
                'id'               => (string) Str::uuid(), // diperlukan untuk insert via upsert
                'master_record_id' => $recordId,
                'user_id'          => $uid,
                'can_view'         => (bool) ($r['view']     ?? $r['can_view']     ?? false),
                'can_download'     => (bool) ($r['download'] ?? $r['can_download'] ?? false),
                'can_update'       => (bool) ($r['update']   ?? $r['can_update']   ?? false),
                'can_delete'       => (bool) ($r['delete']   ?? $r['can_delete']   ?? false),
                'created_at'       => $now, // dipakai saat insert
                'updated_at'       => $now,
            ];
        }

        // Hapus yang tidak ada di incoming
        $toDelete = array_diff($existingUserIds, $incomingUserIds);
        if (!empty($toDelete)) {
            static::query()
                ->forRecord($recordId)
                ->whereIn('user_id', $toDelete)
                ->delete();
        }

        if (!empty($payload)) {
            // Unique constraint yang diharapkan: (master_record_id, user_id)
            static::query()->upsert(
                $payload,
                ['master_record_id', 'user_id'], // kolom kunci konflik
                ['can_view', 'can_download', 'can_update', 'can_delete', 'updated_at'] // kolom yang diupdate saat konflik
            );
        }
    }
}
