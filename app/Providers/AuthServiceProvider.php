<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Existing (kalau dipakai)
use App\Models\HrDailyEntry;
use App\Policies\HrDailyEntryPolicy;
use App\Models\Asset;
use App\Policies\AssetPolicy;

// HSE
use App\Models\Incident;
use App\Policies\IncidentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Existing
        HrDailyEntry::class => HrDailyEntryPolicy::class,
        Asset::class        => AssetPolicy::class,

        // HSE
        Incident::class     => IncidentPolicy::class,
        \App\Models\IncidentInvestigation::class   => \App\Policies\IncidentInvestigationPolicy::class,
        \App\Models\HazardReport::class            => \App\Policies\HazardReportPolicy::class,
        \App\Models\Pica::class                    => \App\Policies\PicaPolicy::class,
        \App\Models\EnvironmentalSample::class     => \App\Policies\EnvironmentalSamplePolicy::class,
        \App\Models\MediaAttachment::class         => \App\Policies\MediaAttachmentPolicy::class,
        \App\Models\KpiIndicator::class            => \App\Policies\KpiIndicatorPolicy::class,
        \App\Models\EnvironmentalSample::class => \App\Policies\EnvironmentalSamplePolicy::class,

    ];

    public function boot(): void
    {
        $this->registerPolicies();
        // Gate tambahan kalau perlu
    }
}
