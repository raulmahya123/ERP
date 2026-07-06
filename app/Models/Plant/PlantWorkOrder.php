<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantWorkOrder extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'plant_work_orders';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'wo_number', 'asset_id', 'wo_type', 'priority', 'description',
        'planned_start', 'planned_end', 'actual_start', 'actual_end', 'status',
        'notes', 'requested_by', 'assigned_to', 'approved_by', 'approved_at'
    ];
    protected $casts = [
        'planned_start' => 'date',
        'planned_end' => 'date',
        'actual_start' => 'date',
        'actual_end' => 'date',
        'approved_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
