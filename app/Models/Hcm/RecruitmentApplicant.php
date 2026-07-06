<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RecruitmentApplicant extends Model
{
    use HasUuids;
    protected $table = 'recruitment_applicants';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'candidate_id', 'application_date', 'source', 'expected_salary', 'status', 'notes'
    ];
    protected $casts = [
        'application_date' => 'date',
        'expected_salary' => 'decimal:2',
    ];

    public function candidate()
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'candidate_id');
    }
}
