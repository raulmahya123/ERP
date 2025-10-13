<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use App\Models\Traits\HasUuid;

class Location extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'locations';
    public $incrementing = false;
    protected $keyType = 'string';

    /** Field yang bisa diisi via form */
    protected $fillable = [
        'site_id',
        'name',
        'longitude',
        'latitude',
        'geofence_radius_m',
        // NOTE: 'created_by' & 'updated_by' di-set otomatis, tidak perlu di-fillable
    ];

    /** Cast angka */
    protected $casts = [
        'latitude'           => 'float',
        'longitude'          => 'float',
        'geofence_radius_m'  => 'integer',
    ];

    /** Eager load default untuk kebutuhan tabel (site & creator) */
    protected $with = ['site', 'creator'];

    /* =======================
     | Relations
     |=======================*/
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function permissions()
    {
        return $this->hasMany(LocationPermission::class);
    }

    /* =======================
     | Scopes bantu
     |=======================*/
    public function scopeSite($q, ?string $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeSearch($q, ?string $kw)
    {
        $kw = trim((string) $kw);
        return $kw !== '' ? $q->where('name', 'like', "%{$kw}%") : $q;
    }

    /* =======================
     | Auto audit
     |=======================*/
    protected static function booted()
    {
        static::creating(function (self $m) {
            if (auth()->check() && Schema::hasColumn($m->getTable(), 'created_by') && empty($m->created_by)) {
                $m->created_by = auth()->id();
            }
        });

        static::updating(function (self $m) {
            if (auth()->check() && Schema::hasColumn($m->getTable(), 'updated_by')) {
                $m->updated_by = auth()->id();
            }
        });
    }
}
