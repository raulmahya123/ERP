<?php

namespace App\Models\Hse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseRtp extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'hse_rtp';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'hazard_report_id',
        'site_id',
        'rtp_number',
        'corrective_action',
        'preventive_action',
        'pic',
        'target_date',
        'completion_date',
        'status',
        'notes',
        'created_by',
    ];
    protected $casts = [
        'target_date' => 'date',
        'completion_date' => 'date',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function hazardReport()
    {
        return $this->belongsTo(\App\Models\HazardReport::class);
    }
}
