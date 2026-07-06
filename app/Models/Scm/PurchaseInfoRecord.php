<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInfoRecord extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'scm_purchase_info_records';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'material_id', 'vendor_id', 'info_category', 'price', 'currency', 'uom', 'min_order_qty', 'valid_from', 'valid_to', 'status'
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'min_order_qty' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function material()
    {
        return $this->belongsTo(MaterialMaster::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\MasterRecord::class);
    }
}
