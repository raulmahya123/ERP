<?php

namespace App\Models\Hse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HazardArea extends Model
{
    use HasUuids;
    protected $table = 'hazard_areas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'code',
        'name',
        'description',
        'location',
        'risk_level',
        'is_active',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
