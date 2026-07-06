<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FuelTankHistory extends Model
{
    use HasUuids;

    protected $table = 'fuel_tank_histories';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'tank_id', 'transaction_type', 'reference_type', 'reference_id',
        'volume_in', 'volume_out', 'balance_before', 'balance_after',
        'transaction_at', 'description', 'created_by',
    ];

    protected $casts = [
        'transaction_at' => 'datetime',
        'volume_in' => 'decimal:2',
        'volume_out' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
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
