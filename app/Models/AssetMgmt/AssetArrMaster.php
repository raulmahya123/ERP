<?php

namespace App\Models\AssetMgmt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetArrMaster extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'asset_arr_masters';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'arr_number', 'asset_id', 'request_date', 'arr_type',
        'reason', 'status', 'notes', 'requested_by'
    ];
    protected $casts = [
        'request_date' => 'date',
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
