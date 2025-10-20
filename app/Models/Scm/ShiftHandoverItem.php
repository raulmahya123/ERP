<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ShiftHandoverItem extends Model
{
    use HasUuids;

    protected $table = 'scm_shift_handover_items';
    protected $fillable = ['handover_id','pit_id','notes'];

    public function handover() { return $this->belongsTo(ShiftHandover::class, 'handover_id'); }
}
