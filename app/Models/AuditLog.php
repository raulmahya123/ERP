<?php

// app/Models/AuditLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id',
        'changes', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
