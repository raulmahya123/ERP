<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

// Controllers (Pages & Auth)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManpowerEntryController;

// Admin Controllers
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\SiteContextController;
use App\Http\Controllers\Admin\SiteConfigController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MasterEntityController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\TimesheetController;
use App\Http\Controllers\Admin\ShiftRosterController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\HrDailyEntryController;
use App\Http\Controllers\Admin\EmploymentContractController;
use App\Http\Controllers\Admin\CrewAssignmentController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AssetAssignmentController;
use App\Http\Controllers\Admin\PayroalController;           // payroll profile (master data)
use App\Http\Controllers\Admin\PayroalHistoryController;    // payslip snapshots

// HSE Controllers
use App\Http\Controllers\Admin\Hse\IncidentController              as HseIncidentController;
use App\Http\Controllers\Admin\Hse\IncidentInvestigationController as HseInvestigationController;
use App\Http\Controllers\Admin\Hse\PicaController                 as HsePicaController;
use App\Http\Controllers\Admin\Hse\EnvironmentalSampleController   as HseEnvSampleController;
use App\Http\Controllers\Admin\Hse\HazardReportController          as HseHazardController;
use App\Http\Controllers\Admin\Hse\MediaAttachmentController       as HseMediaController;
use App\Http\Controllers\Admin\Hse\KpiIndicatorController          as HseKpiController;

// Other Controllers
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\AttendanceController as AttendanceTapController; // self-service tap
use App\Http\Controllers\PayroalProfileController;                         // self-service payroll profile

// OTP-only verification
use App\Http\Controllers\Auth\VerifyEmailCodeController;
use App\Http\Controllers\Scm\{TripController, HourMeterController, FuelLogController, WbTicketController, BreakdownController, PitController};


use App\Models\PayroalHistory;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
/* --------------------------------------------------------------------------
| Global patterns & root redirect
|-------------------------------------------------------------------------- */

Route::pattern('uuid',   '[0-9a-fA-F-]{36}');
Route::pattern('record', '[0-9a-fA-F-]{36}');
Route::pattern('entity', '^[a-z0-9_]+$');

Route::redirect('/', '/login')->name('root');

/* --------------------------------------------------------------------------
| OTP-only verification routes (after login; blocks until email_verified_at terisi)
|-------------------------------------------------------------------------- */
Route::middleware(['auth'])->group(function () {
    // penting: gunakan nama 'verification.notice' agar middleware 'verified' redirect ke sini
    Route::get('/verify-code', [VerifyEmailCodeController::class, 'create'])->name('verification.notice');
    Route::post('/verify-code', [VerifyEmailCodeController::class, 'store'])->name('verification.code.verify');
    Route::post('/verify-code/resend', [VerifyEmailCodeController::class, 'resend'])->name('verification.code.resend');
});

/* --------------------------------------------------------------------------
| Authenticated: dashboard, quick actions, profile, attendance tap
|-------------------------------------------------------------------------- */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/dashboard/assets', [DashboardController::class, 'quickStore'])
        ->middleware(['hasrole:gm|manager', 'throttle:30,1'])
        ->name('dashboard.assets.quick-store');

    // Profile
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Attendance (self-service)
    Route::get('/attendance/tap',        [AttendanceTapController::class, 'tapPage'])->name('attendance.tap');
    Route::post('/attendance/check-in',  [AttendanceTapController::class, 'checkIn'])
        ->name('attendance.checkin')->middleware('throttle:20,1');
    Route::post('/attendance/check-out', [AttendanceTapController::class, 'checkOut'])
        ->name('attendance.checkout')->middleware('throttle:20,1');
});

/* --------------------------------------------------------------------------
| Site selection (persist to session)
|-------------------------------------------------------------------------- */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/sites/select', function () {
        $sites = \App\Models\Site::query()->orderBy('name')->get();
        return view('admin.sites.select', compact('sites'));
    })->name('sites.select');

    Route::post('/sites/select', function (Request $request) {
        $data = $request->validate([
            'site_id' => ['required', 'uuid', 'exists:sites,id'],
        ]);
        $request->session()->put('site_id', $data['site_id']);
        $intended = $request->session()->pull('url.intended');
        return redirect()->to($intended ?: route('dashboard'))
            ->with('success', 'Site aktif telah diubah.');
    })->middleware('throttle:30,1')->name('sites.choose');
});

/* --------------------------------------------------------------------------
| Role dashboards (thin, explicit authorization per role)
|-------------------------------------------------------------------------- */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('hasrole:gm')->get('/gm',          [RoleDashboardController::class, 'gm'])->name('gm.dashboard');
    Route::middleware('hasrole:manager')->get('/manager', [RoleDashboardController::class, 'manager'])->name('manager.dashboard');
    Route::middleware('hasrole:foreman')->get('/foreman', [RoleDashboardController::class, 'foreman'])->name('foreman.dashboard');
    Route::middleware('hasrole:operator')->get('/operator', [RoleDashboardController::class, 'operator'])->name('operator.dashboard');
    Route::middleware('hasrole:hse_officer')->get('/hse', [RoleDashboardController::class, 'hse'])->name('hse.dashboard');
    Route::middleware('hasrole:hr')->get('/hr',          [RoleDashboardController::class, 'hr'])->name('hr.dashboard');
    Route::middleware('hasrole:finance')->get('/finance', [RoleDashboardController::class, 'finance'])->name('finance.dashboard');
});

/* --------------------------------------------------------------------------
| Admin: core identity (users/roles/divisions)
|-------------------------------------------------------------------------- */
Route::prefix('admin')->as('admin.')->middleware(['auth', 'verified', 'hasrole:gm|manager'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password')->whereUuid('user');
    Route::get('users-export', [UserController::class, 'export'])->name('users.export');
    Route::resource('divisions', DivisionController::class);
});

/* --------------------------------------------------------------------------
| Admin: user access management (role & site assignment per user)
|-------------------------------------------------------------------------- */
Route::prefix('admin/access')->as('admin.access.')->middleware(['auth', 'verified', 'hasrole:gm|manager'])->group(function () {
    // Assign/revoke roles
    Route::post('users/{user}/roles/grant',  [UserAccessController::class, 'grantRoles'])->whereUuid('user')->name('users.roles.grant');
    Route::post('users/{user}/roles/revoke', [UserAccessController::class, 'revokeRoles'])->whereUuid('user')->name('users.roles.revoke');

    // Assign site contexts
    Route::get('users/{user}/sites',         [UserAccessController::class, 'sites'])->whereUuid('user')->name('users.sites');
    Route::post('users/{user}/sites/attach', [UserAccessController::class, 'attachSite'])->whereUuid('user')->name('users.sites.attach');
    Route::post('users/{user}/sites/detach', [UserAccessController::class, 'detachSite'])->whereUuid('user')->name('users.sites.detach');

    // Optional: bulk import access
    Route::post('users/import-access',       [UserAccessController::class, 'importAccess'])->name('users.import-access');
});

/* --------------------------------------------------------------------------
| Admin: site management & context helpers
|-------------------------------------------------------------------------- */
Route::prefix('admin/sites')->as('admin.sites.')->middleware(['auth', 'verified', 'hasrole:gm'])->group(function () {
    Route::resource('/', SiteController::class)
        ->parameters(['' => 'site'])
        ->except(['show'])
        ->names([
            'index'  => 'index',
            'create' => 'create',
            'store'  => 'store',
            'edit'   => 'edit',
            'update' => 'update',
            'destroy' => 'destroy',
        ]);

    // Switch active site via controller (alternatif dari session route di atas)
    Route::post('context/switch', [SiteContextController::class, 'switch'])->name('context.switch');
});

/* --------------------------------------------------------------------------
| Admin: site/global config (managed by GM)
|-------------------------------------------------------------------------- */
Route::prefix('admin/settings')->as('admin.settings.')->middleware(['auth', 'verified', 'hasrole:gm'])->group(function () {
    Route::get('/',            [SiteConfigController::class, 'index'])->name('index');
    Route::match(['post', 'put', 'patch'], '/', [SiteConfigController::class, 'upsert'])->name('upsert');
    Route::get('/export',      [SiteConfigController::class, 'export'])->name('export');
    Route::post('/import',     [SiteConfigController::class, 'import'])->name('import');
});

/* --------------------------------------------------------------------------
| Admin: master entities (structure controlled by GM)
|-------------------------------------------------------------------------- */
Route::prefix('admin/master-entities')->as('admin.master_entities.')->middleware(['auth', 'verified', 'hasrole:gm'])->group(function () {
    Route::get('/',                      [MasterEntityController::class, 'index'])->name('index');
    Route::get('/create',                [MasterEntityController::class, 'create'])->name('create');
    Route::post('/',                     [MasterEntityController::class, 'store'])->name('store');
    Route::get('/{master_entity}/edit',  [MasterEntityController::class, 'edit'])->name('edit');
    Route::match(['post', 'put', 'patch'], '/{master_entity}', [MasterEntityController::class, 'update'])->name('update');
    Route::delete('/{master_entity}',    [MasterEntityController::class, 'destroy'])->name('destroy');
});

/* --------------------------------------------------------------------------
| Admin: master (rapih, entity & record dipisah + legacy redirect)
|-------------------------------------------------------------------------- */
Route::prefix('admin/master')->as('admin.master.')->group(function () {

    /* ===== Minimal auth only (overview + legacy redirect) ===== */
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', fn() => redirect()->route('admin.master.overview'))->name('home');
        Route::get('overview', [MasterDataController::class, 'overview'])->name('overview');

        // LEGACY: /admin/master/permissions/{record}?entity=...
        Route::get('permissions/{record}', function (Request $r, string $record) {
            $entity = (string) $r->query('entity', '');
            abort_if($entity === '', 404, 'Missing entity.');
            return redirect()->route('admin.master.permissions', [
                'entity' => $entity,
                'record' => $record,
            ]);
        })->where('record', '[0-9a-fA-F-]{36}')->name('permissions.legacy');
    });

    /* ===== Protected by site context + per-entity policy ===== */
    Route::middleware(['auth', 'verified', 'site.selected', 'can:master.access,entity'])->group(function () {

        $uuid        = '[0-9a-fA-F-]{36}';
        $entityRegex = '^(?!overview$)[a-z0-9_]+$';

        /* ---------- TANPA {record} (scope entity) ---------- */
        Route::prefix('{entity}')
            ->where(['entity' => $entityRegex])
            ->group(function () {
                Route::get('/',               [MasterDataController::class, 'index'])->name('index');
                Route::get('create',          [MasterDataController::class, 'create'])->name('create');
                Route::post('/',              [MasterDataController::class, 'store'])->name('store');

                Route::get('lookup',          [MasterDataController::class, 'lookup'])->name('lookup');
                Route::get('export',          [MasterDataController::class, 'export'])->name('export');
                Route::post('import',         [MasterDataController::class, 'import'])->name('import');
                Route::get('import-template', [MasterDataController::class, 'importTemplate'])->name('import.template');
                Route::delete('bulk-delete',  [MasterDataController::class, 'bulkDelete'])->name('bulk-delete');
            });

        /* ---------- DENGAN {record} (scope record) ---------- */
        Route::prefix('{entity}/{record}')
            ->where(['entity' => $entityRegex, 'record' => $uuid])
            ->group(function () {
                Route::get('/',                    [MasterDataController::class, 'show'])->name('show');
                Route::get('edit',                 [MasterDataController::class, 'edit'])->name('edit');
                Route::match(['post', 'put', 'patch'], '/', [MasterDataController::class, 'update'])->name('update');
                Route::delete('/',                 [MasterDataController::class, 'destroy'])->name('destroy');

                Route::post('duplicate',           [MasterDataController::class, 'duplicate'])->name('duplicate');

                // per-record permissions
                Route::get('permissions',                [MasterDataController::class, 'permissions'])->name('permissions');
                Route::match(['post', 'put', 'patch'], 'permissions', [MasterDataController::class, 'permissionsUpdate'])->name('permissions.update');
            });
    });
});

/* --------------------------------------------------------------------------
| Admin: AUDIT LOGS (read-only, export, purge by GM/superadmin)
|-------------------------------------------------------------------------- */
Route::prefix('admin/audit-logs')->as('admin.audit_logs.')->middleware(['auth', 'verified', 'hasrole:gm|superadmin'])->group(function () {
    Route::get('/',                   [AuditLogController::class, 'index'])->name('index');       // list + filter (q, actor, date)
    Route::get('/{log}',              [AuditLogController::class, 'show'])->name('show');         // detail satu log
    Route::get('/export/csv',         [AuditLogController::class, 'exportCsv'])->name('export');  // export
    Route::post('/purge',             [AuditLogController::class, 'purge'])->name('purge');       // purge by filter (date range)
});

/* --------------------------------------------------------------------------
| Admin: commodities (policy-protected inside controllers)
|-------------------------------------------------------------------------- */
Route::prefix('admin/commodities')->as('admin.commodities.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/',                   [CommodityController::class, 'index'])->name('index');
    Route::get('/create',             [CommodityController::class, 'create'])->name('create');
    Route::post('/',                  [CommodityController::class, 'store'])->name('store');
    Route::get('/{commodity}/edit',   [CommodityController::class, 'edit'])->name('edit');
    Route::match(['post', 'put', 'patch'], '/{commodity}', [CommodityController::class, 'update'])->name('update');
    Route::delete('/{commodity}',     [CommodityController::class, 'destroy'])->name('destroy');
});

/* --------------------------------------------------------------------------
| Admin: assets (policy + site context)
|-------------------------------------------------------------------------- */
Route::prefix('admin')->as('admin.')->middleware(['auth', 'verified', 'site.selected', 'can:viewAny,App\\Models\\Asset'])->group(function () {
    Route::resource('assets', AssetController::class)
        ->parameters(['assets' => 'asset'])
        ->whereUuid(['asset']);

    Route::prefix('assets/{asset}')->as('assets.')->whereUuid(['asset'])->group(function () {
        Route::get('assignments',        [AssetAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/create', [AssetAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('assignments',       [AssetAssignmentController::class, 'store'])->name('assignments.store');
    });

    Route::resource('asset-assignments', AssetAssignmentController::class)
        ->parameters(['asset-assignments' => 'assetAssignment'])
        ->except(['show']);
});

/* --------------------------------------------------------------------------
| Admin HR Suite (GM/HR) — attendance, timesheet, shift, GA, contracts, crew
|-------------------------------------------------------------------------- */
Route::prefix('admin')->as('admin.')->middleware(['auth','verified','site.selected'])->group(function () {
    Route::resource('attendance', AttendanceController::class)->parameters(['attendance' => 'attendance'])->except(['show']);
    Route::resource('timesheets', TimesheetController::class)->parameters(['timesheets' => 'timesheet'])->except(['show']);
    Route::resource('shift-rosters', ShiftRosterController::class)->parameters(['shift-rosters' => 'shiftRoster'])->except(['show']);
    Route::resource('shifts', ShiftController::class)->parameters(['shifts' => 'shift'])->except(['show']);

    Route::get('shift-rosters/shifts-by-site', [ShiftRosterController::class, 'shiftsBySite'])->name('shift-rosters.shifts-by-site');

    // Overtime workflow
    Route::get('overtime', [TimesheetController::class, 'otIndex'])->name('overtime.index');
    Route::post('timesheets/{timesheet}/ot/submit',  [TimesheetController::class, 'otSubmit'])->whereUuid('timesheet')->name('timesheets.ot.submit');
    Route::post('timesheets/{timesheet}/ot/approve', [TimesheetController::class, 'otApprove'])->whereUuid('timesheet')->name('timesheets.ot.approve');
    Route::post('timesheets/{timesheet}/ot/reject',  [TimesheetController::class, 'otReject'])->whereUuid('timesheet')->name('timesheets.ot.reject');

    // GA Locations
    Route::resource('locations', LocationController::class)->parameters(['locations' => 'location'])->except(['show']);

    // HR Daily Entry: types
    Route::prefix('hr-entries/types')->name('hr-entries.types.')->middleware('can:manage,App\\Models\\HrDailyEntry')->group(function () {
        Route::get('/',  [HrDailyEntryController::class, 'typesIndex'])->name('index');
        Route::post('/', [HrDailyEntryController::class, 'typesStore'])->name('store');
        Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'typesUpdate'])->name('update')->where('type', '[A-Za-z0-9_\-]+');
        Route::delete('/{type}', [HrDailyEntryController::class, 'typesDestroy'])->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
        Route::post('/reorder', [HrDailyEntryController::class, 'typesReorder'])->name('reorder');
    });

    // HR Daily Entry: meta form config
    Route::prefix('hr-entries/meta-form-config')->name('hr-entries.meta-form.')->middleware('auth', 'verified')->group(function () {
        Route::get('/', [HrDailyEntryController::class, 'metaFormConfigIndex'])->name('index');
        Route::get('/manage/{type?}', [HrDailyEntryController::class, 'metaFormConfigManage'])->name('manage')->where('type', '[A-Za-z0-9_\-]+');
        Route::get('/{type}', [HrDailyEntryController::class, 'metaFormConfigShow'])->name('show')->where('type', '^(?!manage$)[A-Za-z0-9_\-]+');
        Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'metaFormConfigUpsert'])->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
        Route::delete('/{type}', [HrDailyEntryController::class, 'metaFormConfigDestroy'])->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
    });

    // HR Daily Entry: meta schemas
    Route::prefix('hr-entries/meta-schema')->name('hr-entries.meta-schema.')->middleware('can:manage,App\\Models\\HrDailyEntry')->group(function () {
        Route::get('/', [HrDailyEntryController::class, 'metaSchemasIndex'])->name('index');
        Route::get('/manage/{type?}', [HrDailyEntryController::class, 'metaSchemasManage'])->name('manage')->where('type', '[A-Za-z0-9_\-]+');
        Route::get('/{type}', [HrDailyEntryController::class, 'metaSchemasShow'])->name('show')->where('type', '^(?!manage$)[A-Za-z0-9_\-]+');
        Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'metaSchemasUpsert'])->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
        Route::delete('/{type}', [HrDailyEntryController::class, 'metaSchemasDestroy'])->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
    });

    // HR Daily Entry: approval schemas
    Route::prefix('hr-entries/approval/schemas')->name('hr-entries.approval.schemas.')->middleware('can:viewAny,App\Models\HrDailyEntry')->group(function () {
        Route::get('/', [HrDailyEntryController::class, 'approvalSchemasIndex'])->name('index');
        Route::get('/{type}', [HrDailyEntryController::class, 'approvalSchemasShow'])->name('show')->where('type', '[A-Za-z0-9_\-]+');
        Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'approvalSchemasUpsert'])->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
        Route::delete('/{type}', [HrDailyEntryController::class, 'approvalSchemasDestroy'])->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
    });

    // HR Daily Entries CRUD + bulk + lifecycle
    Route::resource('hr-entries', HrDailyEntryController::class)->parameters(['hr-entries' => 'entry'])->except(['show'])->whereUuid(['entry']);
    Route::post('hr-entries/{entry}/submit',  [HrDailyEntryController::class, 'approvalSubmit'])->middleware('can:submit,entry')->whereUuid('entry')->name('hr-entries.submit');
    Route::post('hr-entries/{entry}/approve', [HrDailyEntryController::class, 'approvalApprove'])->middleware('can:approve,entry')->whereUuid('entry')->name('hr-entries.approve');
    Route::post('hr-entries/{entry}/reject',  [HrDailyEntryController::class, 'approvalReject'])->middleware('can:reject,entry')->whereUuid('entry')->name('hr-entries.reject');

    Route::post('hr-entries/bulk', [HrDailyEntryController::class, 'bulk'])->middleware('can:bulkAction,App\\Models\\HrDailyEntry')->name('hr-entries.bulk');
    Route::get('hr-entries/trashed', [HrDailyEntryController::class, 'trashed'])->middleware('can:viewTrashed,App\\Models\\HrDailyEntry')->name('hr-entries.trashed');
    Route::post('hr-entries/{entry}/restore', [HrDailyEntryController::class, 'restore'])->middleware('can:restore,entry')->whereUuid('entry')->name('hr-entries.restore');
    Route::delete('hr-entries/{entry}/force', [HrDailyEntryController::class, 'forceDelete'])->middleware('can:forceDelete,entry')->whereUuid('entry')->name('hr-entries.force-delete');

    Route::get('hr-entries/export/csv', [HrDailyEntryController::class, 'exportCsv'])->middleware('can:export,App\\Models\\HrDailyEntry')->name('hr-entries.export.csv');
    Route::get('hr-entries/print',     [HrDailyEntryController::class, 'print'])->middleware('can:export,App\\Models\\HrDailyEntry')->name('hr-entries.print');

    Route::get('hr-entries/{entry}/attachments/{key}', [HrDailyEntryController::class, 'downloadAttachment'])
        ->middleware('can:view,entry')->whereUuid('entry')->name('hr-entries.attachments.download');

    // Options endpoints
    Route::get('hr-entries/options/types',           [HrDailyEntryController::class, 'typesOptions'])->name('hr-entries.options.types');
    Route::get('hr-entries/options/ga-categories',   [HrDailyEntryController::class, 'gaCategoriesOptions'])->name('hr-entries.options.ga-categories');
    Route::get('hr-entries/search/users',            [HrDailyEntryController::class, 'searchUsers'])->middleware('can:viewAny,App\\Models\\User')->name('hr-entries.search.users');

    // Contracts & Crew
    Route::resource('contracts',         EmploymentContractController::class)->parameters(['contracts' => 'employmentContract'])->except(['show']);
    Route::resource('crew-assignments',  CrewAssignmentController::class)->parameters(['crew-assignments' => 'crewAssignment'])->except(['show']);
});

/* --------------------------------------------------------------------------
| Print templates (separated to avoid admin prefix collision)
|-------------------------------------------------------------------------- */
Route::prefix('hr-entries/print-templates')->as('hr-entries.print-templates.')->middleware(['auth', 'verified', 'can:manage,App\\Models\\HrDailyEntry'])->group(function () {
    Route::get('/',        [HrDailyEntryController::class, 'printTemplatesIndex'])->name('index');
    Route::get('/{type}',  [HrDailyEntryController::class, 'printTemplatesShow'])->where('type', '[A-Za-z0-9_\-]+')->name('show');
    Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'printTemplatesUpsert'])->where('type', '[A-Za-z0-9_\-]+')->name('upsert');
    Route::delete('/{type}', [HrDailyEntryController::class, 'printTemplatesDestroy'])->where('type', '[A-Za-z0-9_\-]+')->name('destroy');
});

/* --------------------------------------------------------------------------
| Payroll master (GM/HR) & self-service
|-------------------------------------------------------------------------- */
Route::prefix('admin')->as('admin.')->middleware(['auth', 'verified', 'hasrole:gm|hr'])->group(function () {
    Route::resource('payroal', PayroalController::class)->parameters(['payroal' => 'payroal'])->except(['show'])->whereUuid(['payroal']);
    Route::get('payroal/export/csv', [PayroalController::class, 'exportCsv'])->name('payroal.export.csv');
    Route::get('payroal/print',      [PayroalController::class, 'print'])->name('payroal.print');
    Route::post('payroal/{payroal}/lock',   [PayroalController::class, 'lock'])->whereUuid('payroal')->name('payroal.lock');
    Route::post('payroal/{payroal}/unlock', [PayroalController::class, 'unlock'])->whereUuid('payroal')->name('payroal.unlock');
    Route::get('payroal/lookup/by-user', [PayroalController::class, 'lookupByUser'])->name('payroal.lookup.by-user');
});

// Self-service payroll profile (authenticated)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/me/payroal',               [PayroalProfileController::class, 'edit'])->name('me.payroal.edit');
    Route::match(['put', 'patch'], '/me/payroal', [PayroalProfileController::class, 'update'])->name('me.payroal.update');
    Route::post('/me/payroal/upload',       [PayroalProfileController::class, 'upload'])->name('me.payroal.upload');
    Route::get('/me/payroal/download.xls',  [PayroalProfileController::class, 'downloadXlsx'])->name('me.payroal.download.xls');
});

/* --------------------------------------------------------------------------
| Manpower entries (self-service + admin views)
|-------------------------------------------------------------------------- */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('manpower/entries', ManpowerEntryController::class)->parameters(['entries' => 'entry'])->names('manpower.entries');
});

Route::prefix('admin')->as('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::resource('manpower/entries', ManpowerEntryController::class)->parameters(['entries' => 'entry'])->names('manpower.entries');
});

/* --------------------------------------------------------------------------
| HSE module (GM/Manager/HR/HSE Officer) — requires site context
|-------------------------------------------------------------------------- */
Route::prefix('admin/hse')->as('admin.hse.')->middleware(['auth', 'verified', 'hasrole:gm|manager|hr|hse_officer', 'site.selected'])->group(function () {
    Route::get('ping', fn() => 'OK');

    // Incidents
    Route::resource('incidents', HseIncidentController::class)->parameters(['incidents' => 'incident'])->whereUuid(['incident']);
    Route::post('incidents/{incident}/submit',              [HseIncidentController::class, 'submit'])->whereUuid('incident')->name('incidents.submit');
    Route::post('incidents/{incident}/start-investigation', [HseIncidentController::class, 'startInvestigation'])->whereUuid('incident')->name('incidents.start-investigation');
    Route::post('incidents/{incident}/close',               [HseIncidentController::class, 'close'])->whereUuid('incident')->name('incidents.close');

    // Investigations
    Route::resource('investigations', HseInvestigationController::class)->parameters(['investigations' => 'investigation'])->whereUuid(['investigation']);
    Route::post('investigations/{investigation}/complete', [HseInvestigationController::class, 'complete'])->whereUuid('investigation')->name('investigations.complete');
    Route::post('investigations/{investigation}/reopen',   [HseInvestigationController::class, 'reopen'])->whereUuid('investigation')->name('investigations.reopen');

    // PICA
    Route::resource('picas', HsePicaController::class)->parameters(['picas' => 'pica'])->whereUuid(['pica']);
    Route::post('picas/{pica}/mark-effective',   [HsePicaController::class, 'markEffective'])->whereUuid('pica')->name('picas.mark-effective');
    Route::post('picas/{pica}/mark-ineffective', [HsePicaController::class, 'markIneffective'])->whereUuid('pica')->name('picas.mark-ineffective');
    Route::post('picas/{pica}/close',            [HsePicaController::class, 'close'])->whereUuid('pica')->name('picas.close');

    // Hazards
    Route::resource('hazards', HseHazardController::class)->parameters(['hazards' => 'hazard'])->whereUuid(['hazard']);
    Route::post('hazards/{hazard}/assign',   [HseHazardController::class, 'assign'])->whereUuid('hazard')->name('hazards.assign');
    Route::post('hazards/{hazard}/mitigate', [HseHazardController::class, 'mitigate'])->whereUuid('hazard')->name('hazards.mitigate');
    Route::post('hazards/{hazard}/verify',   [HseHazardController::class, 'verify'])->whereUuid('hazard')->name('hazards.verify');
    Route::post('hazards/{hazard}/close',    [HseHazardController::class, 'close'])->whereUuid('hazard')->name('hazards.close');

    // Environmental Samples
    Route::resource('environmental-samples', HseEnvSampleController::class)->parameters(['environmental-samples' => 'sample'])->whereUuid(['sample']);

    // Media (polymorphic)
    Route::post('media/{type}/{id}', [HseMediaController::class, 'store'])
        ->where(['type' => 'incidents|investigations|picas|hazards|environmental-samples', 'id' => '[0-9a-fA-F-]{36}'])
        ->name('media.store');
    Route::delete('media/{attachment}', [HseMediaController::class, 'destroy'])->whereUuid('attachment')->name('media.destroy');

    // KPI
    Route::resource('kpi-indicators', HseKpiController::class)->parameters(['kpi-indicators' => 'kpi'])->whereUuid(['kpi']);
    Route::get('kpi-indicators/type/{type}', [HseKpiController::class, 'index'])->whereIn('type', ['leading', 'lagging', 'operational'])->name('kpi-indicators.type');
    Route::get('kpi-indicators/export/csv', [HseKpiController::class, 'exportCsv'])->name('kpi-indicators.export.csv');
    Route::post('kpi-indicators/import',    [HseKpiController::class, 'import'])->name('kpi-indicators.import');
});

/* --------------------------------------------------------------------------
| PAYROAL HISTORY (GM/HR/superadmin) — payslip snapshots
|-------------------------------------------------------------------------- */
Route::prefix('admin/payroal/history')->as('admin.payroal_history.')->middleware(['auth', 'verified', 'hasrole:hr|gm|superadmin'])->group(function () {
    Route::get('/',                [PayroalHistoryController::class, 'index'])->name('index');
    Route::get('/create',          [PayroalHistoryController::class, 'create'])->name('create');
    Route::post('/',               [PayroalHistoryController::class, 'store'])->name('store');
    Route::post('/{history}/lock', [PayroalHistoryController::class, 'lock'])->whereUuid('history')->name('lock');
    Route::post('/{history}/send', [PayroalHistoryController::class, 'sendOne'])->whereUuid('history')->name('sendOne');
    Route::post('/send-bulk',      [PayroalHistoryController::class, 'sendBulk'])->name('sendBulk');
});


Route::middleware(['auth', 'site.selected', 'hasrole:gm|manager|scm'])
    ->prefix('scm')->name('scm.')
    ->group(function () {
        Route::resource('pits', PitController::class)
            ->parameters(['pits' => 'pit'])
            ->except(['show'])
            ->whereUuid(['pit']);

        Route::resource('trips', TripController::class);
        Route::post('trips/{trip}/submit',   [TripController::class, 'submit'])->name('trips.submit');
        Route::post('trips/{trip}/validate', [TripController::class, 'validateData'])->name('trips.validate');
        Route::post('trips/{trip}/approve',  [TripController::class, 'approve'])->name('trips.approve');

        Route::resource('hour-meters', HourMeterController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['hour-meters' => 'hour_meter'])
            ->names([
                'index'   => 'hour_meters.index',
                'create'  => 'hour_meters.create',
                'store'   => 'hour_meters.store',
                'edit'    => 'hour_meters.edit',
                'update'  => 'hour_meters.update',
                'destroy' => 'hour_meters.destroy',
            ]);

        Route::resource('fuel-logs', FuelLogController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['fuel-logs' => 'fuel_log'])
            ->names([
                'index'   => 'fuel_logs.index',
                'create'  => 'fuel_logs.create',
                'store'   => 'fuel_logs.store',
                'edit'    => 'fuel_logs.edit',
                'update'  => 'fuel_logs.update',
                'destroy' => 'fuel_logs.destroy',
            ]);

        Route::resource('wb-tickets', WbTicketController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['wb-tickets' => 'wb_ticket'])
            ->names([
                'index'   => 'wb_tickets.index',
                'create'  => 'wb_tickets.create',
                'store'   => 'wb_tickets.store',
                'edit'    => 'wb_tickets.edit',
                'update'  => 'wb_tickets.update',
                'destroy' => 'wb_tickets.destroy',
            ]);

        Route::resource('reason-codes', \App\Http\Controllers\Scm\ReasonCodeController::class)->except('show');
        Route::resource('daily-plans', \App\Http\Controllers\Scm\DailyPlanController::class);
        Route::resource('dispatches', \App\Http\Controllers\Scm\DispatchController::class);
        Route::resource('handovers', \App\Http\Controllers\Scm\HandoverController::class);
        Route::get('reports/target-vs-actual', [\App\Http\Controllers\Scm\ReportController::class, 'targetVsActual'])
            ->name('reports.target-actual');

        // ⚠️ HAPUS baris trips duplikat yang ada di bawah sebelumnya
    });

Route::resource('breakdowns', BreakdownController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->parameters(['breakdowns' => 'breakdown'])
    ->names([
        'index'   => 'breakdowns.index',
        'create'  => 'breakdowns.create',
        'store'   => 'breakdowns.store',
        'edit'    => 'breakdowns.edit',
        'update'  => 'breakdowns.update',
        'destroy' => 'breakdowns.destroy',
    ]);


/* --------------------------------------------------------------------------
| Payslip public token (no login)
|-------------------------------------------------------------------------- */
Route::get('/me/payslip/{token}', function (string $token) {
    $h = PayroalHistory::where('view_token', $token)->firstOrFail();
    return view('my.payslip', ['h' => $h]); // Public read-only view
})->where('token', '[A-Za-z0-9_\-]{24,200}')
    ->middleware(['throttle:60,1', 'cache.headers:private;max_age=60;etag'])
    ->name('my.payslip.view');

/* --------------------------------------------------------------------------
| Dev-only SMTP test endpoint
|-------------------------------------------------------------------------- */
if (config('app.debug')) {
    Route::get('/_mailtest', function () {
        Mail::raw('Test email OK', function ($m) {
            $m->to('developer@example.com')->subject('Test SMTP');
        });
        return 'Sent (check inbox / spam)';
    })->middleware(['auth', 'verified', 'throttle:10,1'])->name('dev.mailtest');
}


// Forgot password (request link)
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

// Reset form (from email link)
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

// Submit new password (POST) —> PAKAI NAMA BARU
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

require __DIR__ . '/auth.php';

/* --------------------------------------------------------------------------
| Fallback (404)
|-------------------------------------------------------------------------- */
Route::fallback(function () {
    abort(404);
});
