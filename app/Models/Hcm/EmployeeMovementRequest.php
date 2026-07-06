<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeMovementRequest extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'employee_movement_requests';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'employee_id', 'movement_type', 'from_position', 'to_position',
        'from_department', 'to_department', 'from_location', 'to_location',
        'effective_date', 'reason', 'status', 'requested_by'
    ];
    protected $casts = [
        'effective_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }
}
