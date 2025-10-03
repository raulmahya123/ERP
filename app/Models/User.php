<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // relationship
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // helper: check role by key (e.g. 'manager')
    public function hasRole(string $roleKey): bool
    {
        return $this->role && $this->role->key === $roleKey;
    }

    public function hasAnyRole(array $keys): bool
    {
        return $this->role && in_array($this->role->key, $keys);
    }

    // === Accessors ===
    public function getRoleKeyAttribute(): ?string
    {
        $this->loadMissing('role'); // biar aman dari lazy loading
        return optional($this->role)->key;
    }

    public function getRoleNameAttribute(): ?string
    {
        $this->loadMissing('role');
        return optional($this->role)->name;
    }
}
