<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class KpiIndicator extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /** Mass-assignment whitelist (security) */
    protected $fillable = [
        'site_id',
        'definition_id',   // <— tambahkan
        'date',
        'type',            // legacy
        'name',            // legacy
        'value',
        'unit',
        'notes',
        'meta',
    ];

    /** Casts */
    protected $casts = [
        'date'  => 'date',
        'value' => 'decimal:4',
        'meta'  => 'array',
    ];

    /** PK as UUID string */
    public $incrementing = false;
    protected $keyType   = 'string';

    /* ===========================
     |  Relationships
     |===========================*/
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function definition()
    {
        return $this->belongsTo(KpiDefinition::class, 'definition_id');
    }

    /* ===========================
     |  Scopes (opsional, bantu query)
     |===========================*/
    public function scopeForSite($q, ?string $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeBetweenMonths($q, ?Carbon $from, ?Carbon $to)
    {
        if ($from) $q->where('date', '>=', $from->copy()->startOfMonth());
        if ($to)   $q->where('date', '<=', $to->copy()->startOfMonth());
        return $q;
    }

    public function scopeOfType($q, ?string $type)
    {
        return $type ? $q->where('type', $type) : $q;
    }

    public function scopeWithDefCode($q, ?string $code)
    {
        if (!$code) return $q;
        return $q->whereHas('definition', fn($w) => $w->whereRaw('UPPER(code) = ?', [strtoupper($code)]));
    }

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        $like = '%'.$term.'%';
        return $q->where(function ($w) use ($like) {
            $w->where('name', 'like', $like)
              ->orWhere('unit', 'like', $like)
              ->orWhere('notes', 'like', $like)
              ->orWhereHas('definition', function ($dw) use ($like) {
                  $dw->where('code', 'like', $like)
                     ->orWhere('name', 'like', $like)
                     ->orWhere('group', 'like', $like);
              });
        });
    }

    /* ===========================
     |  Accessors / Mutators
     |===========================*/

    /** Pastikan date selalu awal bulan (normalize) */
    protected function date(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                try {
                    return Carbon::parse($value)->startOfMonth();
                } catch (\Throwable) {
                    return $value;
                }
            }
        );
    }

    /** Nama display: pakai definisi kalau ada, fallback ke legacy name */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->definition->name ?? $this->name
        );
    }

    /** Unit display: pakai definisi kalau kosong di record */
    protected function displayUnit(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->unit ?: ($this->definition->unit ?? null)
        );
    }
}
