<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantStrategiTask extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'plant_strategi_tasks';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'task_code', 'task_name', 'task_type', 'frequency',
        'interval_value', 'interval_uom', 'description', 'is_active'
    ];
    protected $casts = [
        'interval_value' => 'integer',
        'is_active' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }
}
