<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ManpowerRealization extends Model
{
    use HasUuids;
    public $incrementing=false; protected $keyType='string';
    protected $table='manpower_realizations';
    protected $fillable = [
        'site_id','date','shift_slot','department',
        'actual_headcount','actual_operators','actual_mechanics','actual_helpers','actual_others',
        'production_tonnage','manhours','meta'
    ];
    protected $casts=['date'=>'date','meta'=>'array','production_tonnage'=>'decimal:2','manhours'=>'decimal:2'];
    public function scopeSite($q, ?string $sid){ return $q->where('site_id',$sid); }
}
