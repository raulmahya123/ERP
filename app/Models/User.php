<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\HasUuid;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'division_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // === Relationships ===
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    // === Helpers ===
    public function hasRole(string $roleKey): bool
    {
        return $this->role && $this->role->key === $roleKey;
    }

    public function hasAnyRole(array $keys): bool
    {
        return $this->role && in_array($this->role->key, $keys, true);
    }

    // === Accessors ===
    public function getRoleKeyAttribute(): ?string
    {
        $this->loadMissing('role');
        return optional($this->role)->key;
    }

    public function getRoleNameAttribute(): ?string
    {
        $this->loadMissing('role');
        return optional($this->role)->name;
    }

    public function getDivisionNameAttribute(): ?string
    {
        $this->loadMissing('division');
        return optional($this->division)->name;
    }

    public function isGM(): bool
    {
        return optional($this->role)->key === 'gm';
    }
    public function defaultSite()
    {
        return $this->belongsTo(\App\Models\Site::class, 'default_site_id');
    }
}
