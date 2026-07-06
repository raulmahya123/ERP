<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelReceive extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'fuel_receives';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'tank_id', 'supplier', 'po_number', 'invoice_number',
        'receive_at', 'volume', 'fuel_type', 'price_per_unit', 'total_amount',
        'vehicle_number', 'driver_name', 'reference_no', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'receive_at' => 'datetime',
        'volume' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
