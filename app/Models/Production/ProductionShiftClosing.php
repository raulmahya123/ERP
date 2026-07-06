<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductionShiftClosing extends Model
{
    use HasUuids;
    protected $table = 'production_shift_closings';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'close_date',
        'shift',
        'closed_at',
        'is_unlocked',
        'unlocked_at',
        'closed_by',
        'unlocked_by',
        'notes',
    ];
    protected $casts = [
        'close_date' => 'date',
        'closed_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'is_unlocked' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
