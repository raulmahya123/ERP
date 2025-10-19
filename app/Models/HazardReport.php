<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HazardReport extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /** Status whitelist */
    public const ST_REPORTED  = 'reported';
    public const ST_ASSIGNED  = 'assigned';
    public const ST_MITIGATED = 'mitigated';
    public const ST_VERIFIED  = 'verified';
    public const ST_CLOSED    = 'closed';

    /** Mass assignment */
    protected $fillable = [
        'site_id','reporter_id','code','observed_at','location','category',
        'description','immediate_action','recommendation',
        'likelihood_initial','severity_initial','risk_initial',
        'likelihood_residual','severity_residual','risk_residual',
        'assignee_id','due_date','linked_incident_id','status',
        'verified_at','verified_by','verification_note',
        'tags','meta',
    ];

    /** Default values (DB juga punya default status, tapi aman di sisi app) */
    protected $attributes = [
        'status' => self::ST_REPORTED,
    ];

    /** Casts */
    protected $casts = [
        'observed_at' => 'datetime',
        'due_date'    => 'date',
        'verified_at' => 'datetime',
        'tags'        => 'array',
        'meta'        => 'array',

        // pastikan angka jadi int (bukan string) ketika keluar dari model
        'likelihood_initial'   => 'int',
        'severity_initial'     => 'int',
        'risk_initial'         => 'int',
        'likelihood_residual'  => 'int',
        'severity_residual'    => 'int',
        'risk_residual'        => 'int',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /* ========= Relationships ========= */

    public function site()     { return $this->belongsTo(Site::class); }
    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }
    public function incident() { return $this->belongsTo(Incident::class, 'linked_incident_id'); }
    public function picas()    { return $this->hasMany(Pica::class, 'related_hazard_id'); }
    public function media()    { return $this->morphMany(MediaAttachment::class, 'attachable'); }

    /* ========= Query Scopes (ORM-friendly filtering) ========= */

    public function scopeInSite($q, ?string $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        $like = "%{$term}%";
        return $q->where(function ($w) use ($like) {
            $w->where('code', 'like', $like)
              ->orWhere('category', 'like', $like)
              ->orWhere('location', 'like', $like)
              ->orWhere('description', 'like', $like);
        });
    }

    public function scopeStatus($q, ?string $status)
    {
        if (!$status) return $q;
        $allowed = [
            self::ST_REPORTED, self::ST_ASSIGNED, self::ST_MITIGATED,
            self::ST_VERIFIED, self::ST_CLOSED,
        ];
        return in_array($status, $allowed, true) ? $q->where('status', $status) : $q;
    }

    public function scopeObservedBetween($q, $from, $to)
    {
        if ($from) $q->where('observed_at', '>=', to_datetime($from)->startOfDay());
        if ($to)   $q->where('observed_at', '<=', to_datetime($to)->endOfDay());
        return $q;
    }

    public function scopeSeverityRange($q, ?int $min = null, ?int $max = null)
    {
        if ($min !== null) $q->where('severity_initial', '>=', $min);
        if ($max !== null) $q->where('severity_initial', '<=', $max);
        return $q;
    }

    /* ========= Status helpers ========= */

    public function isReported(): bool  { return $this->status === self::ST_REPORTED; }
    public function isAssigned(): bool  { return $this->status === self::ST_ASSIGNED; }
    public function isMitigated(): bool { return $this->status === self::ST_MITIGATED; }
    public function isVerified(): bool  { return $this->status === self::ST_VERIFIED; }
    public function isClosed(): bool    { return $this->status === self::ST_CLOSED; }

    /* ========= Events: auto-calc risk ========= */

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            // Normalisasi angka ke int >= 0
            foreach ([
                'likelihood_initial','severity_initial',
                'likelihood_residual','severity_residual',
            ] as $k) {
                if ($m->{$k} !== null) {
                    $m->{$k} = max(0, (int) $m->{$k});
                }
            }

            // Hitung risk_initial jika L & S tersedia
            if ($m->likelihood_initial && $m->severity_initial) {
                $m->risk_initial = $m->likelihood_initial * $m->severity_initial;
            } elseif ($m->isDirty(['likelihood_initial','severity_initial'])) {
                // kalau salah satunya kosong, kosongkan risk (hindari angka basi)
                $m->risk_initial = null;
            }

            // Hitung risk_residual jika L & S tersedia
            if ($m->likelihood_residual && $m->severity_residual) {
                $m->risk_residual = $m->likelihood_residual * $m->severity_residual;
            } elseif ($m->isDirty(['likelihood_residual','severity_residual'])) {
                $m->risk_residual = null;
            }
        });
    }
}

/**
 * Helper kecil: terima string|Carbon|null -> Carbon
 * (Bisa taruh di helper global project kamu)
 */
if (!function_exists('to_datetime')) {
    function to_datetime($value): \Illuminate\Support\Carbon
    {
        if ($value instanceof \Illuminate\Support\Carbon) return $value;
        try {
            return \Illuminate\Support\Carbon::parse($value ?? 'now');
        } catch (\Throwable) {
            return now();
        }
    }
}
