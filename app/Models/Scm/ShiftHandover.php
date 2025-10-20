<?php

// app/Models/Scm/ShiftHandover.php
namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ShiftHandover extends Model
{
    use HasUuids;

    protected $table = 'scm_shift_handovers';
    protected $fillable = [
        'site_id','handover_date','from_shift_id','to_shift_id','weather','issues','targets','notes','extra'
    ];
    protected $casts = ['handover_date'=>'date','extra'=>'array'];

    public function items() { return $this->hasMany(ShiftHandoverItem::class, 'handover_id'); }
}
