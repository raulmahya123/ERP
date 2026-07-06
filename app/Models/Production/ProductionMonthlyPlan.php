<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionMonthlyPlan extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'production_monthly_plans';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'plan_number',
        'year',
        'month',
        'target_volume',
        'uom',
        'notes',
        'status',
        'created_by',
    ];
    protected $casts = [
        'target_volume' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
