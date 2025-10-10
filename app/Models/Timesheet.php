<?php // app/Models/Timesheet.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Timesheet extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'timesheets';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','user_id','shift_id','equipment_id','work_date',
        'activity_code','activity_desc','hours','overtime_hours',
        'cost_center','meta'
    ];
    protected $casts = [
        'work_date' => 'date:Y-m-d',
        'hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'meta' => 'array',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function shift(){ return $this->belongsTo(Shift::class); }
    public function equipment(){ return $this->belongsTo(Asset::class, 'equipment_id'); }
}
