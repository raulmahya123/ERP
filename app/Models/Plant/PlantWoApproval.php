<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlantWoApproval extends Model
{
    use HasUuids;
    protected $table = 'plant_wo_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'wo_id', 'approver_id', 'approval_level', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function wo()
    {
        return $this->belongsTo(PlantWorkOrder::class, 'wo_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
