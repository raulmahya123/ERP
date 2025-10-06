<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Asset extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'commissioned_at' => 'date',
        'acq_date'        => 'date',
        'acq_cost'        => 'decimal:2',
        'extra'           => 'array',
    ];

    /* =========================
     |  Relationships
     |=========================*/

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function category(): BelongsTo
    {
        // entity guard agar join selalu aman
        return $this->belongsTo(MasterRecord::class, 'asset_category_id')
            ->where('entity', 'asset_categories');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(MasterRecord::class, 'cost_center_id')
            ->where('entity', 'cost_centers');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function latestAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->latestOfMany();
    }

    /* =========================
     |  Scopes
     |=========================*/

    /** Filter by site (abaikan jika null) */
    public function scopeForSite($q, ?string $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    /** Pakai site dari session (kalau ada) */
    public function scopeForCurrentSite($q)
    {
        if ($sid = session('site_id')) {
            $q->where('site_id', $sid);
        }
        return $q;
    }

    /** Quick search by common columns */
    public function scopeSearch($q, ?string $term)
    {
        $s = trim((string) $term);
        if ($s === '') return $q;

        return $q->where(function ($w) use ($s) {
            $w->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%")
                ->orWhere('serial_no', 'like', "%{$s}%")
                ->orWhere('plate_no', 'like', "%{$s}%");
        });
    }

    /** Common status filter */
    public function scopeStatus($q, ?string $status)
    {
        return $status ? $q->where('status', $status) : $q;
    }

    /* =========================
     |  Accessors / Helpers
     |=========================*/

    public function getLabelAttribute(): string
    {
        $code = $this->code ? " [{$this->code}]" : '';
        return "{$this->name}{$code}";
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active'             => 'bg-emerald-100 text-emerald-700',
            'repair'             => 'bg-yellow-100 text-yellow-700',
            'inactive'           => 'bg-slate-100 text-slate-600',
            'sold', 'disposed'   => 'bg-red-100 text-red-700',
            default              => 'bg-slate-100 text-slate-600',
        };
    }

    /** Untuk UI: "CODE — Name" site aktif asset */
    public function getSiteCodeNameAttribute(): ?string
    {
        return $this->site ? ($this->site->code . ' — ' . $this->site->name) : null;
    }

    /** Cek role user GM */
    protected static function userIsGM(?User $user): bool
    {
        if (!$user) return false;

        $raw = $user->role->key
            ?? $user->role->slug
            ?? $user->role->name
            ?? (is_string($user->role ?? null) ? $user->role : '')
            ?? '';

        $norm = mb_strtolower(trim(str_replace(['_', '-'], ' ', $raw)));

        return in_array($norm, ['gm', 'general manager', 'generalmanager'], true);
    }

    /* =========================
     |  Model Events
     |=========================*/

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            // Auto set site dari session bila belum diisi
            if (empty($m->site_id)) {
                $sid = (string) (Session::get('site_id') ?? '');
                if ($sid !== '') $m->site_id = $sid;
            }

            // Non-GM tidak boleh memaksa site_id lain selain session
            $user = Auth::user();
            if (!static::userIsGM($user) && Session::has('site_id')) {
                $m->site_id = Session::get('site_id');
            }

            // set created_by dari user login
            if (empty($m->created_by) && Auth::check()) {
                $m->created_by = Auth::id();
            }

            // normalisasi extra jadi array bila string JSON valid
            if (is_string($m->extra)) {
                $trim = trim($m->extra);
                if ($trim !== '') {
                    try {
                        $m->extra = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable $e) {
                        // biarkan string — cast array akan handle
                    }
                } else {
                    $m->extra = null;
                }
            }
        });

        static::updating(function (self $m) {
            // Non-GM dilarang ubah site_id
            $user = Auth::user();
            if (!static::userIsGM($user) && $m->isDirty('site_id')) {
                $m->site_id = $m->getOriginal('site_id');
            }

            // normalisasi extra
            if (is_string($m->extra)) {
                $trim = trim($m->extra);
                if ($trim !== '') {
                    try {
                        $m->extra = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable $e) {
                        // biarkan string
                    }
                } else {
                    $m->extra = null;
                }
            }
        });
    }

    /* =========================
     |  Mutasi Penempatan / Assignment
     |=========================*/

    /**
     * Catat penempatan asset (mutasi site / assignment user) + sinkronkan kolom di assets.
     *
     * @param  string|null                         $toSiteId     UUID site tujuan (null = tidak pindah site)
     * @param  string|null                         $toUserId     UUID user tujuan (null = unassign)
     * @param  string|null                         $note         Catatan
     * @param  string|\DateTimeInterface|null      $effectiveAt  Waktu efektif (default: now)
     * @return \App\Models\AssetAssignment
     */
    public function assignTo(?string $toSiteId = null, ?string $toUserId = null, ?string $note = null, $effectiveAt = null): AssetAssignment
    {
        $user = Auth::user();
        $isGM = static::userIsGM($user);

        // Non-GM hanya boleh assign dalam site yang sama dengan session
        if (!$isGM && $toSiteId && Session::has('site_id') && $toSiteId !== Session::get('site_id')) {
            abort(403, 'Tidak boleh memindahkan asset ke site lain untuk role kamu.');
        }

        $effectiveAt = $effectiveAt ? Carbon::parse($effectiveAt) : now();

        return DB::transaction(function () use ($toSiteId, $toUserId, $note, $effectiveAt, $user) {
            /** @var self $asset */
            $asset = $this->fresh(['site']);

            $fromSiteId = $asset->site_id;
            $fromUserId = $asset->assigned_to_user_id ?? null;

            // Buat record assignment
            /** @var \App\Models\AssetAssignment $assignment */
            $assignment = $this->assignments()->create([
                'from_site_id' => $fromSiteId,
                'to_site_id'   => $toSiteId ?: $fromSiteId,
                'from_user_id' => $fromUserId,
                'to_user_id'   => $toUserId,
                'note'         => $note,
                'effective_at' => $effectiveAt,
                'created_by'   => $user?->id,
            ]);

            // Sinkronkan kolom di assets (site_id & assigned_to_user_id)
            // Catatan: kalau $toSiteId null, artinya hanya ganti user; site_id tetap.
            $asset->site_id = $toSiteId ?: $fromSiteId;
            $asset->assigned_to_user_id = $toUserId; // boleh null (unassign)
            $asset->save();

            return $assignment;
        });
    }
}
