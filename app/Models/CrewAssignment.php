<?php // app/Models/CrewAssignment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CrewAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'crew_assignments';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','date','shift_slot','user_id','equipment_id',
        'role','activity_code','remarks'
    ];
    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function equipment(){ return $this->belongsTo(Asset::class, 'equipment_id'); }
}
