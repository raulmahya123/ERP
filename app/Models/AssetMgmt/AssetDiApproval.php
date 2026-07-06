<?php

namespace App\Models\AssetMgmt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetDiApproval extends Model
{
    use HasUuids;
    protected $table = 'asset_di_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'di_id', 'approver_id', 'status', 'notes', 'action_at'
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function di()
    {
        return $this->belongsTo(AssetDeliveryInstruction::class, 'di_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
