<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'scm_purchase_requests';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'site_id', 'pr_number', 'request_date', 'description', 'status', 'requested_by'
    ];
    protected $casts = [
        'request_date' => 'date',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }
}
