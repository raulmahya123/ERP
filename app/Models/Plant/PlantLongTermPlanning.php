<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantLongTermPlanning extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'plant_long_term_plannings';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'asset_id', 'year', 'plan_type', 'description',
        'planned_date', 'estimated_cost', 'status'
    ];
    protected $casts = [
        'planned_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'year' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id');
    }
}
