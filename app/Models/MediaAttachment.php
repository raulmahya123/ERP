<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MediaAttachment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'attachable_type','attachable_id','uploaded_by','path','disk','mime',
        'size_bytes','taken_at','caption','meta',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'meta'     => 'array',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /** Relations */
    public function attachable() { return $this->morphTo(); }
    public function uploader()   { return $this->belongsTo(User::class, 'uploaded_by'); }
}
