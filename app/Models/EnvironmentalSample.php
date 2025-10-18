<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EnvironmentalSample extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'site_id','sampled_at','type','location','parameter','value','unit',
        'method','instrument','limit_value','is_compliant','meta',
    ];

    protected $casts = [
        'sampled_at'   => 'datetime',
        'value'        => 'decimal:4',
        'limit_value'  => 'decimal:4',
        'is_compliant' => 'boolean',
        'meta'         => 'array',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /** Relations */
    public function site()  { return $this->belongsTo(Site::class); }
    public function media() { return $this->morphMany(MediaAttachment::class, 'attachable'); }
}
