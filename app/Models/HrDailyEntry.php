<?php // app/Models/HrDailyEntry.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HrDailyEntry extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hr_daily_entries';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','user_id','date','type','code','reason',
        'from_shift_id','to_shift_id','meta'
    ];
    protected $casts = [
        'date' => 'date:Y-m-d',
        'meta' => 'array',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function fromShift(){ return $this->belongsTo(Shift::class,'from_shift_id'); }
    public function toShift(){ return $this->belongsTo(Shift::class,'to_shift_id'); }
}
