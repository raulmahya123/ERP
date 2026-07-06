<?php

namespace App\Models\AssetMgmt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetArrApproval extends Model
{
    use HasUuids;
    protected $table = 'asset_arr_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'arr_id', 'approver_id', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function arr()
    {
        return $this->belongsTo(AssetArrMaster::class, 'arr_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
