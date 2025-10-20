<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FuelLog extends Model
{
    use HasUuids;
    protected $table = 'scm_fuel_logs';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'site_id',
        'unit_id',
        'operator_id',
        'dispensed_at',
        'fuel_type',
        'liter',
        'dispenser_id',
        'receipt_no',
        'client_uid',
        'created_by'
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
        'liter'        => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'unit_id');
    }
    public function operator()
    {
        return $this->belongsTo(\App\Models\User::class, 'operator_id');
    }
    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
