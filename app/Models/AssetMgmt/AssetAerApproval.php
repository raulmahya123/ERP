<?php

namespace App\Models\AssetMgmt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetAerApproval extends Model
{
    use HasUuids;
    protected $table = 'asset_aer_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'aer_id', 'approver_id', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function aer()
    {
        return $this->belongsTo(AssetAerMaster::class, 'aer_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
