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

// === imports model yang dipakai ===
use App\Models\Role;
use App\Models\Division;
use App\Models\Site;
use App\Models\SiteConfig;
use App\Models\Payroal;

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

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** ikut terserialisasi ke json (berguna untuk UI dropdown, dll.) */
    protected $appends = ['display_label','role_key','role_name'];

    /* =========================
     | Relationships
     |=========================*/

    // 1:1 ke tabel payroal
    public function payroal()
    {
        return $this->hasOne(Payroal::class, 'user_id');
    }

    // alias relasi role (biar with('role') / whereHas('role') tetap bisa)
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function defaultSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'default_site_id');
    }

    /** Alias agar kode lama yg expect 'site()' tetap aman (mengarah ke default_site_id) */
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
            $qq->where('default_site_id', $siteId);

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
     | Helpers (AMAN, pakai accessor)
     |=========================*/

    public function hasRole(string $roleKey): bool
    {
        return $this->role_key === $roleKey;
    }

    public function hasAnyRole(array $keys): bool
    {
        return in_array($this->role_key, $keys, true);
    }

    public function isGM(): bool
    {
        return $this->role_key === 'gm';
    }

    /* =========================
     | Accessors (role aman utk string/relasi)
     |=========================*/

    /**
     * role_key:
     * - Jika kolom 'users.role' ada (string/enum), gunakan itu.
     * - Jika tidak ada, coba dari relasi roles->key.
     */
    public function getRoleKeyAttribute(): ?string
    {
        if (Schema::hasColumn('users', 'role')) {
            $val = $this->getAttribute('role');
            return is_string($val) && $val !== '' ? $val : null;
        }
        $this->loadMissing('role');
        return optional($this->role)->key;
    }

    /**
     * role_name:
     * - Jika ada role_key string, kembalikan label humanized.
     * - Jika tidak, fallback ke relasi roles->name.
     */
    public function getRoleNameAttribute(): ?string
    {
        if ($this->role_key) {
            return match (strtolower($this->role_key)) {
                'superadmin' => 'Super Admin',
                'admin'      => 'Admin',
                'hr'         => 'HR',
                'pelamar'    => 'Pelamar',
                'gm'         => 'General Manager',
                default      => ucwords(str_replace(['_','-'], ' ', $this->role_key)),
            };
        }
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

    /**
     * Fallback ke payroal untuk employee_code & photo bila kolom di users kosong.
     */
    public function getEmployeeCodeAttribute($value)
    {
        if ($value) return $value;
        $this->loadMissing('payroal');
        return optional($this->payroal)->employee_code;
    }

    public function getPhotoAttribute($value)
    {
        if ($value) return $value;
        $this->loadMissing('payroal');
        return optional($this->payroal)->photo;
    }

    /** Label siap pakai buat dropdown: "Nama — Kode/Email" */
    public function getDisplayLabelAttribute(): string
    {
        $name = $this->name ?: $this->email;
        $tag  = $this->employee_code ?: $this->email; // sudah fallback ke payroal
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
