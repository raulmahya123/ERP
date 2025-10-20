<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * App\Models\KpiDefinition
 *
 * Kolom (disarankan di migrasi):
 * - id (uuid, pk)
 * - order_no (int, nullable)
 * - code (string, unique, UPPER)
 * - name (string)
 * - group (string: leading|lagging|base|operational|environment|health|custom)
 * - value_type (string: int|decimal|rate|currency)
 * - agg (string: SUM|MAX|MIN|AVG|NONE)
 * - unit (string|null)
 * - is_derived (bool)
 * - threshold_value (decimal, nullable)
 * - threshold_label (string|null)
 * - meta (json|null)
 * - timestamps
 * - softDeletes (opsional)
 */
class KpiDefinition extends Model
{
    use HasFactory;
    use HasUuids;
    // uncomment jika tabel pakai soft deletes
    // use SoftDeletes;

    /** @var string */
    protected $table = 'kpi_definitions';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** Whitelist kolom yang boleh diisi (security / anti-mass assignment) */
    protected $fillable = [
        'order_no',
        'code',
        'name',
        'group',
        'value_type',
        'agg',
        'unit',
        'is_derived',
        'threshold_value',
        'threshold_label',
        'meta',
    ];

    /** Casts (typed) */
    protected $casts = [
        'is_derived'      => 'boolean',
        'meta'            => 'array',
        // simpan presisi angka saat serialisasi
        'threshold_value' => 'decimal:4',
    ];

    /** Konstanta bantu */
    public const GROUPS = [
        'leading', 'lagging', 'base', 'operational', 'environment', 'health', 'custom',
    ];

    public const VALUE_TYPES = [
        'int', 'decimal', 'rate', 'currency',
    ];

    public const AGGREGATIONS = [
        'SUM', 'AVG', 'MAX', 'MIN', 'NONE',
    ];

    /* ===========================
     |  Relationships
     |===========================*/
    public function indicators()
    {
        // relasi ke KPI realisasi bulanan
        return $this->hasMany(KpiIndicator::class, 'definition_id');
    }

    /* ===========================
     |  Scopes
     |===========================*/
    public function scopeCode($q, string $code)
    {
        return $q->whereRaw('UPPER(code) = ?', [strtoupper($code)]);
    }

    public function scopeGroup($q, string $group)
    {
        return $q->where('group', $group);
    }

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        $like = '%'.$term.'%';
        return $q->where(function ($w) use ($like) {
            $w->where('code', 'like', $like)
              ->orWhere('name', 'like', $like)
              ->orWhere('group', 'like', $like)
              ->orWhere('unit', 'like', $like)
              ->orWhere('threshold_label', 'like', $like);
        });
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('group')->orderBy('order_no')->orderBy('code');
    }

    /* ===========================
     |  Accessors / Helpers
     |===========================*/

    /** Selalu simpan & tampilkan code dalam uppercase */
    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn($v) => $v ? strtoupper(trim($v)) : $v,
        );
    }

    /** Label siap tampil: "CODE — Name" */
    public function displayLabel(): string
    {
        $code = $this->code ?? '';
        $name = $this->name ?? '';
        return $code && $name ? "{$code} — {$name}" : ($code ?: $name);
    }

    /** Map group → legacy type untuk kompatibilitas */
    public function legacyType(): string
    {
        return in_array($this->group, ['leading','lagging'], true)
            ? $this->group
            : 'operational';
    }

    /** Apakah tipe nilainya numerik bulat (int) */
    public function isIntegerType(): bool
    {
        return $this->value_type === 'int';
    }

    /** Apakah nilainya tipe rasio/persen */
    public function isRatioType(): bool
    {
        return in_array($this->value_type, ['rate','decimal'], true);
    }
}
