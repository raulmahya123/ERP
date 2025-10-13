<?php

// app/Models/MasterEntity.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MasterEntity extends Model
{
    use HasFactory;

    protected $table = 'master_entities';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id','key','label','enabled','sort','schema','icon','color_from','color_to'
    ];

    protected $attributes = [
        'enabled' => false,
        'sort'    => 0,
    ];

    protected $casts = [
        'schema'  => 'array',
        'enabled' => 'boolean',
        'sort'    => 'integer',
    ];

    /** ===== Relations ===== */
    public function masterRecords()
    {
        return $this->hasMany(MasterRecord::class, 'master_entity_id');
    }

    /** ===== Scopes ===== */
    public function scopeEnabled($q, bool $state = true)
    {
        return $q->where('enabled', $state);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort')->orderBy('label');
    }

    /** ===== Events / Normalization ===== */
    protected static function booted()
    {
        static::creating(function (self $m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
            $m->key = Str::slug((string) $m->key, '_');
            if (is_string($m->schema ?? null)) {
                $m->schema = json_decode($m->schema, true);
            }
            $m->enabled = (bool) ($m->enabled ?? false);
            $m->sort ??= 0;
        });

        static::saving(function (self $m) {
            $m->key = Str::slug((string) $m->key, '_');
            if (is_string($m->schema ?? null)) {
                $m->schema = json_decode($m->schema, true);
            }
        });
    }
}
