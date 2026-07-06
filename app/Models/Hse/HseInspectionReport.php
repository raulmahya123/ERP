<?php

namespace App\Models\Hse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseInspectionReport extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'hse_inspection_reports';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'report_number',
        'inspection_type',
        'location',
        'inspection_date',
        'findings',
        'recommendations',
        'status',
        'inspector_id',
        'verified_by',
        'verified_at',
    ];
    protected $casts = [
        'inspection_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
