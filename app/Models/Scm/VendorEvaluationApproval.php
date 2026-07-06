<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VendorEvaluationApproval extends Model
{
    use HasUuids;
    protected $table = 'scm_vendor_evaluation_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'evaluation_id', 'approver_id', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
