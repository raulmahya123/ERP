<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class VhsSettlement extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'scm_vhs_settlements';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'settlement_number', 'purchase_order_id', 'total_amount', 'paid_amount', 'balance', 'settlement_date', 'status', 'notes'
    ];
    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'settlement_date' => 'date',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
