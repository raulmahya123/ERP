<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Breakdown extends Model
{
    use HasUuids;

    protected $table = 'scm_breakdowns';

    protected $fillable = [
        'site_id',
        'unit_id',
        'category',
        'cause_code',
        'start_at',
        'end_at',
        'duration_hours',
        'notes',
        'client_uid',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Asset::class, 'unit_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }
}
