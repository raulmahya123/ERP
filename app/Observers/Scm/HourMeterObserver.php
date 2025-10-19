<?php

namespace App\Observers\Scm;

use App\Models\Scm\HourMeter;

class HourMeterObserver
{
    public function saving(HourMeter $m): void
    {
        $m->hm_delta = max(0, (float)$m->hm_end - (float)$m->hm_start);
        // aturan simple: delta tidak mungkin > 24 jam per hari/shift
        $m->anomaly  = $m->hm_delta < 0 || $m->hm_delta > 24;
    }
}
