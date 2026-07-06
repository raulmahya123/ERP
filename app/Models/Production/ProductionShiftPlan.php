<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionShiftPlan extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'production_shift_plans';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'monthly_plan_id',
        'plan_date',
        'shift',
        'target_volume',
        'target_ob',
        'uom',
        'notes',
        'status',
        'created_by',
    ];
    protected $casts = [
        'plan_date' => 'date',
        'target_volume' => 'decimal:2',
        'target_ob' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
