<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorEvaluation extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'scm_vendor_evaluations';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'vendor_id', 'evaluation_period', 'quality_score', 'delivery_score', 'price_score', 'service_score', 'total_score', 'notes', 'status'
    ];
    protected $casts = [
        'quality_score' => 'decimal:2',
        'delivery_score' => 'decimal:2',
        'price_score' => 'decimal:2',
        'service_score' => 'decimal:2',
        'total_score' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\MasterRecord::class);
    }
}
