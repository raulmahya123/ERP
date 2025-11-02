<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DailyPlanItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'scm_daily_plan_items';

    protected $fillable = [
        'daily_plan_id', 'pit_id', 'target_ton', 'target_ritase', 'notes',
    ];

    protected $casts = [
        'target_ton' => 'decimal:2',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function plan()
    {
        return $this->belongsTo(DailyPlan::class, 'daily_plan_id');
    }
}
