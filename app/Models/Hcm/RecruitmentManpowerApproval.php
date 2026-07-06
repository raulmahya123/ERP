<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RecruitmentManpowerApproval extends Model
{
    use HasUuids;
    protected $table = 'recruitment_manpower_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'manpower_request_id', 'approver_id', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function manpowerRequest()
    {
        return $this->belongsTo(RecruitmentManpowerRequest::class, 'manpower_request_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
