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
        'entity',
        'name',
        'code',
        'description',
        'extra',
        'created_by',
    ];

    /** Casting */
    protected $casts = [
        'id'         => 'string',
        'created_by' => 'string',
        'extra'      => 'array',   // simpan/ambil sebagai array JSON
    ];

    /** Route binding pakai UUID */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /** Auto-generate UUID saat create */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /* =========================================================
     |  Relationships
     |=========================================================*/

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

    /** Filter berdasarkan entity */
    public function scopeEntity(Builder $q, string $entity): Builder
    {
        return $q->where('entity', $entity);
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

    /** Cari berdasarkan code dalam entity yg sama */
    public function scopeCode(Builder $q, string $entity, string $code): Builder
    {
        return $q->where('entity', $entity)->where('code', $code);
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
