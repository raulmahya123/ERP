<?php

// app/Models/EmailVerificationCode.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class EmailVerificationCode extends Model
{
    protected $fillable = ['user_id','code','expires_at','attempts'];
    protected $casts = ['expires_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function isExpired(): bool { return now()->greaterThan($this->expires_at); }
}
