<?php // app/Models/ShiftRoster.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ShiftRoster extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'shift_rosters';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','user_id','roster_date','shift_id','crew_code','remarks'
    ];
    protected $casts = [
        'roster_date' => 'date:Y-m-d',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function shift(){ return $this->belongsTo(Shift::class); }
}
