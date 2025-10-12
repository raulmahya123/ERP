<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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

    /** Kolom yang bisa di-mass-assign */
    protected $fillable = [
        'id','site_id','user_id','date','type','code','reason',
        'from_shift_id','to_shift_id',
        'status','approved_by','approved_at','approval_notes',
        'created_by','updated_by',
        'notes','attachment_id',
        'meta',

        // ====== VIRTUAL → disimpan ke meta ======
        'permit_category','hours','start_time','end_time',
        'leave_type','duration_days','half_day',
        'doctor_note','inpatient','bpjs_claim','diagnosis',
        'effective_from',
        'category','priority','needed_date','needed_time','location',
        'item_name','quantity','unit','budget_code',
    ];

    protected $casts = [
        'date'        => 'date',
        'meta'        => 'array',   // GET -> array (decode), SET -> tetap kita kontrol via mutator agar string JSON di DB
        'approved_at' => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    protected $appends = [
        'type_label','meta_summary','is_approved','is_rejected','is_pending',
    ];

    /** ===== Boot ===== */
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->id)) {
                $m->id = (string) Str::uuid();
            }
            if (empty($m->status)) {
                $m->status = self::STATUS_PENDING;
            }
            if (empty($m->created_by)) {
                $m->created_by = Auth::id();
            }
            if (empty($m->site_id) && function_exists('session') && session()->has('site_id')) {
                $m->site_id = session('site_id');
            }
        });

        static::updating(function (self $m) {
            $m->updated_by = Auth::id();
        });
    }

    /** ===== Relationships ===== */
    public function user()      { return $this->belongsTo(User::class); }
    public function site()      { return $this->belongsTo(Site::class); }
    public function fromShift() { return $this->belongsTo(Shift::class, 'from_shift_id'); }
    public function toShift()   { return $this->belongsTo(Shift::class, 'to_shift_id'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }

    /** ===== Accessors ===== */

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => self::TYPES[$this->type] ?? Str::of((string)$this->type)->headline()->toString());
    }

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
     * Mutator meta: pastikan yang TERSIMPAN ke DB selalu STRING JSON.
     * - Jika diberi string -> biarkan apa adanya (anggap sudah JSON).
     * - Jika diberi array/object -> normalisasi & json_encode.
     */
    protected function meta(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                // Jika sudah string, jangan di-decode agar tidak berubah jadi array
                if (is_string($value)) {
                    return $value;
                }

                // Object -> array
                if (is_object($value)) {
                    $value = json_decode(json_encode($value), true) ?: [];
                }

                if (!is_array($value)) {
                    // biarkan Eloquent yang meng-handle (tidak akan dipakai juga)
                    return $value;
                }

                // Normalisasi boolean-keys
                $booleanKeys = [
                    'doctor_note','inpatient','bpjs_claim', // sick
                    'half_day',                              // leave
                ];
                foreach ($booleanKeys as $key) {
                    if (array_key_exists($key, $value)) {
                        $v = $value[$key];
                        $value[$key] = filter_var($v, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                            ?? in_array($v, [1, '1', 'on', 'yes', true], true);
                    }
                }

                // Simpan sebagai JSON string
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        );
    }

    /** Helper untuk bikin atribut virtual yang di-proxy ke meta */
    private function metaProxy(string $metaKey): Attribute
    {
        return Attribute::make(
            // GET: baca dari meta (cast -> array)
            get: fn () => Arr::get($this->meta ?? [], $metaKey),
            // SET: tulis ke meta & kembalikan 'meta' sebagai STRING JSON
            set: function ($value) use ($metaKey) {
                $meta = $this->meta ?? []; // berkat cast, ini array
                if ($value === null || $value === '') {
                    Arr::forget($meta, $metaKey);
                } else {
                    Arr::set($meta, $metaKey, $value);
                }
                return ['meta' => json_encode($meta, JSON_UNESCAPED_UNICODE)];
            }
        );
    }

    // ====== VIRTUAL ATTRIBUTES (mapping ke JSON meta) ======
    protected function permitCategory(): Attribute { return $this->metaProxy('permit_category'); }
    protected function hours(): Attribute           { return $this->metaProxy('hours'); }
    protected function startTime(): Attribute       { return $this->metaProxy('start_time'); }
    protected function endTime(): Attribute         { return $this->metaProxy('end_time'); }

    protected function leaveType(): Attribute       { return $this->metaProxy('leave_type'); }
    protected function durationDays(): Attribute    { return $this->metaProxy('duration_days'); }
    protected function halfDay(): Attribute         { return $this->metaProxy('half_day'); }

    protected function doctorNote(): Attribute      { return $this->metaProxy('doctor_note'); }
    protected function inpatient(): Attribute       { return $this->metaProxy('inpatient'); }
    protected function bpjsClaim(): Attribute       { return $this->metaProxy('bpjs_claim'); }
    protected function diagnosis(): Attribute       { return $this->metaProxy('diagnosis'); }

    protected function effectiveFrom(): Attribute   { return $this->metaProxy('effective_from'); }

    // GA Request common
    protected function category(): Attribute        { return $this->metaProxy('category'); }
    protected function priority(): Attribute        { return $this->metaProxy('priority'); }
    protected function neededDate(): Attribute      { return $this->metaProxy('needed_date'); }
    protected function neededTime(): Attribute      { return $this->metaProxy('needed_time'); }
    protected function location(): Attribute        { return $this->metaProxy('location'); }
    protected function itemName(): Attribute        { return $this->metaProxy('item_name'); }
    protected function quantity(): Attribute        { return $this->metaProxy('quantity'); }
    protected function unit(): Attribute            { return $this->metaProxy('unit'); }
    protected function budgetCode(): Attribute      { return $this->metaProxy('budget_code'); }

    /** ===== Scopes ===== */
    public function scopeForSite($q, ?string $siteId) { return $siteId ? $q->where('site_id', $siteId) : $q; }
    public function scopeType($q, ?string $type)      { return $type ? $q->where('type', $type) : $q; }
    public function scopeOnDate($q, ?string $date)    { return $date ? $q->whereDate('date', $date) : $q; }
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
    public function scopeStatus($q, ?string $status) { return $status ? $q->where('status', $status) : $q; }
    public function scopePending($q)  { return $q->where('status', self::STATUS_PENDING); }
    public function scopeApproved($q) { return $q->where('status', self::STATUS_APPROVED); }
    public function scopeRejected($q) { return $q->where('status', self::STATUS_REJECTED); }
    public function scopeRecent($q)   { return $q->orderByDesc('date')->orderByDesc('created_at'); }

    /** ===== Helpers ===== */
    public function approve(string $userId, ?string $notes = null): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        if ($notes !== null) $this->approval_notes = $notes;
        $this->save();
    }

    public function reject(string $userId, ?string $notes = null): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        if ($notes !== null) $this->approval_notes = $notes;
        $this->save();
    }

    public function markApproved(string $userId): void { $this->approve($userId); }
    public function markRejected(string $userId): void { $this->reject($userId); }
}
