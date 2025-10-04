<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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

    /** Casting */
    protected $casts = [
        'id'               => 'string',
        'master_record_id' => 'string',
        'user_id'          => 'string',
        'can_view'         => 'boolean',
        'can_download'     => 'boolean',
        'can_update'       => 'boolean',
        'can_delete'       => 'boolean',
    ];

    /** Auto-generate UUID saat create */
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

    /** Static helper: grant permission untuk (recordId, userId) */
    public static function grant(
        string $recordId,
        string $userId,
        array $abilities = ['view' => true]
    ): self {
        // upsert by unique (master_record_id, user_id)
        $values = [
            'can_view'     => (bool) ($abilities['view']     ?? false),
            'can_download' => (bool) ($abilities['download'] ?? false),
            'can_update'   => (bool) ($abilities['update']   ?? false),
            'can_delete'   => (bool) ($abilities['delete']   ?? false),
            'updated_at'   => now(),
        ];

        // coba dapatkan existing
        $existing = static::query()
            ->forRecord($recordId)
            ->forUser($userId)
            ->first();

        if ($existing) {
            $existing->fill($values)->save();

            return $existing;
        }

        // create baru
        return static::create(array_merge([
            'master_record_id' => $recordId,
            'user_id'          => $userId,
            'id'               => (string) Str::uuid(),
            'created_at'       => now(),
        ], $values));
    }

    /** Static helper: revoke permission untuk (recordId, userId) */
    public static function revoke(string $recordId, string $userId): int
    {
        return static::query()
            ->forRecord($recordId)
            ->forUser($userId)
            ->delete();
    }

    /**
     * Sync massal: set permissions untuk satu record berdasarkan array rows:
     * [
     *   ['user_id' => '...', 'view'=>1, 'download'=>0, 'update'=>0, 'delete'=>0],
     *   ...
     * ]
     * Menghapus yang tidak ada di $rows.
     */
    public static function syncForRecord(string $recordId, array $rows): void
    {
        // Hapus semua dulu → insert ulang (sederhana, konsisten)
        static::query()->forRecord($recordId)->delete();

        if (empty($rows)) {
            return;
        }

        $now = now();
        $payload = [];
        foreach ($rows as $r) {
            $payload[] = [
                'id'               => (string) Str::uuid(),
                'master_record_id' => $recordId,
                'user_id'          => (string) $r['user_id'],
                'can_view'         => (bool) ($r['view']     ?? $r['can_view']     ?? false),
                'can_download'     => (bool) ($r['download'] ?? $r['can_download'] ?? false),
                'can_update'       => (bool) ($r['update']   ?? $r['can_update']   ?? false),
                'can_delete'       => (bool) ($r['delete']   ?? $r['can_delete']   ?? false),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        // gunakan insert chunked untuk skala besar
        foreach (array_chunk($payload, 1000) as $chunk) {
            static::query()->insert($chunk);
        }
    }
}
