<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantStandardJob extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'plant_standard_jobs';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'job_code', 'job_name', 'equipment_class_id', 'description',
        'estimated_duration', 'duration_uom', 'maintenance_type', 'safety_notes', 'is_active'
    ];
    protected $casts = [
        'estimated_duration' => 'integer',
        'is_active' => 'boolean',
    ];
}
