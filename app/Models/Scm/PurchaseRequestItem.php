<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PurchaseRequestItem extends Model
{
    use HasUuids;
    protected $table = 'scm_purchase_request_items';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'purchase_request_id', 'material_id', 'quantity', 'uom', 'required_date', 'notes'
    ];
    protected $casts = [
        'quantity' => 'decimal:2',
        'required_date' => 'date',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function material()
    {
        return $this->belongsTo(MaterialMaster::class);
    }
}
