<?php

use Illuminate\Support\Facades\Route;

// Controllers (Pages & Auth)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaticPageController;
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
use App\Http\Controllers\Admin\SiteController;           // CRUD daftar site
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MasterEntityController;    // CRUD master_entities (UI)
use App\Http\Controllers\CommodityController;             // CRUD commodities

// Dedicated module
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AssetAssignmentController; // Riwayat/transfer aset

// Master Data (generic per-entity)
use App\Http\Controllers\MasterDataController;

// HCM / Manpower & Shift (ADMIN area)
use App\Http\Controllers\Admin\ManpowerController as MP;
use App\Http\Controllers\Admin\AttendanceController;         // CRUD attendance (admin)
use App\Http\Controllers\Admin\TimesheetController;          // CRUD timesheets + OT actions (admin)
use App\Http\Controllers\Admin\ShiftRosterController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\ManpowerPlanController;
use App\Http\Controllers\Admin\ManpowerRealizationController;
use App\Http\Controllers\Admin\HrDailyEntryController;
use App\Http\Controllers\Admin\EmploymentContractController;
use App\Http\Controllers\Admin\CrewAssignmentController;

// === Employee-side GPS Tap (Check-In/Out) ===
use App\Http\Controllers\AttendanceController as AttendanceTapController;

// === (Opsional) CRUD Lokasi untuk geofence ===
use App\Http\Controllers\Admin\LocationController;

/*
|--------------------------------------------------------------------------
| Route Patterns
|--------------------------------------------------------------------------
*/

Route::pattern('record', '[0-9a-fA-F-]{36}');
Route::pattern('entity', '[a-z0-9_]+');

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login')->name('root');

/*
|--------------------------------------------------------------------------
| Dashboard + Profile (auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/dashboard/assets', [DashboardController::class, 'quickStore'])
        ->middleware('hasrole:gm|manager')
        ->name('dashboard.assets.quick-store');

    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // === Employee-side: Absen GPS (tap) + Check-In/Out (pakai default site di session) ===
    Route::get('/attendance/tap',        [AttendanceTapController::class, 'tapPage'])->name('attendance.tap');
    Route::post('/attendance/check-in',  [AttendanceTapController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [AttendanceTapController::class, 'checkOut'])->name('attendance.checkout');
});

/*
|--------------------------------------------------------------------------
| Pilih Site
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->get('/sites/select', function () {
        $sites = \App\Models\Site::orderBy('name')->get();
        return view('admin.sites.select', compact('sites'));
    })->name('sites.select');

Route::middleware(['auth'])
    ->post('/sites/select', function (\Illuminate\Http\Request $request) {
        $data = $request->validate(['site_id' => ['required', 'uuid', 'exists:sites,id']]);
        $request->session()->put('site_id', $data['site_id']);
        $intended = $request->session()->pull('url.intended');
        return redirect()->to($intended ?: route('dashboard'))
            ->with('success', 'Site aktif telah diubah.');
    })->name('sites.choose');

/*
|--------------------------------------------------------------------------
| Admin Area (GM & Manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm|manager'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('users-export', [UserController::class, 'export'])->name('users.export');
        Route::resource('divisions', DivisionController::class);
    });

/*
|--------------------------------------------------------------------------
| Master Entities (GM only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/master-entities')->as('admin.master_entities.')
    ->group(function () {
        Route::get('/',                      [MasterEntityController::class, 'index'])->name('index');
        Route::get('/create',                [MasterEntityController::class, 'create'])->name('create');
        Route::post('/',                     [MasterEntityController::class, 'store'])->name('store');
        Route::get('/{master_entity}/edit',  [MasterEntityController::class, 'edit'])->name('edit');
        Route::put('/{master_entity}',       [MasterEntityController::class, 'update'])->name('update');
        Route::delete('/{master_entity}',    [MasterEntityController::class, 'destroy'])->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Master Data (GM only) — per-entity
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm', 'site.selected'])
    ->prefix('admin/master')->as('admin.master.')
    ->group(function () {
        Route::get('/', fn() => redirect()->route('admin.master.overview'))->name('home');
        Route::get('overview', [MasterDataController::class, 'overview'])->name('overview');

        Route::get('{entity}/lookup',             [MasterDataController::class, 'lookup'])->name('lookup');
        Route::get('{entity}/export',             [MasterDataController::class, 'export'])->name('export');
        Route::post('{entity}/import',            [MasterDataController::class, 'import'])->name('import');
        Route::get('{entity}/import-template',    [MasterDataController::class, 'importTemplate'])->name('import.template');
        Route::delete('{entity}/bulk-delete',     [MasterDataController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('{entity}/{record}/duplicate', [MasterDataController::class, 'duplicate'])
            ->whereUuid('record')->name('duplicate');

        Route::get('{entity}',               [MasterDataController::class, 'index'])->name('index');
        Route::get('{entity}/create',        [MasterDataController::class, 'create'])->name('create');
        Route::post('{entity}',              [MasterDataController::class, 'store'])->name('store');
        Route::get('{entity}/{record}',      [MasterDataController::class, 'show'])
            ->where('record', '[0-9a-fA-F-]{36}')->name('show');
        Route::get('{entity}/{record}/edit', [MasterDataController::class, 'edit'])
            ->where('record', '[0-9a-fA-F-]{36}')->name('edit');
        Route::put('{entity}/{record}',      [MasterDataController::class, 'update'])
            ->where('record', '[0-9a-fA-F-]{36}')->name('update');
        Route::delete('{entity}/{record}',   [MasterDataController::class, 'destroy'])
            ->where('record', '[0-9a-fA-F-]{36}')->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Kelola Akses User (GM only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/access')->as('admin.access.')
    ->group(function () {
        Route::get('users',              [UserAccessController::class, 'index'])->name('users.index');
        Route::get('users/{user}/role',  [UserAccessController::class, 'editRole'])->name('users.role.edit');
        Route::post('users/{user}/role', [UserAccessController::class, 'updateRole'])->name('users.role');
    });

/*
|--------------------------------------------------------------------------
| Dashboards per Role
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])->get('/gm',           [RoleDashboardController::class, 'gm'])->name('gm.dashboard');
Route::middleware(['auth', 'hasrole:manager'])->get('/manager',  [RoleDashboardController::class, 'manager'])->name('manager.dashboard');
Route::middleware(['auth', 'hasrole:foreman'])->get('/foreman',  [RoleDashboardController::class, 'foreman'])->name('foreman.dashboard');
Route::middleware(['auth', 'hasrole:operator'])->get('/operator', [RoleDashboardController::class, 'operator'])->name('operator.dashboard');
Route::middleware(['auth', 'hasrole:hse_officer'])->get('/hse',  [RoleDashboardController::class, 'hse'])->name('hse.dashboard');
Route::middleware(['auth', 'hasrole:hr'])->get('/hr',            [RoleDashboardController::class, 'hr'])->name('hr.dashboard');
Route::middleware(['auth', 'hasrole:finance'])->get('/finance',  [RoleDashboardController::class, 'finance'])->name('finance.dashboard');

/*
|--------------------------------------------------------------------------
| GM: Site Switcher & Site Config
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])->post('/admin/site/switch', [SiteContextController::class, 'switch'])->name('admin.site.switch');

// Sites CRUD (GM only)
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/sites')->as('admin.sites.')
    ->group(function () {
        Route::get('/',            [SiteController::class, 'index'])->name('index');
        Route::get('/create',      [SiteController::class, 'create'])->name('create');
        Route::post('/',           [SiteController::class, 'store'])->name('store');
        Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');
        Route::put('/{site}',      [SiteController::class, 'update'])->name('update');
        Route::delete('/{site}',   [SiteController::class, 'destroy'])->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Audit Logs (GM only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/audit-logs')->as('admin.audit.')
    ->group(function () {
        Route::get('/',       [AuditLogController::class, 'index'])->name('index');
        Route::get('/{log}',  [AuditLogController::class, 'show'])->whereUuid('log')->name('show');
        Route::get('/export', [AuditLogController::class, 'export'])->name('export');
        Route::get('/feed/json', [AuditLogController::class, 'feed'])->name('feed');
    });

/*
|--------------------------------------------------------------------------
| Konfigurasi Site (GM only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::prefix('site-config')->as('site_config.')->group(function () {
            Route::get('/',                   [SiteConfigController::class, 'index'])->name('index');
            Route::get('/create',             [SiteConfigController::class, 'create'])->name('create');
            Route::post('/',                  [SiteConfigController::class, 'store'])->name('store');
            Route::get('/{site_config}/edit', [SiteConfigController::class, 'edit'])->name('edit');
            Route::put('/{site_config}',      [SiteConfigController::class, 'update'])->name('update');
            Route::delete('/{site_config}',   [SiteConfigController::class, 'destroy'])->name('destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| Commodities (auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('admin/commodities')->as('admin.commodities.')
    ->group(function () {
        Route::get('/',                 [CommodityController::class, 'index'])->name('index');
        Route::get('/create',           [CommodityController::class, 'create'])->name('create');
        Route::post('/',                [CommodityController::class, 'store'])->name('store');
        Route::get('/{commodity}/edit', [CommodityController::class, 'edit'])->name('edit');
        Route::put('/{commodity}',      [CommodityController::class, 'update'])->name('update');
        Route::delete('/{commodity}',   [CommodityController::class, 'destroy'])->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Assets (GM & Manager) — site.selected
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm|manager', 'site.selected'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        // Resource utama assets (lengkap termasuk show)
        Route::resource('assets', AssetController::class)
            ->parameters(['assets' => 'asset'])
            ->whereUuid(['asset']);

        // Nested assignments di bawah assets → admin.assets.assignments.*
        Route::prefix('assets/{asset}')->as('assets.')->whereUuid(['asset'])->group(function () {
            Route::get('assignments',        [AssetAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('assignments/create', [AssetAssignmentController::class, 'create'])->name('assignments.create');
            Route::post('assignments',       [AssetAssignmentController::class, 'store'])->name('assignments.store');
        });

        // (opsional) resource flat → admin.asset-assignments.*
        Route::resource('asset-assignments', AssetAssignmentController::class)
            ->parameters(['asset-assignments' => 'assetAssignment'])
            ->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| HCM (GM & HR) — Blade-only + site.selected
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm|hr', 'site.selected'])
    ->prefix('admin')->name('admin.')->group(function () {

        // === Attendance / Timesheet / Shift roster ===
        Route::resource('attendance', AttendanceController::class)
            ->parameters(['attendance' => 'attendance'])->except(['show']);
        Route::resource('timesheets', TimesheetController::class)
            ->parameters(['timesheets' => 'timesheet'])->except(['show']);
        Route::resource('shift-rosters', ShiftRosterController::class)
            ->parameters(['shift-rosters' => 'shiftRoster'])->except(['show']);
        Route::resource('shifts', ShiftController::class)
            ->parameters(['shifts' => 'shift'])->except(['show']);

        // === AJAX: daftar shift by site (untuk dropdown di form roster)
        Route::get('shift-rosters/shifts-by-site', [ShiftRosterController::class, 'shiftsBySite'])
            ->name('shift-rosters.shifts-by-site');

        // === Overtime flow (berbasis TIMESHEETS) ===
        Route::get('overtime', [TimesheetController::class, 'otIndex'])->name('overtime.index');
        Route::post('timesheets/{timesheet}/ot/submit',  [TimesheetController::class, 'otSubmit'])
            ->whereUuid('timesheet')->name('timesheets.ot.submit');
        Route::post('timesheets/{timesheet}/ot/approve', [TimesheetController::class, 'otApprove'])
            ->whereUuid('timesheet')->name('timesheets.ot.approve');
        Route::post('timesheets/{timesheet}/ot/reject',  [TimesheetController::class, 'otReject'])
            ->whereUuid('timesheet')->name('timesheets.ot.reject');

        // === Locations (opsional) — CRUD titik geofence (name, lat/lng, radius 100m) ===
        Route::resource('locations', LocationController::class)
            ->parameters(['locations' => 'location'])->except(['show']);

        /* =========================
     * HR Daily Types (Blade)
     * ========================= */
        Route::prefix('hr-entries/types')->name('hr-entries.types.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/',  [HrDailyEntryController::class, 'typesIndex'])->name('index');
                Route::post('/', [HrDailyEntryController::class, 'typesStore'])->name('store');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'typesUpdate'])
                    ->name('update')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'typesDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
                Route::post('/reorder', [HrDailyEntryController::class, 'typesReorder'])->name('reorder');
            });

        /* =========================
     * Manage: Meta Form Config (Blade)
     * ========================= */
        Route::prefix('hr-entries/meta-form-config')->name('hr-entries.meta-form.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/', [HrDailyEntryController::class, 'metaFormConfigIndex'])->name('index');
                Route::get('/manage/{type?}', [HrDailyEntryController::class, 'metaFormConfigManage'])
                    ->name('manage')->where('type', '[A-Za-z0-9_\-]+');
                Route::get('/{type}', [HrDailyEntryController::class, 'metaFormConfigShow'])
                    ->name('show')->where('type', '^(?!manage$)[A-Za-z0-9_\-]+$');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'metaFormConfigUpsert'])
                    ->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'metaFormConfigDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
            });

        /* =========================
     * Manage: Meta Schemas (Blade)
     * ========================= */
        Route::prefix('hr-entries/meta-schema')->name('hr-entries.meta-schema.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/', [HrDailyEntryController::class, 'metaSchemasIndex'])->name('index');
                Route::get('/manage/{type?}', [HrDailyEntryController::class, 'metaSchemasManage'])
                    ->name('manage')->where('type', '[A-Za-z0-9_\-]+');
                Route::get('/{type}', [HrDailyEntryController::class, 'metaSchemasShow'])
                    ->name('show')->where('type', '^(?!manage$)[A-Za-z0-9_\-]+$');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'metaSchemasUpsert'])
                    ->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'metaSchemasDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
            });

        /* =========================
     * Manage: Approval Schemas (Blade)
     * ========================= */
        Route::prefix('hr-entries/approval/schemas')->name('hr-entries.approval.schemas.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/', [HrDailyEntryController::class, 'approvalSchemasIndex'])->name('index');
                Route::get('/{type}', [HrDailyEntryController::class, 'approvalSchemasShow'])
                    ->name('show')->where('type', '[A-Za-z0-9_\-]+');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'approvalSchemasUpsert'])
                    ->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'approvalSchemasDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
            });

        /* =========================
     * Manage: Print Templates (Blade)
     * ========================= */
        Route::prefix('hr-entries/print-templates')->name('hr-entries.print-templates.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/', [HrDailyEntryController::class, 'printTemplatesIndex'])->name('index');
                Route::get('/{type}', [HrDailyEntryController::class, 'printTemplatesShow'])
                    ->name('show')->where('type', '[A-Za-z0-9_\-]+');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'printTemplatesUpsert'])
                    ->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'printTemplatesDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
            });

        /* =========================
     * HR Daily Entries (CRUD + actions)
     * ========================= */
        Route::resource('hr-entries', HrDailyEntryController::class)
            ->parameters(['hr-entries' => 'entry'])->except(['show'])->whereUuid(['entry']);

        Route::post('hr-entries/{entry}/submit', [HrDailyEntryController::class, 'approvalSubmit'])
            ->middleware('can:submit,entry')->name('hr-entries.submit')->whereUuid('entry');
        Route::post('hr-entries/{entry}/approve', [HrDailyEntryController::class, 'approvalApprove'])
            ->middleware('can:approve,entry')->name('hr-entries.approve')->whereUuid('entry');
        Route::post('hr-entries/{entry}/reject', [HrDailyEntryController::class, 'approvalReject'])
            ->middleware('can:reject,entry')->name('hr-entries.reject')->whereUuid('entry');

        Route::post('hr-entries/bulk', [HrDailyEntryController::class, 'bulk'])
            ->middleware('can:bulkAction,App\Models\HrDailyEntry')->name('hr-entries.bulk');
        Route::get('hr-entries/trashed', [HrDailyEntryController::class, 'trashed'])
            ->middleware('can:viewTrashed,App\Models\HrDailyEntry')->name('hr-entries.trashed');
        Route::post('hr-entries/{entry}/restore', [HrDailyEntryController::class, 'restore'])
            ->middleware('can:restore,entry')->name('hr-entries.restore')->whereUuid('entry');
        Route::delete('hr-entries/{entry}/force', [HrDailyEntryController::class, 'forceDelete'])
            ->middleware('can:forceDelete,entry')->name('hr-entries.force-delete')->whereUuid('entry');

        Route::get('hr-entries/export/csv', [HrDailyEntryController::class, 'exportCsv'])
            ->middleware('can:export,App\Models\HrDailyEntry')->name('hr-entries.export.csv');
        Route::get('hr-entries/print', [HrDailyEntryController::class, 'print'])
            ->middleware('can:export,App\Models\HrDailyEntry')
            ->name('hr-entries.print');

        Route::get('hr-entries/{entry}/attachments/{key}', [HrDailyEntryController::class, 'downloadAttachment'])
            ->middleware('can:view,entry')->name('hr-entries.attachments.download')->whereUuid('entry');

        Route::get('hr-entries/options/types', [HrDailyEntryController::class, 'typesOptions'])
            ->name('hr-entries.options.types');
        Route::get('hr-entries/options/ga-categories', [HrDailyEntryController::class, 'gaCategoriesOptions'])
            ->name('hr-entries.options.ga-categories');
        Route::get('hr-entries/search/users', [HrDailyEntryController::class, 'searchUsers'])
            ->middleware('can:viewAny,App\Models\User')->name('hr-entries.search.users');

        Route::resource('contracts', EmploymentContractController::class)
            ->parameters(['contracts' => 'employmentContract'])->except(['show']);
        Route::resource('crew-assignments', CrewAssignmentController::class)
            ->parameters(['crew-assignments' => 'crewAssignment'])->except(['show']);
    });

// Non-admin (nama: manpower.entries.*)
Route::middleware(['auth'])->group(function () {
    Route::resource('manpower/entries', ManpowerEntryController::class)
        ->parameters(['entries' => 'entry'])
        ->names('manpower.entries');
});

// (Opsional) Admin alias (nama: admin.manpower.entries.*)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('manpower/entries', ManpowerEntryController::class)
        ->parameters(['entries' => 'entry'])
        ->names('manpower.entries');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
