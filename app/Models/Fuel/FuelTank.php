<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTank extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'fuel_tanks';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'code', 'name', 'fuel_type', 'capacity',
        'current_volume', 'location', 'notes', 'is_active', 'created_by',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'current_volume' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function flowMeters()
    {
        return $this->hasMany(FuelFlowMeter::class, 'tank_id');
    }

    public function histories()
    {
        return $this->hasMany(FuelTankHistory::class, 'tank_id');
    }
}
