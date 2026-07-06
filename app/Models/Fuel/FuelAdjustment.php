<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelAdjustment extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'fuel_adjustments';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'tank_id', 'adjustment_at', 'volume', 'adjustment_type',
        'reason', 'status', 'requested_by', 'approved_by', 'approved_at', 'approval_notes',
    ];

    protected $casts = [
        'adjustment_at' => 'datetime',
        'approved_at' => 'datetime',
        'volume' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function tank()
    {
        return $this->belongsTo(FuelTank::class, 'tank_id');
    }

    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function approvals()
    {
        return $this->hasMany(FuelAdjustmentApproval::class, 'adjustment_id');
    }
}
