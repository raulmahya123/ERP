<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayroalHistory extends Model
{
    use HasUuid;

    protected $table = 'payroal_histories';

    /** PK bertipe UUID string */
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'payroal_id',
        'period',
        'site_id',
        'gross',
        'deduction',
        'net',
        'take_home_pay',
        'earnings',
        'deductions',
        'meta',
        'status',
        'locked_at',
        'sent_at',
        'emailed_to',
        'pdf_path',
        'view_token',
    ];

    protected $casts = [
        'period'     => 'date',      // simpan YYYY-MM-01
        'earnings'   => 'array',
        'deductions' => 'array',
        'meta'       => 'array',
        'locked_at'  => 'datetime',
        'sent_at'    => 'datetime',
    ];

    /** Relasi ke payroal (wajib) */
    public function payroal(): BelongsTo
    {
        return $this->belongsTo(Payroal::class, 'payroal_id');
    }

    /** Relasi ke site (opsional) */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
