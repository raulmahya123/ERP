<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class KpiIndicator extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'site_id','date','type','name','value','unit','notes','meta',
    ];

    protected $casts = [
        'date'  => 'date',
        'value' => 'decimal:4',
        'meta'  => 'array',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /** Relations */
    public function site() { return $this->belongsTo(Site::class); }
}
