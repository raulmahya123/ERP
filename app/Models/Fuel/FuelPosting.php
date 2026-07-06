<?php

namespace App\Models\Fuel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FuelPosting extends Model
{
    use HasUuids;

    protected $table = 'fuel_postings';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'site_id', 'posting_type', 'reference_type', 'reference_id',
        'posting_date', 'description', 'status', 'journal_entries', 'posted_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'journal_entries' => 'json',
    ];

    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class);
    }

    public function poster()
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }
}
