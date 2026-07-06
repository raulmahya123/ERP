<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialMaster extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'scm_material_masters';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'material_code', 'material_name', 'material_type', 'material_group', 'base_uom', 'weight', 'volume', 'description', 'is_active'
    ];
    protected $casts = [
        'weight' => 'decimal:2',
        'volume' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
