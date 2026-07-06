<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmployeeMovementApproval extends Model
{
    use HasUuids;
    protected $table = 'employee_movement_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'movement_request_id', 'approver_id', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function movementRequest()
    {
        return $this->belongsTo(EmployeeMovementRequest::class, 'movement_request_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
