<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelConsume extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'fuel_consumes';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'tank_id', 'flow_meter_id', 'unit_id', 'operator_id',
        'consume_at', 'volume', 'fuel_type', 'meter_start', 'meter_end',
        'reference_no', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'consume_at' => 'datetime',
        'volume' => 'decimal:2',
        'meter_start' => 'decimal:2',
        'meter_end' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function tank()
    {
        return $this->belongsTo(FuelTank::class, 'tank_id');
    }

    public function flowMeter()
    {
        return $this->belongsTo(FuelFlowMeter::class, 'flow_meter_id');
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'unit_id');
    }

    public function operator()
    {
        return $this->belongsTo(\App\Models\User::class, 'operator_id');
    }
}
