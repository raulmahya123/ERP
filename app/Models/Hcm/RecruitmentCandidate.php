<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentCandidate extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'recruitment_candidates';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'candidate_number', 'full_name', 'email', 'phone', 'position_applied',
        'address', 'education', 'experience', 'status', 'notes', 'resume_file', 'created_by'
    ];

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
