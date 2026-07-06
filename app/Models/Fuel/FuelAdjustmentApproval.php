<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FuelAdjustmentApproval extends Model
{
    use HasUuids;

    protected $table = 'fuel_adjustment_approvals';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'adjustment_id', 'approver_id', 'status', 'notes', 'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function adjustment()
    {
        return $this->belongsTo(FuelAdjustment::class, 'adjustment_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
