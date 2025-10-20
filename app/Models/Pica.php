<?php

namespace App\Models;

use App\Enums\PicaStatus; // ⬅️ enum di bawah
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Pica extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /** Whitelist kolom yang boleh diisi via mass assignment */
    protected $fillable = [
        'code', 'reference', // ⬅️ penting utk controller kamu
        'related_incident_id','related_hazard_id',
        'title','problem_statement','root_cause','preventive_action',
        'owner_id','due_date','status','closed_at',
        'effectiveness_review','meta',
    ];

    /** Cast & defaults */
    protected $casts = [
        'due_date'  => 'date',
        'closed_at' => 'datetime',
        'meta'      => 'array',
        'status'    => PicaStatus::class, // kalau belum pakai enum, ganti 'string'
    ];

    protected $attributes = [
        'status' => 'open',
    ];

    public $incrementing = false;
    protected $keyType   = 'string';

    /** ========= Relations ========= */
    public function incident()  { return $this->belongsTo(Incident::class, 'related_incident_id'); }
    public function hazard()    { return $this->belongsTo(HazardReport::class, 'related_hazard_id'); }
    public function owner()     { return $this->belongsTo(User::class, 'owner_id'); }
    public function media()     { return $this->morphMany(MediaAttachment::class, 'attachable'); }

    /** ========= Scopes (ORM Helpers) ========= */

    /** Filter berdasarkan site via relasi incident/hazard */
    public function scopeForSite(Builder $q, ?string $siteId): Builder
    {
        if (!$siteId) return $q;
        return $q->where(function ($w) use ($siteId) {
            $w->whereHas('incident', fn($i) => $i->where('site_id', $siteId))
              ->orWhereHas('hazard',   fn($h) => $h->where('site_id', $siteId));
        });
    }

    /** Pencarian ringan di beberapa kolom + kode relasi */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        // Sanitasi ringan (opsional, query builder sendiri sdh parameterized)
        $safe = preg_replace('/[^\p{L}\p{N}\s\-\_\.\#]/u', '', $term) ?? '';
        $like = "%{$safe}%";

        return $q->where(function ($w) use ($like) {
            $w->where('code','like',$like)
              ->orWhere('reference','like',$like)
              ->orWhere('title','like',$like)
              ->orWhere('problem_statement','like',$like)
              ->orWhere('root_cause','like',$like)
              ->orWhere('preventive_action','like',$like)
              ->orWhereHas('incident', fn($i) => $i->where('code','like',$like))
              ->orWhereHas('hazard',   fn($h) => $h->where('code','like',$like));
        });
    }

    /** Filter status (string atau enum) */
    public function scopeStatus(Builder $q, $status): Builder
    {
        if (!$status) return $q;
        $value = $status instanceof PicaStatus ? $status->value : strtolower((string) $status);
        return $q->where('status', $value);
    }

    /** Hanya yang dimiliki owner tertentu */
    public function scopeOwnedBy(Builder $q, ?int $userId): Builder
    {
        return $userId ? $q->where('owner_id', $userId) : $q;
    }

    /** Jatuh tempo antara tanggal */
    public function scopeDueBetween(Builder $q, $from, $to): Builder
    {
        if ($from) $q->whereDate('due_date', '>=', $from);
        if ($to)   $q->whereDate('due_date', '<=', $to);
        return $q;
    }

    /** Overdue (belum closed & due_date < today) */
    public function scopeOverdue(Builder $q): Builder
    {
        return $q->whereNull('closed_at')
                 ->whereDate('due_date', '<', now()->toDateString());
    }

    /** Effective saja */
    public function scopeEffective(Builder $q): Builder
    {
        return $q->where('status', PicaStatus::EFFECTIVE->value);
    }

    /** ========= Accessors / Mutators ========= */

    /** Apakah overdue? (boolean) */
    public function getIsOverdueAttribute(): bool
    {
        return is_null($this->closed_at)
            && !is_null($this->due_date)
            && $this->due_date->isBefore(now()->startOfDay());
    }

    /** Site id gabungan dari incident/hazard (berguna untuk badge/label) */
    public function getSiteIdAttribute(): ?string
    {
        return $this->incident->site_id ?? $this->hazard->site_id ?? null;
    }

    /** Rapikan title */
    public function setTitleAttribute($v): void
    {
        $this->attributes['title'] = trim((string) $v);
    }

    /** Pastikan meta selalu array ter-encode dengan benar */
    public function setMetaAttribute($v): void
    {
        $this->attributes['meta'] = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
    }

    /** ========= Model Hooks ========= */

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            // Safety net: generate code jika belum ada (controller kamu sudah handle, ini fallback)
            if (empty($model->code)) {
                $model->code = self::makePicaCode('PCA', $model->site_id ? self::siteCodeFromId($model->site_id) : 'GEN');
            }
            // Default status open kalau belum diisi
            if (empty($model->status)) {
                $model->status = PicaStatus::OPEN->value;
            }
        });
    }

    /** Utility kecil di dalam model (tanpa query berat) */
    protected static function makePicaCode(string $prefix, ?string $siteCode = 'GEN'): string
    {
        $siteCode = $siteCode ? strtoupper($siteCode) : 'GEN';
        return sprintf('%s-%s-%s-%s',
            $prefix,
            $siteCode,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }

    protected static function siteCodeFromId(?string $siteId): string
    {
        if (!$siteId) return 'GEN';
        try {
            return strtoupper((string) Site::query()->whereKey($siteId)->value('code') ?? 'GEN');
        } catch (\Throwable $e) {
            return 'GEN';
        }
    }
}
