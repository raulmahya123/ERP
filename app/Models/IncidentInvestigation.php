<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class IncidentInvestigation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',                                // ← tambahkan ini
        'incident_id',
        'lead_investigator_id',
        'started_at',
        'completed_at',
        'method',
        'findings_summary',
        'root_cause',
        'corrective_actions',
        'status',
        'meta',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'meta'         => 'array',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /** Relations */
    public function incident()         { return $this->belongsTo(Incident::class); }
    public function leadInvestigator() { return $this->belongsTo(User::class, 'lead_investigator_id'); }
    public function media()            { return $this->morphMany(MediaAttachment::class, 'attachable'); }
}
