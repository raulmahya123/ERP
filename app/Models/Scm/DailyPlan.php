<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\DB;

class DailyPlan extends Model
{
    use HasUuids;

    protected $table = 'scm_daily_plans';
    protected $fillable = ['site_id', 'plan_date', 'shift_id', 'remarks', 'extra'];
    protected $casts = ['plan_date' => 'date', 'extra' => 'array'];

    public function items()
    {
        return $this->hasMany(DailyPlanItem::class, 'daily_plan_id');
    }
    public function shift()
    {
        return $this->belongsTo(DB::raw('shifts'), 'shift_id');
    } // atau model Shift sendiri
}
