<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pica extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'related_incident_id','related_hazard_id','title','problem_statement',
        'root_cause','preventive_action','owner_id','due_date','status',
        'closed_at','effectiveness_review','meta',
    ];

    protected $casts = [
        'due_date' => 'date',
        'closed_at'=> 'datetime',
        'meta'     => 'array',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /** Relations */
    public function incident()  { return $this->belongsTo(Incident::class, 'related_incident_id'); }
    public function hazard()    { return $this->belongsTo(HazardReport::class, 'related_hazard_id'); }
    public function owner()     { return $this->belongsTo(User::class, 'owner_id'); }
    public function media()     { return $this->morphMany(MediaAttachment::class, 'attachable'); }
}
