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
        Incident::class                           => IncidentPolicy::class,
        \App\Models\IncidentInvestigation::class  => \App\Policies\IncidentInvestigationPolicy::class,
        \App\Models\HazardReport::class           => \App\Policies\HazardReportPolicy::class,
        \App\Models\Pica::class                   => \App\Policies\PicaPolicy::class,
        \App\Models\EnvironmentalSample::class    => \App\Policies\EnvironmentalSamplePolicy::class,
        \App\Models\MediaAttachment::class        => \App\Policies\MediaAttachmentPolicy::class,
        \App\Models\KpiIndicator::class           => \App\Policies\KpiIndicatorPolicy::class,

        // SCM
        \App\Models\Scm\Trip::class               => \App\Policies\Scm\TripPolicy::class,
        \App\Models\Scm\Breakdown::class          => \App\Policies\BreakdownPolicy::class,

        // HSE — additional
        \App\Models\Hse\HazardArea::class                => \App\Policies\HazardAreaPolicy::class,
        \App\Models\Hse\HseRtp::class                    => \App\Policies\HseRtpPolicy::class,
        \App\Models\Hse\HseInspectionReport::class       => \App\Policies\HseInspectionReportPolicy::class,

        // Production
        \App\Models\Production\ProductionMonthlyPlan::class  => \App\Policies\ProductionMonthlyPlanPolicy::class,
        \App\Models\Production\ProductionShiftPlan::class    => \App\Policies\ProductionShiftPlanPolicy::class,
        \App\Models\Production\ProductionActual::class       => \App\Policies\ProductionActualPolicy::class,
        \App\Models\Production\ProductionReconcile::class    => \App\Policies\ProductionReconcilePolicy::class,
        \App\Models\Production\ProductionShiftClosing::class => \App\Policies\ProductionShiftClosingPolicy::class,
        \App\Models\Production\ProductionMonthlyClosing::class => \App\Policies\ProductionMonthlyClosingPolicy::class,

        // HCM
        \App\Models\Hcm\RecruitmentCandidate::class       => \App\Policies\RecruitmentCandidatePolicy::class,
        \App\Models\Hcm\RecruitmentManpowerRequest::class => \App\Policies\RecruitmentManpowerRequestPolicy::class,
        \App\Models\Hcm\EmployeeMovementRequest::class    => \App\Policies\EmployeeMovementRequestPolicy::class,
        \App\Models\Hcm\EmployeeBenefit::class            => \App\Policies\EmployeeBenefitPolicy::class,
        \App\Models\Hcm\EmployeeBenefitClaim::class       => \App\Policies\EmployeeBenefitClaimPolicy::class,

        // Asset Management
        \App\Models\AssetMgmt\AssetArrMaster::class           => \App\Policies\AssetArrMasterPolicy::class,
        \App\Models\AssetMgmt\AssetAerMaster::class           => \App\Policies\AssetAerMasterPolicy::class,
        \App\Models\AssetMgmt\AssetDeliveryInstruction::class => \App\Policies\AssetDeliveryInstructionPolicy::class,

        // Plant
        \App\Models\Plant\PlantStandardJob::class          => \App\Policies\PlantStandardJobPolicy::class,
        \App\Models\Plant\PlantStrategiTask::class         => \App\Policies\PlantStrategiTaskPolicy::class,
        \App\Models\Plant\PlantWorkOrder::class            => \App\Policies\PlantWorkOrderPolicy::class,
        \App\Models\Plant\PlantLongTermPlanning::class     => \App\Policies\PlantLongTermPlanningPolicy::class,
        \App\Models\Plant\PlantBreakdownStatus::class      => \App\Policies\PlantBreakdownStatusPolicy::class,
        \App\Models\Plant\PlantPicklist::class             => \App\Policies\PlantPicklistPolicy::class,

        // Fuel
        \App\Models\Fuel\FuelTank::class         => \App\Policies\FuelTankPolicy::class,
        \App\Models\Fuel\FuelFlowMeter::class    => \App\Policies\FuelFlowMeterPolicy::class,
        \App\Models\Fuel\FuelConsume::class      => \App\Policies\FuelConsumePolicy::class,
        \App\Models\Fuel\FuelReceive::class      => \App\Policies\FuelReceivePolicy::class,
        \App\Models\Fuel\FuelStockCheck::class   => \App\Policies\FuelStockCheckPolicy::class,
        \App\Models\Fuel\FuelPosting::class      => \App\Policies\FuelPostingPolicy::class,
        \App\Models\Fuel\FuelAdjustment::class   => \App\Policies\FuelAdjustmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        // Gate tambahan kalau perlu
    }
}
