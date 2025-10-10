<?php // app/Models/ManpowerPlan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ManpowerPlan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'manpower_plans';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'site_id','date','shift_slot','department',
        'planned_headcount','planned_operators','planned_mechanics',
        'planned_helpers','planned_others','note','meta'
    ];
    protected $casts = [
        'date' => 'date:Y-m-d',
        'meta' => 'array',
    ];

    public function site(){ return $this->belongsTo(Site::class); }
}
