<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PurchaseOrderItem extends Model
{
    use HasUuids;
    protected $table = 'scm_purchase_order_items';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'purchase_order_id', 'material_id', 'quantity', 'unit_price', 'total_price', 'uom', 'delivery_date', 'notes'
    ];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'delivery_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function material()
    {
        return $this->belongsTo(MaterialMaster::class);
    }
}
