<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'scm_reservations';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'reservation_number', 'material_id', 'quantity', 'uom', 'reservation_type', 'movement_type', 'notes', 'status', 'requested_by', 'approved_by', 'approved_at'
    ];
    protected $casts = [
        'quantity' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function material()
    {
        return $this->belongsTo(MaterialMaster::class);
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
