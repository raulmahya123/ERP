<?php

namespace App\Models\Plant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlantPicklist extends Model
{
    use HasUuids;
    protected $table = 'plant_picklists';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'wo_id', 'material_id', 'quantity_required',
        'quantity_issued', 'uom', 'notes', 'status'
    ];
    protected $casts = [
        'quantity_required' => 'decimal:2',
        'quantity_issued' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }

    public function wo()
    {
        return $this->belongsTo(PlantWorkOrder::class, 'wo_id');
    }
}
