<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductionReconcile extends Model
{
    use HasUuids;
    protected $table = 'production_reconciles';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id',
        'reconcile_date',
        'plan_volume',
        'actual_volume',
        'variance',
        'variance_pct',
        'notes',
        'reconciled_by',
    ];
    protected $casts = [
        'reconcile_date' => 'date',
        'plan_volume' => 'decimal:2',
        'actual_volume' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_pct' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
