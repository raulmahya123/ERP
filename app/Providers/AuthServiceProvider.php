<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\HrDailyEntry;
use App\Policies\HrDailyEntryPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        HrDailyEntry::class => HrDailyEntryPolicy::class,
        // Tambah mapping lain di sini bila perlu
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        // Gate tambahan kalau perlu
    }
}
