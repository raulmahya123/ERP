<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductionActual extends Model
{
    use HasUuids;
    protected $table = 'production_actuals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'shift_plan_id',
        'actual_date',
        'shift',
        'volume',
        'ob_volume',
        'waste_volume',
        'overburden_volume',
        'uom',
        'notes',
        'recorded_by',
    ];
    protected $casts = [
        'actual_date' => 'date',
        'volume' => 'decimal:2',
        'ob_volume' => 'decimal:2',
        'waste_volume' => 'decimal:2',
        'overburden_volume' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
