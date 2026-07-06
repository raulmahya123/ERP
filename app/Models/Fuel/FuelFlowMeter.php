<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelFlowMeter extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'fuel_flow_meters';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'code', 'name', 'tank_id', 'meter_reading',
        'uom', 'location', 'notes', 'is_active', 'created_by',
    ];

    protected $casts = [
        'meter_reading' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function tank()
    {
        return $this->belongsTo(FuelTank::class, 'tank_id');
    }
}
