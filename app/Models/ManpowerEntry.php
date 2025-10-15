<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ManpowerEntry extends Model
{
    use HasFactory;

    protected $table = 'manpower_entries';
    public $incrementing = false;
    protected $keyType = 'string';

    // Enum tipe entri
    public const TYPE_PLAN  = 'PLAN';
    public const TYPE_REAL  = 'REAL';
    public const TYPE_ASSIGN = 'ASSIGN';

    protected $fillable = [
        'id',
        'site_id', 'date', 'shift_slot', 'entry_type',
        // PLAN/REAL (aggregates)
        'department',
        'planned_headcount','planned_operators','planned_mechanics','planned_helpers','planned_others',
        'actual_headcount','actual_operators','actual_mechanics','actual_helpers','actual_others',
        'production_tonnage','manhours',
        // ASSIGN (per user/equipment)
        'user_id','equipment_id','role','activity_code','remarks',
        // umum
        'note','meta',
    ];

    protected $casts = [
        'date' => 'date',
        'meta' => 'array',
        'production_tonnage' => 'decimal:2',
        'manhours' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
        });
    }

    // Helper pembuat instance (tanpa query/simpan)
    public static function makePlan(array $attrs = []): self
    {
        $attrs['entry_type'] = self::TYPE_PLAN;
        return new self($attrs);
    }

    public static function makeReal(array $attrs = []): self
    {
        $attrs['entry_type'] = self::TYPE_REAL;
        return new self($attrs);
    }

    public static function makeAssign(array $attrs = []): self
    {
        $attrs['entry_type'] = self::TYPE_ASSIGN;
        return new self($attrs);
    }
}
