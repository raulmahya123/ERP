<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBenefitClaim extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'employee_benefit_claims';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'employee_id', 'benefit_id', 'claim_date', 'amount', 'description', 'status'
    ];
    protected $casts = [
        'claim_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }

    public function benefit()
    {
        return $this->belongsTo(EmployeeBenefit::class, 'benefit_id');
    }
}
