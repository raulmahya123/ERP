<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DailyPlan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'scm_daily_plans';

    protected $fillable = [
        'site_id', 'plan_date', 'shift_id', 'remarks', 'extra',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'extra'     => 'array',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function items()
    {
        return $this->hasMany(DailyPlanItem::class, 'daily_plan_id');
    }
}
