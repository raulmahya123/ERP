<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Trip extends Model
{
    protected $table = 'scm_trips';  
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'site_id','date','shift_id','unit_id','operator_id','pit_id',
        'from_stockpile_id','to_stockpile_id','commodity_id','material_type',
        'tonnage','distance_km','start_time','end_time','status',
        'wb_ticket_in_id','wb_ticket_out_id','client_uid','notes','created_by'
    ];

    protected $casts = [
        'date'       => 'date',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    // relasi
    public function site()       { return $this->belongsTo(\App\Models\Site::class); }
    public function shift()      { return $this->belongsTo(\App\Models\Shift::class); }
    public function unit()       { return $this->belongsTo(\App\Models\Asset::class, 'unit_id'); }
    public function operator()   { return $this->belongsTo(\App\Models\User::class, 'operator_id'); }
    public function pit()        { return $this->belongsTo(\App\Models\Location::class, 'pit_id'); }
    public function fromStock()  { return $this->belongsTo(\App\Models\Location::class, 'from_stockpile_id'); }
    public function toStock()    { return $this->belongsTo(\App\Models\Location::class, 'to_stockpile_id'); }
    public function commodity()  { return $this->belongsTo(\App\Models\Commodity::class); }
}
