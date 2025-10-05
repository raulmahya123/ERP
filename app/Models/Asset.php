<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Auth;
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
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function category()
    {
        // entity guard agar join selalu aman
        return $this->belongsTo(MasterRecord::class, 'asset_category_id')
            ->where('entity', 'asset_categories');
    }

    public function costCenter()
    {
        return $this->belongsTo(MasterRecord::class, 'cost_center_id')
            ->where('entity', 'cost_centers');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function latestAssignment()
    {
        return $this->hasOne(AssetAssignment::class)->latestOfMany();
    }
    /* =========================
     |  Scopes
     |=========================*/
    /** Filter by site (ignore if null) */
    public function scopeForSite($q, ?string $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
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
            'active'   => 'bg-emerald-100 text-emerald-700',
            'repair'   => 'bg-yellow-100 text-yellow-700',
            'inactive' => 'bg-slate-100 text-slate-600',
            'sold', 'disposed' => 'bg-red-100 text-red-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }

    /* =========================
     |  Model Events
     |=========================*/
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            // auto set site_id dari session jika belum diisi
            if (empty($m->site_id)) {
                $sid = (string) (Session::get('site_id') ?? '');
                if ($sid !== '') $m->site_id = $sid;
            }

            // set created_by dari user login
            if (empty($m->created_by) && Auth::check()) {
                $m->created_by = Auth::id();
            }

            // normalisasi extra jadi JSON array bila string JSON valid
            if (is_string($m->extra)) {
                $trim = trim($m->extra);
                if ($trim !== '') {
                    try {
                        $m->extra = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable $e) { /* biarkan string — cast array akan handle */
                    }
                } else {
                    $m->extra = null;
                }
            }
        });

        static::updating(function (self $m) {
            if (is_string($m->extra)) {
                $trim = trim($m->extra);
                if ($trim !== '') {
                    try {
                        $m->extra = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable $e) { /* biarkan string */
                    }
                } else {
                    $m->extra = null;
                }
            }
        });
    }
}
