<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlantBreakdownStatus extends Model
{
    use HasUuids;
    protected $table = 'plant_breakdown_statuses';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'asset_id', 'breakdown_start', 'breakdown_end', 'breakdown_code',
        'description', 'status', 'root_cause', 'action_taken', 'reported_by'
    ];
    protected $casts = [
        'breakdown_start' => 'datetime',
        'breakdown_end' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'reported_by');
    }
}
