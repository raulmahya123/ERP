<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FuelStockCheck extends Model
{
    use HasUuids;

    protected $table = 'fuel_stock_checks';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'tank_id', 'check_at', 'book_volume', 'actual_volume',
        'difference', 'uom', 'notes', 'checked_by',
    ];

    protected $casts = [
        'check_at' => 'datetime',
        'book_volume' => 'decimal:2',
        'actual_volume' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function tank()
    {
        return $this->belongsTo(FuelTank::class, 'tank_id');
    }

    public function checker()
    {
        return $this->belongsTo(\App\Models\User::class, 'checked_by');
    }
}
