<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'scm_purchase_orders';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'po_number', 'vendor_id', 'order_date', 'delivery_date', 'payment_terms', 'shipping_method', 'total_amount', 'currency', 'status', 'notes', 'approved_by', 'approved_at', 'created_by'
    ];
    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\MasterRecord::class);
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
