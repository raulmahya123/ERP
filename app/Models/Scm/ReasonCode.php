<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReasonCode extends Model
{
    use HasUuids;

    protected $table = 'scm_reason_codes';
    protected $fillable = [
        'site_id',
        'code',
        'name',
        'category',
        'is_downtime',
        'is_billable',
        'active',
        'extra'
    ];
    protected $casts = [
        'is_downtime' => 'boolean',
        'is_billable' => 'boolean',
        'active' => 'boolean',
        'extra' => 'array'
    ];
}
