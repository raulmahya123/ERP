<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MasterRecord extends Model
{
    use HasFactory;

    /** Tabel & primary key */
    protected $table = 'master_records';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** Mass-assignable fields */
    protected $fillable = [
        'id',
        'master_entity_id', // ⬅️ ganti dari 'entity' ke FK id
        'site_id',          // opsional (multi-site)
        'name',
        'code',
        'description',
        'extra',
        'created_by',
    ];

    /** Casting */
    protected $casts = [
        'id'               => 'string',
        'master_entity_id' => 'string',
        'site_id'          => 'string',
        'created_by'       => 'string',
        'extra'            => 'array', // simpan/ambil sebagai array JSON
    ];

    /** Route binding pakai UUID */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /** Auto-generate UUID & normalisasi kecil saat create/save */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (is_string($model->extra ?? null)) {
                $model->extra = json_decode($model->extra, true);
            }
            $model->code = is_string($model->code ?? null)
                ? trim($model->code)
                : $model->code;
        });

        static::saving(function (self $model) {
            if (is_string($model->extra ?? null)) {
                $model->extra = json_decode($model->extra, true);
            }
            $model->code = is_string($model->code ?? null)
                ? trim($model->code)
                : $model->code;
        });
    }

    /* =========================================================
     |  Relationships
     |=========================================================*/

    /** Entity induk (FK: master_entity_id) */
    public function entity()
    {
        return $this->belongsTo(MasterEntity::class, 'master_entity_id');
    }

    /** Pembuat record (users.id = UUID) */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Daftar permission baris-per-baris untuk record ini */
    public function permissions()
    {
        return $this->hasMany(MasterRecordPermission::class, 'master_record_id');
    }

    /** User yang diizinkan mengakses record ini (pivot = flags izin) */
    public function permittedUsers()
    {
        return $this->belongsToMany(User::class, 'master_record_permissions', 'master_record_id', 'user_id')
            ->withPivot(['can_view', 'can_download', 'can_update', 'can_delete'])
            ->withTimestamps();
    }

    /* =========================================================
     |  Query Scopes
     |=========================================================*/

    /** Filter berdasarkan entity: terima UUID (id) atau key */
    public function scopeEntity(Builder $q, string $entity): Builder
    {
        $isUuid = (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
            $entity
        );

        return $isUuid
            ? $q->where('master_entity_id', $entity)
            : $q->whereHas('entity', fn (Builder $w) => $w->where('key', $entity));
    }

    /** Filter per site (opsional) */
    public function scopeSite(Builder $q, ?string $siteId): Builder
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    /** Pencarian sederhana name/code/description */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function (Builder $w) use ($term) {
            $w->where('name', 'like', "%{$term}%")
              ->orWhere('code', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Cari berdasarkan code dalam entity yang sama.
     * $entity bisa UUID (id) atau key.
     */
    public function scopeCode(Builder $q, string $entity, string $code): Builder
    {
        return $q->entity($entity)->where('code', $code);
    }

    /**
     * Filter record yang user punya izin tertentu.
     * $ability: 'view' | 'download' | 'update' | 'delete'
     */
    public function scopeWhereUserCan(Builder $q, string $userId, string $ability = 'view'): Builder
    {
        $flag = match ($ability) {
            'view'     => 'can_view',
            'download' => 'can_download',
            'update'   => 'can_update',
            'delete'   => 'can_delete',
            default    => 'can_view',
        };

        return $q->whereHas('permissions', function (Builder $w) use ($userId, $flag) {
            $w->where('user_id', $userId)->where($flag, true);
        });
    }

    /* =========================================================
     |  Helpers
     |=========================================================*/

    /**
     * Cek cepat: user boleh aksi tertentu pada record ini?
     * $ability: 'view' | 'download' | 'update' | 'delete'
     */
    public function userCan(User|string $user, string $ability = 'view'): bool
    {
        $userId = is_string($user) ? $user : $user->id;

        $perm = $this->permissions()
            ->where('user_id', $userId)
            ->first(['can_view', 'can_download', 'can_update', 'can_delete']);

        if (!$perm) return false;

        return match ($ability) {
            'view'     => (bool) $perm->can_view,
            'download' => (bool) $perm->can_download,
            'update'   => (bool) $perm->can_update,
            'delete'   => (bool) $perm->can_delete,
            default    => false,
        };
    }
}
