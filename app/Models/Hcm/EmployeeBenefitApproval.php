<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmployeeBenefitApproval extends Model
{
    use HasUuids;
    protected $table = 'employee_benefit_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'claim_id', 'approver_id', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function claim()
    {
        return $this->belongsTo(EmployeeBenefitClaim::class, 'claim_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
