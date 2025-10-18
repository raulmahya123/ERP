<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HazardReport extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'site_id','reporter_id','code','observed_at','location','category',
        'description','immediate_action','recommendation',
        'likelihood_initial','severity_initial','risk_initial',
        'likelihood_residual','severity_residual','risk_residual',
        'assignee_id','due_date','linked_incident_id','status',
        'verified_at','verified_by','verification_note',
        'tags','meta',
    ];

    protected $casts = [
        'observed_at' => 'datetime',
        'due_date'    => 'date',
        'verified_at' => 'datetime',
        'tags'        => 'array',
        'meta'        => 'array',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /** Relations */
    public function site()       { return $this->belongsTo(Site::class); }
    public function reporter()   { return $this->belongsTo(User::class, 'reporter_id'); }
    public function assignee()   { return $this->belongsTo(User::class, 'assignee_id'); }
    public function verifier()   { return $this->belongsTo(User::class, 'verified_by'); }
    public function incident()   { return $this->belongsTo(Incident::class, 'linked_incident_id'); }
    public function picas()      { return $this->hasMany(Pica::class, 'related_hazard_id'); }
    public function media()      { return $this->morphMany(MediaAttachment::class, 'attachable'); }
}
