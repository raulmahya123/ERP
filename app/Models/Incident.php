<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Incident extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'site_id','reporter_id','code','occurred_at','location',
        'category','severity','description','status','tags','meta',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'tags'        => 'array',
        'meta'        => 'array',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /** Relations */
    public function site()        { return $this->belongsTo(Site::class); }
    public function reporter()    { return $this->belongsTo(User::class, 'reporter_id'); }
    public function investigations(){ return $this->hasMany(IncidentInvestigation::class); }
    public function picas()       { return $this->hasMany(Pica::class, 'related_incident_id'); }
    public function hazards()     { return $this->hasMany(HazardReport::class, 'linked_incident_id'); }
    public function media()       { return $this->morphMany(MediaAttachment::class, 'attachable'); }
}
