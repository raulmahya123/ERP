<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WbTicket extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'site_id',
        'ticket_no',
        'direction',
        'ticket_time',
        'unit_id',
        'pit_id',
        'stockpile_id',
        'commodity_id',
        'gross',
        'tare',
        'net',
        'pair_id',
        'notes'
    ];

    protected $casts = [
        'ticket_time' => 'datetime',
        'gross' => 'decimal:2',
        'tare' => 'decimal:2',
        'net' => 'decimal:2',
    ];
    public function unit()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'unit_id');
    }
    public function pit()
    {
        return $this->belongsTo(\App\Models\Location::class, 'pit_id');
    }
    public function stockpile()
    {
        return $this->belongsTo(\App\Models\Location::class, 'stockpile_id');
    }
    public function commodity()
    {
        return $this->belongsTo(\App\Models\Commodity::class, 'commodity_id');
    }
}
