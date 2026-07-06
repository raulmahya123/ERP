<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FuelInventoryBalance extends Model
{
    use HasUuids;

    protected $table = 'fuel_inventory_balances';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'tank_id', 'balance_date', 'opening_balance',
        'receive_qty', 'consume_qty', 'adjustment_qty',
        'closing_balance', 'fuel_type', 'notes',
    ];

    protected $casts = [
        'balance_date' => 'date',
        'opening_balance' => 'decimal:2',
        'receive_qty' => 'decimal:2',
        'consume_qty' => 'decimal:2',
        'adjustment_qty' => 'decimal:2',
        'closing_balance' => 'decimal:2',
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
