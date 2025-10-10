<?php

use Illuminate\Support\Facades\Route;

// Controllers (Pages & Auth)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\RoleDashboardController;

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
use App\Http\Controllers\Admin\AssetAssignmentController; // Riwayat penempatan

// Master Data (generic per-entity)
use App\Http\Controllers\MasterDataController;

use App\Http\Controllers\DashboardController; // dashboard

// HCM / Manpower & Shift (semua Blade)
use App\Http\Controllers\Admin\ManpowerController as MP;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\TimesheetController;
use App\Http\Controllers\Admin\ShiftRosterController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\ManpowerPlanController;
use App\Http\Controllers\Admin\ManpowerRealizationController;
use App\Http\Controllers\Admin\HrDailyEntryController;
use App\Http\Controllers\Admin\EmploymentContractController;
use App\Http\Controllers\Admin\CrewAssignmentController;

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
        Route::post('{entity}/{record}/duplicate',[MasterDataController::class, 'duplicate'])
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
Route::middleware(['auth', 'hasrole:gm'])->get('/gm',       [RoleDashboardController::class, 'gm'])->name('gm.dashboard');
Route::middleware(['auth', 'hasrole:manager'])->get('/manager', [RoleDashboardController::class, 'manager'])->name('manager.dashboard');
Route::middleware(['auth', 'hasrole:foreman'])->get('/foreman', [RoleDashboardController::class, 'foreman'])->name('foreman.dashboard');
Route::middleware(['auth', 'hasrole:operator'])->get('/operator', [RoleDashboardController::class, 'operator'])->name('operator.dashboard');
Route::middleware(['auth', 'hasrole:hse_officer'])->get('/hse', [RoleDashboardController::class, 'hse'])->name('hse.dashboard');
Route::middleware(['auth', 'hasrole:hr'])->get('/hr', [RoleDashboardController::class, 'hr'])->name('hr.dashboard');
Route::middleware(['auth', 'hasrole:finance'])->get('/finance', [RoleDashboardController::class, 'finance'])->name('finance.dashboard');

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
| Assets (GM & Manager) — dedicated module, BUTUH konteks site
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm|manager', 'site.selected'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::resource('assets', AssetController::class)->except(['show']);
        Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
        Route::post('assets/bulk-delete', [AssetController::class, 'bulkDelete'])->name('assets.bulk-delete');

        Route::get('assets/{asset}/assignments', [AssetAssignmentController::class, 'index'])->name('assets.assignments.index');
        Route::post('assets/{asset}/assignments', [AssetAssignmentController::class, 'store'])->name('assets.assignments.store');
        Route::delete('assets/{asset}/assignments/{assignment}', [AssetAssignmentController::class, 'destroy'])->name('assets.assignments.destroy');
    });

/*
|--------------------------------------------------------------------------
| HCM (GM & HR) — Blade-only + site.selected
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm|hr', 'site.selected'])
    ->prefix('admin')->name('admin.')->group(function () {

        // Attendance (Blade)
        Route::resource('attendance', AttendanceController::class)
            ->parameters(['attendance' => 'attendance'])
            ->except(['show']);

        // Timesheets (Blade)
        Route::resource('timesheets', TimesheetController::class)
            ->parameters(['timesheets' => 'timesheet'])
            ->except(['show']);

        // Shift Rosters (Blade)
        Route::resource('shift-rosters', ShiftRosterController::class)
            ->parameters(['shift-rosters' => 'shiftRoster'])
            ->except(['show']);

        // Shifts master (Blade)
        Route::resource('shifts', ShiftController::class)
            ->parameters(['shifts' => 'shift'])
            ->except(['show']);

        // Manpower Plans (Blade)
        Route::resource('manpower-plans', ManpowerPlanController::class)
            ->parameters(['manpower-plans' => 'manpowerPlan'])
            ->except(['show']);

        // Manpower Realizations (Blade)
        Route::resource('manpower-reals', ManpowerRealizationController::class)
            ->parameters(['manpower-reals' => 'manpowerRealization'])
            ->except(['show']);

        // HR Daily Entries (Blade)
        Route::resource('hr-entries', HrDailyEntryController::class)
            ->parameters(['hr-entries' => 'entry'])
            ->except(['show']);

        // Employment Contracts (Blade)
        Route::resource('contracts', EmploymentContractController::class)
            ->parameters(['contracts' => 'employmentContract'])
            ->except(['show']);

        // Crew Assignments (Blade)
        Route::resource('crew-assignments', CrewAssignmentController::class)
            ->parameters(['crew-assignments' => 'crewAssignment'])
            ->except(['show']);

        // Manpower dashboard + forms (Blade)
        Route::prefix('manpower')->name('manpower.')->group(function () {
            Route::get('/', [MP::class, 'dashboard'])->name('dashboard');
            Route::get('/plans/create',      [MP::class, 'dashboard'])->name('plans.create'); // optional: atau langsung view khusus
            Route::post('/plan',             [MP::class, 'storePlan'])->name('plan.store');
            Route::get('/reals/create',      [MP::class, 'dashboard'])->name('reals.create'); // optional
            Route::post('/realization',      [MP::class, 'storeRealization'])->name('realization.store');
            Route::get('/assignments',       [MP::class, 'assignments'])->name('assignments.index');
            Route::post('/assignments',      [MP::class, 'storeAssignment'])->name('assignments.store');
        });
    });

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
