<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

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
        'default_site_id', // <<< penting
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* =========================
     | Relationships
     |=========================*/
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function defaultSite()
    {
        return $this->belongsTo(\App\Models\Site::class, 'default_site_id');
    }

    /* =========================
     | Query Scopes
     |=========================*/
    public function scopeInSite($q, $siteId)
    {
        return $q->where('default_site_id', $siteId);
    }

    /* =========================
     | Helpers
     |=========================*/
    public function hasRole(string $roleKey): bool
    {
        return $this->role && $this->role->key === $roleKey;
    }

    public function hasAnyRole(array $keys): bool
    {
        return $this->role && in_array($this->role->key, $keys, true);
    }

    public function isGM(): bool
    {
        return optional($this->role)->key === 'gm';
    }

    /* =========================
     | Accessors
     |=========================*/
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

    public function getDefaultSiteNameAttribute(): ?string
    {
        $this->loadMissing('defaultSite');
        return optional($this->defaultSite)->name;
    }

    /* =========================
     | Mutators (email & password)
     |=========================*/
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_string($value) ? mb_strtolower($value) : $value,
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (!$value) return $value;
                // Hindari rehash jika sudah ter-hash
                return Hash::needsRehash($value) ? Hash::make($value) : $value;
            }
        );
    }

    /* =========================
     | Boot hooks: auto-isi default_site_id
     |=========================*/
    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (filled($user->default_site_id)) return;

            try {
                // Ambil dari SiteConfig.params->default_for_users = true
                $siteId = \App\Models\SiteConfig::whereJsonContains('params->default_for_users', true)->value('site_id')
                       ?? \App\Models\Site::orderBy('name')->value('id');

                if ($siteId) {
                    $user->default_site_id = $siteId;
                }
            } catch (\Throwable $e) {
                // Diamkan jika tabel belum ada saat early migration/seed
            }
        });
    }
}
