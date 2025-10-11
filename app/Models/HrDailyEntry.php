<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HrDailyEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_daily_entries';
    protected $keyType = 'string';
    public $incrementing = false;

    /** Tipe & Status standar */
    public const TYPES = [
        'leave'        => 'Leave',
        'permit'       => 'Permit',
        'sick'         => 'Sick',
        'shift_change' => 'Shift Change',
        'ga_request'   => 'GA Request',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** Default column values */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected $fillable = [
        'id','site_id','user_id','date','type','code','reason',
        'from_shift_id','to_shift_id','status','approved_by','approved_at',
        'created_by','updated_by','meta'
    ];

    protected $casts = [
        'date'        => 'date',
        'meta'        => 'array',
        'approved_at' => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /** ===== Boot ===== */
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
            // Default status pending bila belum diisi
            if (empty($m->status)) {
                $m->status = self::STATUS_PENDING;
            }
        });
    }

    /** ===== Relationships ===== */
    public function user()      { return $this->belongsTo(User::class); }
    public function site()      { return $this->belongsTo(Site::class); }
    public function fromShift() { return $this->belongsTo(Shift::class, 'from_shift_id'); }
    public function toShift()   { return $this->belongsTo(Shift::class, 'to_shift_id'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }

    /** ===== Accessors ===== */

    /** Label ramah untuk type */
    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => self::TYPES[$this->type] ?? Str::of((string)$this->type)->headline()->toString());
    }

    /** Boolean helpers */
    protected function isApproved(): Attribute
    {
        return Attribute::get(fn () => $this->status === self::STATUS_APPROVED);
    }

    protected function isRejected(): Attribute
    {
        return Attribute::get(fn () => $this->status === self::STATUS_REJECTED);
    }

    protected function isPending(): Attribute
    {
        return Attribute::get(fn () => $this->status === self::STATUS_PENDING);
    }

    /** Ringkasan meta (string sederhana; berguna untuk export/log) */
    protected function metaSummary(): Attribute
    {
        return Attribute::get(function () {
            $m = (array) ($this->meta ?? []);
            switch ($this->type) {
                case 'leave':
                    return trim(sprintf(
                        'Jenis:%s %s%s',
                        Arr::get($m, 'leave_type', '-'),
                        Arr::get($m, 'duration_days') ? 'Durasi:'.Arr::get($m, 'duration_days').'h ' : '',
                        !empty($m['half_day']) ? 'Half-day' : ''
                    ));
                case 'permit':
                    return trim(sprintf(
                        'Kategori:%s %s %s',
                        Arr::get($m, 'permit_category', '-'),
                        Arr::get($m, 'hours') ? 'Jam:'.Arr::get($m, 'hours') : '',
                        (Arr::get($m, 'start_time') && Arr::get($m, 'end_time')) ? Arr::get($m, 'start_time').'–'.Arr::get($m, 'end_time') : ''
                    ));
                case 'sick':
                    $flags = [];
                    if (!empty($m['doctor_note'])) $flags[] = 'Surat Dokter';
                    if (!empty($m['inpatient']))   $flags[] = 'Rawat Inap';
                    if (!empty($m['bpjs_claim']))  $flags[] = 'BPJS';
                    return trim(sprintf('Dx:%s %s', Arr::get($m,'diagnosis','-'), implode(',', $flags)));
                case 'shift_change':
                    $from = $this->fromShift?->name ?? '-';
                    $to   = $this->toShift?->name ?? '-';
                    $eff  = Arr::get($m, 'effective_from');
                    return trim($from.' → '.$to.($eff ? ' (Eff: '.$eff.')' : ''));
                case 'ga_request':
                    $map = [
                        'vehicle_booking'=>'Booking Kendaraan',
                        'travel'=>'Perjalanan Dinas',
                        'consumables'=>'ATK/Consumables',
                        'facility_repair'=>'Perbaikan Fasilitas',
                        'meeting_room'=>'Rapat/Meeting Room',
                        'other'=>'Lainnya',
                    ];
                    $chips = [];
                    if (!empty($m['category']))    $chips[] = $map[$m['category']] ?? $m['category'];
                    if (!empty($m['priority']))    $chips[] = 'Priority: '.ucfirst($m['priority']);
                    if (!empty($m['needed_date'])) $chips[] = 'Need: '.$m['needed_date'].(empty($m['needed_time'])?'':' '.$m['needed_time']);
                    if (!empty($m['location']))    $chips[] = 'Lokasi: '.$m['location'];
                    if (!empty($m['item_name']))   $chips[] = 'Item: '.$m['item_name'];
                    if (!empty($m['quantity']))    $chips[] = 'Qty: '.$m['quantity'].(empty($m['unit'])?'':' '.$m['unit']);
                    if (!empty($m['budget_code'])) $chips[] = 'COA: '.$m['budget_code'];
                    return implode(' | ', $chips);
                default:
                    return is_array($this->meta) ? json_encode($this->meta) : (string)$this->meta;
            }
        });
    }

    /** ===== Mutators ===== */

    /**
     * Normalisasi meta: konversi "1"/"true" ke bool untuk kunci boolean yang umum.
     * Dijalankan saat set attribute 'meta'.
     */
    protected function meta(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (is_string($value)) {
                    // Jika datang dari textarea/string JSON
                    $decoded = json_decode($value, true);
                    $value = json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $value];
                }
                if (!is_array($value)) return $value;

                $booleanKeys = [
                    // sick
                    'doctor_note','inpatient','bpjs_claim',
                    // leave
                    'half_day',
                ];

                foreach ($booleanKeys as $key) {
                    if (array_key_exists($key, $value)) {
                        $v = $value[$key];
                        $value[$key] = filter_var($v, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? in_array($v, [1, '1', 'on', 'yes'], true);
                    }
                }

                return $value;
            }
        );
    }

    /** ===== Scopes ===== */

    public function scopeForSite($q, ?string $siteId)
    {
        return $siteId ? $q->where('site_id', $siteId) : $q;
    }

    public function scopeType($q, ?string $type)
    {
        return $type ? $q->where('type', $type) : $q;
    }

    public function scopeOnDate($q, ?string $date)
    {
        return $date ? $q->whereDate('date', $date) : $q;
    }

    public function scopeBetweenDates($q, ?string $from, ?string $to)
    {
        if ($from && $to) return $q->whereBetween('date', [$from, $to]);
        return $q;
    }

    public function scopeSearch($q, ?string $s)
    {
        if (!$s) return $q;
        $s = trim($s);
        return $q->where(fn ($w) =>
            $w->where('reason','like',"%{$s}%")
              ->orWhere('code','like',"%{$s}%")
        );
    }

    /** ===== Helpers ===== */

    public function markApproved(string $userId): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function markRejected(string $userId): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }
}
