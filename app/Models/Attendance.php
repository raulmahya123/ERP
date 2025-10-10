<?php // app/Models/Attendance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'attendances';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','user_id','work_date','shift_id','source',
        'check_in_at','check_out_at','gps_in_lat','gps_in_lng',
        'gps_out_lat','gps_out_lng','device_id','late_minutes',
        'early_leave_minutes','overtime_minutes','work_minutes',
        'status','flags'
    ];
    protected $casts = [
        'work_date'   => 'date:Y-m-d',
        'check_in_at' => 'datetime',
        'check_out_at'=> 'datetime',
        'flags'       => 'array',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function shift(){ return $this->belongsTo(Shift::class); }
}
