<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductionMonthlyClosing extends Model
{
    use HasUuids;
    protected $table = 'production_monthly_closings';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'year',
        'month',
        'closed_at',
        'is_unlocked',
        'closed_by',
    ];
    protected $casts = [
        'closed_at' => 'datetime',
        'is_unlocked' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
