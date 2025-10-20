<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HourMeter extends Model
{
    use HasUuids;
    protected $table = 'scm_hour_meters';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'site_id',
        'date',
        'shift_id',
        'unit_id',
        'hm_start',
        'hm_end',
        'hm_delta',
        'anomaly',
        'client_uid',
        'created_by'
    ];

    protected $casts = [
        'date'    => 'date',
        'anomaly' => 'boolean',
        'hm_start' => 'decimal:1',
        'hm_end'  => 'decimal:1',
        'hm_delta' => 'decimal:1',
    ];

    public function unit()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'unit_id');
    }
    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
    public function shift()
    {
        return $this->belongsTo(\App\Models\Shift::class);
    }
    protected static function booted()
    {
        static::saving(function ($m) {
            $m->hm_delta = round(((float)$m->hm_end - (float)$m->hm_start), 1);
            if ($m->hm_delta < 0 || $m->hm_delta > 24) {
                $m->anomaly = true;
            }
        });
    }
}
