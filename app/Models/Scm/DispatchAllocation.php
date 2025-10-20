<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DispatchAllocation extends Model
{
    use HasUuids;

    protected $table = 'scm_dispatch_allocations';
    protected $fillable = [
        'site_id',
        'work_date',
        'shift_id',
        'pit_id',
        'asset_id',
        'operator_id',
        'route_id',
        'planned_start',
        'planned_end',
        'status',
        'notes',
        'extra'
    ];
    protected $casts = [
        'work_date' => 'date',
        'planned_start' => 'datetime:H:i',
        'planned_end' => 'datetime:H:i',
        'extra' => 'array'
    ];
}
