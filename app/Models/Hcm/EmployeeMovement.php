<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmployeeMovement extends Model
{
    use HasUuids;
    protected $table = 'employee_movements';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'request_id', 'employee_id', 'movement_type', 'old_data', 'new_data',
        'effective_date', 'approved_by', 'executed_at'
    ];
    protected $casts = [
        'effective_date' => 'date',
        'executed_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(EmployeeMovementRequest::class, 'request_id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
