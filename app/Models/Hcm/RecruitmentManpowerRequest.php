<?php

namespace App\Models\Hcm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentManpowerRequest extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'recruitment_manpower_requests';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'request_number', 'position', 'quantity', 'required_date',
        'justification', 'status', 'requested_by'
    ];
    protected $casts = [
        'required_date' => 'date',
        'quantity' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }
}
