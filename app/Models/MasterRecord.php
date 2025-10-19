<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MasterRecord extends Model
{
    use HasFactory;

    protected $table = 'master_records';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** Mass-assignable fields */
    protected $fillable = [
        'id',
        'master_entity_id',
        'site_id',
        'name',
        'code',
        'description',
        'extra',
        'created_by',
        'entity',           // <— WAJIB: biar bisa diisi
    ];

    /** Casting */
    protected $casts = [
        'id'               => 'string',
        'master_entity_id' => 'string',
        'site_id'          => 'string',
        'created_by'       => 'string',
        'extra'            => 'array',    // biarkan Laravel yang encode/decode JSON
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            if (is_string($model->code)) {
                $model->code = trim($model->code) ?: null;
            }

            // === Auto-set kolom 'entity' bila kosong ===
            if (empty($model->entity) && !empty($model->master_entity_id)) {
                $ref = \App\Models\MasterEntity::query()
                    ->select(['id', 'key', 'slug', 'name'])
                    ->find($model->master_entity_id);

                if ($ref) {
                    $model->entity = $ref->key
                        ?? $ref->slug
                        ?? Str::slug((string) $ref->name);
                }
            }
        });

        static::saving(function (self $model) {
            if (is_string($model->code)) {
                $model->code = trim($model->code) ?: null;
            }
        });
    }

    /* =========================================================
     |  Mutators (normalisasi aman)
     |=========================================================*/

    /**
     * Terima array atau string JSON untuk kolom extra; simpan sebagai array.
     * Karena sudah di-cast ke 'array', JANGAN json_encode manual.
     */
    public function setExtraAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['extra'] = $value;
            return;
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') {
                $this->attributes['extra'] = null;
                return;
            }
            try {
                $decoded = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                $this->attributes['extra'] = $decoded;
            } catch (\Throwable $e) {
                // simpan sebagai array dengan satu kunci 'raw' agar tetap valid JSON
                $this->attributes['extra'] = ['raw' => $trim];
            }
            return;
        }

        $this->attributes['extra'] = null;
    }

    /* =========================================================
     |  Relationships
     |=========================================================*/

    /** GANTI NAMA: hindari bentrok dengan kolom 'entity' */
    public function masterEntity()
    {
        return $this->belongsTo(\App\Models\MasterEntity::class, 'master_entity_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function permissions()
    {
        return $this->hasMany(\App\Models\MasterRecordPermission::class, 'master_record_id');
    }

    public function permittedUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'master_record_permissions', 'master_record_id', 'user_id')
            ->withPivot(['can_view', 'can_download', 'can_update', 'can_delete'])
            ->withTimestamps();
    }

    /* =========================================================
     |  Query Scopes
     |=========================================================*/

    /**
     * Filter berdasarkan entity (UUID id atau key pada master_entities.key)
     */
    public function scopeEntity(Builder $q, string $entity): Builder
    {
        $isUuid = (bool) preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
            $entity
        );

        return $isUuid
            ? $q->where('master_entity_id', $entity)
            : $q->whereHas('masterEntity', fn(Builder $w) => $w->where('key', $entity));
    }

    public function scopeSite(Builder $q, ?string $siteId): Builder
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeForSite(Builder $q, ?string $siteId): Builder
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

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

    public function scopeCode(Builder $q, string $entity, string $code): Builder
    {
        return $q->entity($entity)->where('code', $code);
    }

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

    public function userCan(\App\Models\User|string $user, string $ability = 'view'): bool
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
