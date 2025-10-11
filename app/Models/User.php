<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'default_site_id',   // site default (single-site)
        'employee_code',     // dipakai di UI dropdown
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** aksesori yang ikut terserialisasi ke array/json (buat UI) */
    protected $appends = ['display_label'];

    /* =========================
     | Relationships
     |=========================*/
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function defaultSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'default_site_id');
    }

    /** Alias agar kode lama yg expect 'site()' tetap aman */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'default_site_id');
    }

    /** Multi-site (opsional) via pivot `site_user` */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_user', 'user_id', 'site_id')
                    ->withTimestamps();
    }

    /* =========================
     | Query Scopes
     |=========================*/
    /** Filter user pada site tertentu (default_site_id, plus pivot kalau ada) */
    public function scopeInSite($q, $siteId)
    {
        if (!$siteId) return $q;

        return $q->where(function ($qq) use ($siteId) {
            // single-site
            $qq->where('default_site_id', $siteId);

            // multi-site (hanya jika tabel pivot ada)
            if (Schema::hasTable('site_user')) {
                $qq->orWhereHas('sites', fn($s) => $s->where('sites.id', $siteId));
            }
        });
    }

    /** Cari berdasarkan nama / email / employee_code */
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        $like = '%'.str_replace(['%','_'], ['\%','\_'], $term).'%';
        return $q->where(function ($qq) use ($like) {
            $qq->where('name', 'like', $like)
               ->orWhere('email', 'like', $like)
               ->orWhere('employee_code', 'like', $like);
        });
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

    /** Label siap pakai buat dropdown: "Nama — Kode/Email" */
    public function getDisplayLabelAttribute(): string
    {
        $name = $this->name ?: $this->email;
        $tag  = $this->employee_code ?: $this->email;
        return trim($name.' — '.$tag);
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
                $siteId = SiteConfig::whereJsonContains('params->default_for_users', true)->value('site_id')
                       ?? Site::orderBy('name')->value('id');

                if ($siteId) {
                    $user->default_site_id = $siteId;
                }
            } catch (\Throwable $e) {
                // Diamkan jika tabel belum ada saat early migration/seed
            }
        });
    }
}
