<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class EnvironmentalSample extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const TYPE_AIR      = 'air';
    public const TYPE_EMISSION = 'emission';
    public const TYPE_NOISE    = 'noise';

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED  = 'verified';

    /** Jika kamu memakai enum native PHP/Laravel, ubah konstanta ini jadi enum. */
    public const TYPES   = [self::TYPE_AIR, self::TYPE_EMISSION, self::TYPE_NOISE];
    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_VERIFIED];

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'code', 'site_id', 'sampled_at', 'type', 'location',
        'parameter', 'value', 'unit', 'method', 'instrument',
        'limit_value', 'is_compliant', 'status', 'meta',
    ];

    protected $casts = [
        'sampled_at'   => 'datetime',
        'value'        => 'decimal:4',
        'limit_value'  => 'decimal:4',
        'is_compliant' => 'boolean',
        'meta'         => 'array',
        'code'         => 'string',
        'type'         => 'string',
        'status'       => 'string',
    ];

    /** ====== Relations ====== */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'attachable');
    }

    /** ====== Model Events (defaults + code generator) ====== */
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            // default status
            if (empty($model->status)) {
                $model->status = self::STATUS_DRAFT;
            }

            // default sampled_at
            if (empty($model->sampled_at)) {
                $model->sampled_at = now();
            }

            // generate code jika kosong
            if (empty($model->code)) {
                $siteCode = 'GEN';
                if (!empty($model->site_id)) {
                    $code = Site::query()->whereKey($model->site_id)->value('code');
                    if (is_string($code) && $code !== '') {
                        $siteCode = strtoupper($code);
                    }
                }
                $model->code = sprintf(
                    'ENV-%s-%s-%s',
                    $siteCode,
                    now()->format('Ymd'),
                    Str::upper(Str::random(6))
                );
            }
        });
    }

    /** ====== Query Scopes (buat controller lebih ringkas) ====== */
    public function scopeForSite($q, ?string $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeOfType($q, ?string $type)
    {
        return $type ? $q->where('type', $type) : $q;
    }

    public function scopeOfStatus($q, ?string $status)
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeBetweenDates($q, ?string $from, ?string $to)
    {
        if ($from) { $q->where('sampled_at', '>=', \Illuminate\Support\Carbon::parse($from)->startOfDay()); }
        if ($to)   { $q->where('sampled_at', '<=', \Illuminate\Support\Carbon::parse($to)->endOfDay()); }
        return $q;
    }

    public function scopeSearch($q, string $term = '')
    {
        $term = trim(mb_substr($term, 0, 60));
        if ($term === '') return $q;

        return $q->where(function ($w) use ($term) {
            $w->where('code', 'like', "%{$term}%")
              ->orWhere('parameter', 'like', "%{$term}%")
              ->orWhere('location', 'like', "%{$term}%")
              ->orWhere('method', 'like', "%{$term}%")
              ->orWhere('instrument', 'like', "%{$term}%");
        });
    }
}
