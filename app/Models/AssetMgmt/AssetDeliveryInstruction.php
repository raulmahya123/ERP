<?php

namespace App\Models\AssetMgmt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetDeliveryInstruction extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'asset_delivery_instructions';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'di_number', 'asset_id', 'delivery_date', 'from_location',
        'to_location', 'notes', 'status', 'requested_by'
    ];
    protected $casts = [
        'delivery_date' => 'date',
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
}
